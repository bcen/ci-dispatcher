<?php
namespace Dispatcher;

/**
 * Base class of controller for Dispatcher.
 */
abstract class DispatchableController
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
            show_error('Declare your views as protected '.
                       '$views = array("index") or "index"');
        }
        return $views;
    }

    protected function renderView(array $data = array(),
                                  array $extra = array())
    {
        $merged = array_merge(
            $extra,
            array('contextData' => $data, 'views' => $this->getViews())
        );
        return new ViewTemplateResponse($merged);
    }

    protected function renderHtml($html = '')
    {
        return new RawHtmlResponse(array(
            'content' => $html
        ));
    }

    protected function renderJson($data = NULL,
                                  $statusCode = 200,
                                  array $extra = array())
    {
        return new JsonResponse($data, $statusCode, $extra);
    }
}
