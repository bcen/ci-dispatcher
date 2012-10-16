<?php
namespace Dispatcher;

use stdClass;
use Exception;
use ReflectionClass;
use ReflectionMethod;
use ReflectionException;

/**
 * Re-routes incoming uri to class based controller instead of CI's default
 * function based controller
 */
class BootstrapController extends \CI_Controller
{
    /**
     * Array of middlewares to handle before and after the dispatch.
     * @var array
     */
    private $_middlewares = array();

    /**
     * Whether to show/hide debug info
     * @var boolean
     */
    private $_debug = false;

    /**
     * Dependency injection container (IoC)
     * @var DIContainer
     */
    private $_container;

    /**
     * This will be called by CodeIgniter.php to remap to user defined function.
     * <i>Note: We'll use this to remap to our class-based controller.</i>
     *
     * @param $method string The CodeIgniter controller function to be called.
     * @param $uri    array  Array of uri segments
     * @throws \Exception Exception thrown if request does not implement
     *                    {@link \Dispatcher\HttpRequestInterface}
     */
    public function _remap($method, $uri)
    {
        $this->initializeConfig();

        $request = $this->createHttpRequest();
        if (!$request instanceof HttpRequestInterface) {
            throw new \Exception(
                'Object must implements \Dispatcher\HttpRequestInterface');
        }

        $middlewares = $this->loadMiddlewares();
        foreach ($middlewares as $m) {
            if (method_exists($m, 'processRequest')) {
                $m->processRequest($request);
            }
        }

        array_unshift($uri, $method);
        $response = $this->dispatch($uri, $request);

        for ($i = count($middlewares) - 1; $i >= 0; $i--) {
            if (method_exists($middlewares[$i], 'processResponse')) {
                $middlewares[$i]->processResponse($response);
            }
        }

        $this->renderResponse($request, $response);
    }

    protected function initializeConfig()
    {
        $config = $this->loadDispatcherConfig();
        $this->_middlewares = isset($config['middlewares'])
            ? $config['middlewares'] : array();
        $this->_debug = isset($config['debug']) ? $config['debug'] : false;

        $this->_container = $this->createContainer(
            $this->loadDependenciesConfig());
    }

    /**
     * Loads and returns the Dispatcher configuration.
     * @return array The Dispatcher configuration array
     */
    protected function loadDispatcherConfig()
    {
        $config = array();
        require APPPATH . 'config/dispatcher.php';
        return $config;
    }

    /**
     * Loads and returns the dependency container configuration.
     * @return array The dependency configuration for DIContainer
     */
    protected function loadDependenciesConfig()
    {
        $config = array();
        require APPPATH . 'config/dependencies.php';
        return $config;
    }

    /**
     * Creates the concrete {@link \Dispatcher\HttpRequestInterface} object.
     * @return \Dispatcher\HttpRequestInterface
     */
    protected function createHttpRequest()
    {
        static $request = null;
        if ($request === null) {
            $request = new HttpRequest();
            $this->_container['request'] = $request;
        }
        return $request;
    }

    /**
     * Creates the DIContainer with the given configuration.
     * @param  array $config The dependency configuration
     * @return \Dispatcher\DIContainer
     */
    protected function createContainer(array $config = array())
    {
        $container = new DIContainer();

        $containerCfg = isset($config['container'])
            ? $config['container']
            : array();

        $sharedContainerCfg = isset($config['sharedContainer'])
            ? $config['sharedContainer']
            : array();

        foreach ($containerCfg as $k => $v) {
            $container[$k] = $v;
        }

        foreach ($sharedContainerCfg as $k => $v) {
            $container->share($k, $v);
        }

        return $container;
    }

    protected function renderResponse(HttpRequestInterface $request,
                                      HttpResponseInterface $response)
    {
        $response->send($request);
    }

