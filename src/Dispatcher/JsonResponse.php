<?php
namespace Dispatcher;

class JsonResponse extends HttpResponse
{
    public function __construct($statusCode = 200,
                                $content = '',
                                $headers = array())
    {
        parent::__construct($statusCode, $content, $headers);
        $this->setContentType('application/json');
    }

    public function render(HttpRequestInterface $request)
    {
    }
}
