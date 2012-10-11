<?php
namespace Dispatcher;

final class Error404Response extends HttpResponse
{
    public static function create()
    {
        return new static();
    }

    public function __construct()
    {
        parent::__construct(404);
    }

    protected function sendBody(HttpRequestInterface $request)
    {
        show_404();
        exit();
    }
}