    /**
     * Dispatches the incoming request to the proper resource.
     * <i>Note: Resource must implement {@link \Dispatcher\DispatchableInterface}</i>
     * @param array                $uri     The incoming resource URI in array
     * @param HttpRequestInterface $request The incoming request object
     * @throws \Exception
     * @return \Dispatcher\HttpResponseInterface
     */
    protected function dispatch($uri, HttpRequestInterface $request)
    {
        // gets the class infomation that we will be dispatching to
        $classInfo = $this->loadClassInfoOn($uri);

        // 404 page if we cannot find any assocaited class info
        if ($classInfo === null) {
            log_message('debug', '404 due to unknown classInfo for '.
                implode(',', $request->getUriArray()));
            return new Error404Response();
        }

        // Finally, let's load the class and dispatch it
        $controller = $this->loadClass($classInfo);

        if (!$controller instanceof DispatchableInterface) {
            return new Error404Response();
        }


        $exception = null;

        set_error_handler(function($errno, $errstr) {
            throw new Exception("$errno@$errstr");
        });
        try {
            $response = $controller->doDispatch(
                $request, $classInfo->getParams());
        } catch (DispatchingException $ex) {
            $exception = $ex;
            $response = $ex->getResponse();
        } catch (Exception $ex) {
            $exception = $ex;
            $response = new Error404Response();
        }
        restore_error_handler();

        if ($exception) {
            // When exception thrown,
            // only re-throw in debug mode
            if ($this->_debug) {
                throw $exception;
            }
        }

        return $response;
    }

    /**
     * Loads and returns an array of middleware instance.
     * @return array(DispatchableMiddleware)
     */
    protected function loadMiddlewares()
    {
        $middlewares = array();
        foreach ($this->_middlewares as $name) {
            $mw = null;
            $classInfo = null;
            if (class_exists($name)) {
                $classInfo = new ClassInfo($name, '');
            } else {
                $paths = explode('/', $name);
                $name = array_pop($paths);
                $name = ucwords(preg_replace(
                    '/[_]+/', ' ', strtolower(trim($name))));
                $name = preg_replace('/[\s]+/', '_', trim($name));

                $parts = array_merge(
                    array(rtrim(APPPATH, '/'), 'middlewares'),
                    $paths,
                    array(strtolower($name).EXT)
                );
                $classInfo = new ClassInfo($name, implode('/', $parts));
            }

            $mw = $this->loadClass($classInfo);
            if ($mw !== null) {
                $middlewares[] = $mw;
            }
        }

        return $middlewares;
    }

    protected function loadClassInfoOn(array $routes)
    {
        $path = APPPATH . 'controllers'; // default path to look for the class

        $classInfo = NULL;

        // We always take the first element in `$routes`
        // and try to see if the file exists with the same name
        while ($r = array_shift($routes)) {
            $path .= DIRECTORY_SEPARATOR . $r;

            if (is_file($path . EXT)) {
                // if file exists,
                // we assume that the uri is mapped to this class


                // Taken from the inflector helper
                // it normalizes the uri name into
                // camelized word with underscore
                // e.g.  myname -> Myname, your_name -> Your_Name
                $huamnized = ucwords(preg_replace(
                    '/[_]+/', ' ', strtolower(trim($r))));
                $underscored = preg_replace('/[\s]+/', '_', trim($huamnized));

                $classInfo = new ClassInfo($underscored, $path . EXT, $routes);
            } else if (is_file($path . '/index' . EXT)) {
                // see if we have an index.php in the mapped uri directory
                $classInfo = new ClassInfo('Index', $path . '/index' . EXT,
                    $routes);
            }
        }

        return $classInfo;
    }

    /**
     * Loads and returns an instance of $className with the given $classPath.
     * <i>Note: The default implementation uses Reflection to inject
     * dependencies into constructor from the dependencies config.</i>
     *
     * @param \Dispatcher\ClassInfo $classInfo
     * @return mixed The instance of the class, or null if failed
     */
    protected function loadClass(ClassInfo $classInfo)
    {
        if (file_exists($classInfo->getPath())) {
            require_once $classInfo->getPath();
        }

        if (!class_exists($classInfo->getName())) {
            return null;
        }

        $clsReflect = new ReflectionClass($classInfo->getName());
        $ctor = $clsReflect->getConstructor();

        // if constructor found, get all the parameters
        // for dependency injection
        $expectedParams = array();
        if ($ctor) {
            $expectedParams = $ctor->getParameters();
        }

        $deps = array();
        foreach ($expectedParams as $param) {
            $depName = $param->getName();

            try {
                $deps[] = $this->_container[$param->getName()];
            } catch (\InvalidArgumentException $ex) {
                die("$depName is not found in your dependencies.php");
            }
        }

        $class = $clsReflect->newInstanceArgs($deps);
        if ($class instanceof CodeIgniterAware) {
            $class->setCI(get_instance());
        }
        return $class;
    }
}
