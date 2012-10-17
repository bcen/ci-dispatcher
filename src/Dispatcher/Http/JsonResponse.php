<?php
namespace Dispatcher\Http;

class JsonResponse extends HttpResponse
{
    public function __construct($statusCode = 200,
                                $content = '',
                                $headers = array())
    {
        parent::__construct($statusCode, $content, $headers);
        $this->setContentType('application/json');
    }

    protected function sendBody(HttpRequestInterface $request)
    {
        $data = $this->getContent();
        $content = (is_array($data) || is_object($data))
            ? json_encode($data) : '';
        $this->getCI()->output->set_output($content);
    }
}
