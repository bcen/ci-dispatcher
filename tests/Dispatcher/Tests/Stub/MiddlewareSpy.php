<?php
namespace Dispatcher\Tests\Stub;

use Dispatcher\HttpRequestInterface;
use Dispatcher\HttpResponseInterface;

class MiddlewareSpy
{
    public $processRequestCalled = false;
    public $processResponseCalled = false;

    public function processRequest(HttpRequestInterface $request)
    {
        $this->processRequestCalled = true;
    }

    public function processResponse(HttpResponseInterface $response)
    {
        $this->processResponseCalled = true;
    }
}
