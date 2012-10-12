<?php
namespace Dispatcher;

final class Error404Response extends HttpResponse
{
    public function __construct($statusCode = 404,
                                $content = '',
                                $headers = array())
    {
        parent::__construct(404, $content, $headers);
    }

    protected function sendBody(HttpRequestInterface $request)
    {
        show_404();
        exit();
    }
}
