<?php
namespace Dispatcher\Tests\Stub;

use Dispatcher\BootstrapController;
use Dispatcher\HttpRequestInterface;
use Dispatcher\HttpResponseInterface;
use Dispatcher\JsonResponse;

class BootstrapControllerLoadMiddlewareSpy extends BootstrapController
{
    public $middlewares = array();

    public function __construct()
    {
        parent::__construct();
    }

    protected function dispatch($uri, HttpRequestInterface $request)
    {
        // DO NOTHING
        return JsonResponse::create();
    }

    protected function renderResponse(HttpRequestInterface $req,
                                      HttpResponseInterface $res)
    {
        // DO NOTHING
    }

    protected function loadMiddlewares()
    {
        $middlewares = parent::loadMiddlewares();
        $this->middlewares = $middlewares;
        return $middlewares;
    }

    protected function loadDispatcherConfig()
    {
        return array(
            'middlewares' => array(
                'Dispatcher\\Tests\Stub\\MiddlewareSpy'
            ),
            'debug' => false
        );
    }
}
