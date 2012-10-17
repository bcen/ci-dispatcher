<?php
namespace Dispatcher\Http;

final class Error404Response extends HttpResponse
{
    public function __construct(array $headers = array())
    {
        parent::__construct(404, '', $headers);
    }

    protected function sendBody(HttpRequestInterface $request)
    {
        show_404();
        exit();
    }
}
