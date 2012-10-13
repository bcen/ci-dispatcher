<?php
namespace Dispatcher;

use Dispatcher\HttpRequestInterface;

/**
 * Interface for dispatchable endpoint.
 */
interface DispatchableInterface
{
    /**
     * Does the actual dispatch from the incoming request and returns a
     * response.
     * @param HttpRequestInterface $request The incoming request
     * @param array                $args    Extra parameters
     * @return HttpResponseInterface
     */
    public function doDispatch(HttpRequestInterface $request,
                               array $args = array());
}
