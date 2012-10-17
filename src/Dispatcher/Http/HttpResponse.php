<?php
namespace Dispatcher\Http;

class HttpResponse implements HttpResponseInterface
{
    protected $statusCode;
    protected $content;
    protected $contentType;
    protected $cookies;
    protected $headers;

    public function __construct($statusCode = 200,
                                $content = '',
                                array $headers = array())
    {
        $this->setStatusCode($statusCode)
             ->setContentType('text/html')
             ->setContent($content)
             ->setHeaders($headers);
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function setStatusCode($code)
    {
        $this->statusCode = $code;
        return $this;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    public function getContentType()
    {
        return $this->contentType;
    }

    public function setContentType($type)
    {
        $this->contentType = $type;
        return $this;
    }

    public function getHeader($key)
    {
        return $this->headers[$key];
    }

    public function setHeader($key, $value)
    {
        $this->headers[$key] = $value;
        return $this;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
        return $this;
    }

    public function send(HttpRequestInterface $request)
    {
        $this->sendHeaders($request);
        $this->sendBody($request);
    }

    protected function getCI()
    {
        return get_instance();
    }

    protected function sendHeaders(HttpRequestInterface $request)
    {
        $this->getCI()->output->set_content_type($this->getContentType());

        foreach ($this->getHeaders() as $k => $v) {
            $this->output->set_header($k . ': ' . $v);
        }

        $this->getCI()->output->set_status_header($this->getStatusCode());
    }

    protected function sendBody(HttpRequestInterface $request)
    {
        $this->getCI()->output->set_output('');
    }
}
