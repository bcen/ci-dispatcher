<?php
namespace Dispatcher;

use Dispatcher\Exception\DispatchingException;
use Dispatcher\Http\HttpRequestInterface;
use Dispatcher\Http\HttpResponseInterface;
use Dispatcher\Http\HttpResponse;
use Dispatcher\Http\Error404Response;
use Dispatcher\Http\ViewTemplateResponse;
use Dispatcher\Http\JsonResponse;
use Dispatcher\Http\RawHtmlResponse;

/**
 * Base controller that implemented {@link \Dispatcher\DispatchableInterface}.
 */
abstract class DispatchableController implements DispatchableInterface
{
    /**
     * Dispatches incoming request and returns a response back to caller.
     * Generally, this will be called by {@link \Dispatcher\BootstrapController}.
     *
     * @param \Dispatcher\Http\HttpRequestInterface $request The incoming request
     * @param array                                 $args    Extra uri arguments
     * @throws \Dispatcher\Exception\DispatchingException
     * @return \Dispatcher\Http\HttpResponseInterface
     */
    public function doDispatch(HttpRequestInterface $request,
                               array $args = array())
    {
        // number of expected arguments
        $argc = array_unshift($args, $request);

        // see what is the requested method, e.g. 'GET', 'POST' and etc...
        try {
            $reflectedMethod = new \ReflectionMethod(
                $this, strtolower($request->getMethod()));
        } catch (\ReflectionException $ex) {
            return new HttpResponse(501);
        }

        if ($argc > count($reflectedMethod->getParameters())) {
            throw new DispatchingException('Not enough arguments',
                new Error404Response());
        }

        $response = call_user_func_array(array(
            $this, strtolower($request->getMethod())), $args);

        if (!$response instanceof HttpResponseInterface) {
            throw new DispatchingException(
                'response must implement HttpResponseInterface',
                new Error404Response());
        }

        return $response;
    }

    /**
     * The default handler for GET request.
     * @param \Dispatcher\Http\HttpRequestInterface $request
     * @return \Dispatcher\Http\HttpResponseInterface
     */
    public function get(HttpRequestInterface $request)
    {
        return $this->renderView($this->getContextData($request));
    }

    /**
     * Gets the current context data for response.
     * @return array The data in array
     */
    public function getContextData()
    {
        return array();
    }

    /**
     * Gets the views for view template response.
     * @return array
     * @throws \Dispatcher\Exception\DispatchingException When there is no views property defined
     */
    protected function getViews()
    {
        $views = property_exists($this, 'views') ? $this->{'views'} : array();
        $views = is_array($views) ? $views : array($views);
        if (empty($views)) {
            throw new DispatchingException('No views defined.',
                new Error404Response());
        }
        return $views;
    }

    /**
     * Creates a {@link \Dispatcher\Http\ViewTemplateResponse}.
     * @param array $data
     * @param int $statusCode
     * @return \Dispatcher\Http\HttpResponseInterface
     */
    protected function renderView(array $data = array(), $statusCode = 200)
    {
        return new ViewTemplateResponse($this->getViews(), $statusCode, $data);
    }

    /**
     * Creates a {@link \Dispatcher\Http\RawHtmlResponse}.
     * @param string $html
     * @param int $statusCode
     * @return \Dispatcher\Http\HttpResponseInterface
     */
    protected function renderHtml($html = '', $statusCode = 200)
    {
        return new RawHtmlResponse($statusCode, $html);
    }

    /**
     * Creates a {@link \Dispatcher\Http\JsonResponse}.
     * @param mixed $data
     * @param int $statusCode
     * @return \Dispatcher\Http\HttpResponseInterface
     */
    protected function renderJson($data = null, $statusCode = 200)
    {
        return new JsonResponse($statusCode, $data);
    }
}
