<?php
namespace Dispatcher\Tests\Stub;

use Dispatcher\Http\HttpRequestInterface;
use Dispatcher\Http\HttpResponseInterface;

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
