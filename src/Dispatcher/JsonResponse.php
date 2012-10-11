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

    public function sendBody(HttpRequestInterface $request)
    {
        $data = $this->getData();
        $content = (is_array($data) || is_object($data))
            ? json_encode($data) : '';
        $this->getCI()->output->set_output($content);
    }
}
