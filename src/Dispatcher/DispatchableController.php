<?php
namespace Dispatcher;

/**
 * Base controller that implemented {@link \Dispatcher\DispatchableInterface}.
 */
abstract class DispatchableController implements DispatchableInterface
{
    /**
     * Does the actual dispatching.
     * @param HttpRequestInterface $request
     * @param array $params
     * @return HttpResponseInterface
     * @throws \LogicException When there is not enough expected arguments
     */
    public function doDispatch(HttpRequestInterface $request,
                               array $params = array())
    {
        $expectedNum = array_unshift($params, $request);

        // see what is the requested method, e.g. 'GET', 'POST' and etc...
        $reflectedMethod = new \ReflectionMethod(
            $this, strtolower($request->getMethod()));

        if ($expectedNum > count($reflectedMethod->getParameters())) {
            throw new \LogicException(
                sprintf('Method: %s must accept %d params',
                        strtolower($request->getMethod()), $expectedNum));
        }

        $response = call_user_func_array(array(
            $this, strtolower($request->getMethod())), $params);

        return $response;
    }

    /**
     * The default handler for GET request.
     * @param HttpRequestInterface $request
     * @return HttpResponseInterface
     */
    public function get(HttpRequestInterface $request)
    {
        $data = call_user_func_array(
            array($this, 'getContextData'), func_get_args());
        return $this->renderView($data);
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
     * @throws \LogicException When there is no views property defined
     */
    protected function getViews()
    {
        $views = property_exists($this, 'views') ? $this->views : array();
        $views = is_array($views) ? $views : array($views);
        if (empty($views)) {
            throw new \LogicException('No views defined.');
        }
        return $views;
    }

    /**
     * Creates a {@link \Dispatcher\ViewTemplateResponse}.
     * @param array $data
     * @param int $statusCode
     * @return HttpResponseInterface
     */
    protected function renderView(array $data = array(), $statusCode = 200)
    {
        return ViewTemplateResponse::create($statusCode, $data)
            ->setViews($this->getViews());
    }

    /**
     * Creates a {@link \Dispatcher\RawHtmlResponse}.
     * @param string $html
     * @param int $statusCode
     * @return HttpResponseInterface
     */
    protected function renderHtml($html = '', $statusCode = 200)
    {
        return RawHtmlResponse::create($statusCode, $html);
    }

    /**
     * Creates a {@link \Dispatcher\JsonResponse}.
     * @param mixed $data
     * @param int $statusCode
     * @return HttpResponseInterface
     */
    protected function renderJson($data = null, $statusCode = 200)
    {
        return JsonResponse::create($statusCode, $data);
    }
}
