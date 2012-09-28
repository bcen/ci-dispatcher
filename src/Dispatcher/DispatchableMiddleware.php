<?php
namespace Dispatcher;

abstract class DispatchableMiddleware
{
    public function __construct()
    {
    }

    abstract public function processRequest(HttpRequest &$request);
    abstract public function processResponse(HttpResponse &$response);
}
