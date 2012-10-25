<?php
namespace Dispatcher;

use Dispatcher\Http\HttpRequestInterface;

/**
 * Interface for dispatchable endpoint.
 */
interface DispatchableInterface
{
    /**
     * Does the actual dispatch from the incoming request and returns a
     * response.
     * <i>Note: Implementer must return a object of
     * {@link \Dispatcher\Http\HttpResponseInterface}.</i>
     *
     * @param \Dispatcher\Http\HttpRequestInterface $request    The incoming request
     * @param array                                 $args       Extra parameters
     * @return \Dispatcher\Http\HttpResponseInterface
     */
    public function doDispatch(HttpRequestInterface $request,
                               array $args = array());
}
