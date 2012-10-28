<?php
namespace Dispatcher;

use stdClass;
use Exception;
use ReflectionClass;
use CI_Controller;
use InvalidArgumentException;

use Dispatcher\Http\HttpRequestInterface;
use Dispatcher\Http\HttpResponseInterface;
use Dispatcher\Http\HttpRequest;
use Dispatcher\Http\Error404Response;
use Dispatcher\Http\Exception\HttpErrorException;
use Dispatcher\Common\DIContainer;
use Dispatcher\Common\ClassInfo;
use Dispatcher\Common\CodeIgniterAware;
use Dispatcher\Exception\DispatchingException;

/**
 * Re-routes incoming uri to class based controller instead of CI's default
 * function based controller
 */
class BootstrapController extends CI_Controller
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
     * @var \Dispatcher\Common\DIContainer
     */
    protected $container;

    /**
     * This is the main entry point of the CI-Dispatcher plugin.
     * <i>Note: The life cycle of this class wil be managed/called by CodeIgniter.php</i>
     *
     * @param string $method      The CodeIgniter controller function to be called.
     * @param array  $uriSegments URI segments
     * @throws \Dispatcher\Exception\DispatchingException|\Exception
     */
    public function _remap($method, $uriSegments)
    {
        // Initializes all configurations
        $this->initializeConfig();

        $request = $this->createHttpRequest();
        if (!$request instanceof HttpRequestInterface) {
            throw new \Exception(
                'Object must implements \Dispatcher\HttpRequestInterface');
        }

        // for injection in middleware/controller constructor
        $this->container['request'] = $request;

        // loads up the middleware for pre/post dispatch
        $middlewares = $this->loadMiddlewares();
        $exception = null;

        try {
            // Pre-dispatch process
            foreach ($middlewares as $m) {
                if (method_exists($m, 'processRequest')) {
                    $m->{'processRequest'}($request);
                }
            }

            // Dispatches the request, and wait for response
            array_unshift($uriSegments, $method);
            $response = $this->dispatch($request, $uriSegments);

            // Post-dispatch process
            for ($i = count($middlewares) - 1; $i >= 0; $i--) {
                if (method_exists($middlewares[$i], 'processResponse')) {
                    $middlewares[$i]->{'processResponse'}($response);
                }
            }
        } catch (HttpErrorException $ex) {
            $response = $ex->getResponse();
        } catch (DispatchingException $ex) {
            $exception = $ex;
            $response = $ex->getResponse();
        } catch (Exception $ex) {
            $exception = $ex;
            $response = new Error404Response();
        }

        // if we are in debugging mode, lets throw the exception out
        if ($exception && $this->_debug) {
            throw $exception;
        }

        $this->renderResponse($request, $response);
    }

    /**
     * Initializes configurations from 'loadDispatcherConfig' and 'loadDependenciesCOnfig'.
     */
    protected function initializeConfig()
    {
        $config = $this->loadDispatcherConfig();
        $this->_middlewares = getattr($config['middlewares'], array());
        $this->_debug = getattr($config['debug'], false);
        $this->container = $this->createContainer(
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
     * Creates the concrete {@link \Dispatcher\Http\HttpRequestInterface} object.
     * @return \Dispatcher\Http\HttpRequestInterface
     */
    protected function createHttpRequest()
    {
        static $request = null;
        if ($request === null) {
            $request = new HttpRequest();
        }
        return $request;
    }

    /**
     * Creates the DIContainer with the given configuration.
     * @param  array $config The dependency configuration
     * @return \Dispatcher\Common\DIContainer
     */
    protected function createContainer(array $config = array())
    {
        $container = new DIContainer();

        $containerCfg = getattr($config['container'], array());

        foreach ($containerCfg as $k => $v) {
            $container[$k] = $v;
        }

        $sharedContainerCfg = getattr($config['sharedContainer'], array());

        foreach ($sharedContainerCfg as $k => $v) {
            $container->share($k, $v);
        }

        return $container;
    }

    /**
     * Sends the response.
     * @param \Dispatcher\Http\HttpRequestInterface $request
     * @param \Dispatcher\Http\HttpResponseInterface $response
     */
    protected function renderResponse(HttpRequestInterface $request,
                                      HttpResponseInterface $response)
    {
        $response->send($request);
    }

    /**
     * Dispatches the incoming request to the proper resource.
     * <i>Note: Resource must implement {@link \Dispatcher\Http\DispatchableInterface}</i>
     *
     * @param \Dispatcher\Http\HttpRequestInterface $request     The incoming request object
     * @param array                                 $uriSegments The uri segments
     * @throws \Dispatcher\Exception\DispatchingException|\Exception
     * @return \Dispatcher\Http\HttpResponseInterface
     */
    protected function dispatch(HttpRequestInterface $request, $uriSegments)
    {
        // gets the class infomation that we will be dispatching to
        $classInfo = $this->loadClassInfoOn($uriSegments);

        // 404 page if we cannot find any assocaited class info
        if ($classInfo === null) {
            return new Error404Response();
        }

        // Finally, let's load the class and dispatch it
        $controller = $this->loadClass($classInfo);

        if (!$controller instanceof DispatchableInterface) {
            return new Error404Response();
        }

        set_error_handler(function($errno, $errstr) {
            throw new Exception("$errno@$errstr");
        });
        $response = $controller->doDispatch($request, $classInfo->getParams());
        restore_error_handler();

        return $response;
    }

    /**
     * Loads and returns an array of middleware instances.
     * @return array
     */
    protected function loadMiddlewares()
    {
        // middleware instances
        $middlewares = array();

        foreach ($this->_middlewares as $name) {
            $mw = null;
            $classInfo = null;

            // see if the class is loaded or trigger autoload
            if (class_exists($name)) {
                $classInfo = new ClassInfo($name, '');
            } else {
                $paths = explode('/', $name);

                // prepares the class name
                $name = array_pop($paths);
                $name = ucwords(preg_replace(
                    '/[_]+/', ' ', strtolower(trim($name))));
                $name = preg_replace('/[\s]+/', '_', trim($name));

                $parts = array_merge(
                    array(rtrim(APPPATH, '/'), 'middlewares'),
                    $paths,
                    array(strtolower($name) . EXT)
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

    /**
     * Lookup the resource controller to use base on the $uriSegments.
     * @param array $uriSegments An array of URI
     * @return \Dispatcher\Common\ClassInfo|null Returns an instance of the class if success, otherwise, null
     */
    protected function loadClassInfoOn(array $uriSegments)
    {
        $path = APPPATH . 'controllers'; // default path to look for the class

        $classInfo = null;

        // We always take the first element in `$routes`
        // and try to see if the file exists with the same name
        while ($r = array_shift($uriSegments)) {
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

                $classInfo = new ClassInfo(
                    $underscored, $path . EXT,
                    $uriSegments);
            } else if (is_file($path . '/index' . EXT)) {
                // see if we have an index.php in the mapped uri directory
                $classInfo = new ClassInfo(
                    'Index', $path . '/index' . EXT,
                    $uriSegments);
            }
        }

        return $classInfo;
    }

    /**
     * Loads and returns an instance of the given class.
     * <i>Note: The default implementation uses Reflection to inject
     * dependencies into constructor from the dependencies config.</i>
     *
     * @param \Dispatcher\Common\ClassInfo $classInfo
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
                $deps[] = $this->container[$param->getName()];
            } catch (InvalidArgumentException $ex) {
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
