<?php
namespace Dispatcher;

/**
 * Base class of controller for Dispatcher.
 */
abstract class DispatchableController implements DispatchableInterface
{
    protected $views = '';

    public function __construct()
    {
    }

    public function __get($key)
    {
        // Remaps CodeIgniter's property to this controller
        $CI =& get_instance();
        if (property_exists($CI, $key)) {
            return $CI->$key;
        }

        // stolen from
        // http://www.php.net/manual/en/language.oop5.overloading.php#object.get
        $trace = debug_backtrace();
        trigger_error(
            'Undefined property via __get(): ' . $key .
            ' in ' . $trace[0]['file'] .
            ' on line ' . $trace[0]['line'],
            E_USER_NOTICE);

        return NULL;
    }

    /**
     * {@inheritdoc}
     */
    public function doDispatch(HttpRequestInterface $request,
                               array $params = array(),
                               $failSilent = false)
    {
        // see what is the requested method, e.g. 'GET', 'POST' and etc...
        try {
            $reflectedMethod = new \ReflectionMethod(
                $this, strtolower($request->getMethod()));

            if (count($params) >
                count($reflectedMethod->getParameters()) - 1) {
                log_message('debug', '404 due to not enough expected params');
                return new Error404Response();
            }
        } catch (\ReflectionException $ex) {
            log_message('error', 'Unable to reflect on method');
            return new Error404Response();
        }

        array_unshift($params, $request);
        if ($failSilent) {
            set_error_handler(function() {
                throw new \Exception('Hacky exception to hide the CI ' .
                    'error handler message');
            });
            try {
                // dispatch and get the response
                $response = call_user_func_array(array(
                    $this, strtolower($request->getMethod())), $params);
            } catch (\Exception $ex) {
                log_message('debug', '404 due to ' . $ex->getMessage());
                return new Error404Response();
            }
            restore_error_handler();
        } else {
            // dispatch and get the response
            $response = call_user_func_array(array(
                $this, strtolower($request->getMethod())), $params);
        }

        return $response;
    }

    public function get(HttpRequestInterface $request)
    {
        $data = call_user_func_array(
            array($this, 'getContextData'),
            func_get_args());
        return $this->renderView($data);
    }

    /**
     * Returns data according to the current context.
     * @return array the data
     */
    public function getContextData()
    {
        return array();
    }

    protected function getViews()
    {
        $views = is_array($this->views) ? $this->views : array($this->views);
        if (empty($views)) {
            show_error('Declare your views as protected ' .
                       '$views = array("index") or "index"');
        }
        return $views;
    }

    protected function renderView(array $data = array(), $statusCode = 200)
    {
        return ViewTemplateResponse::create($statusCode)
            ->setData($data)
            ->setViews($this->getViews());
    }

    protected function renderHtml($html = '', $statusCode = 200)
    {
        return RawHtmlResponse::create($statusCode, $html);
    }

    protected function renderJson($data = NULL, $statusCode = 200)
    {
        return JsonResponse::create($statusCode)->setData($data);
    }
}
