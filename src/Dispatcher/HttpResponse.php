<?php
namespace Dispatcher;

abstract class HttpResponse implements HttpResponseInterface
{
    protected $statusCode;
    protected $content;
    protected $views;
    protected $contextData = array();
    protected $contentType;
    protected $cookies;
    protected $headers;

    public static function create($statusCode = 200,
                                  $content = '',
                                  $headers = array())
    {
        return new static($statusCode, $content, $headers);
    }

    public function __construct($statusCode = 200,
                                $content = '',
                                $headers = array())
    {
        $this->setStatusCode($statusCode)
             ->setContentType('text/html')
             ->setContent($content);
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

    public function getViews()
    {
        return $this->views;
    }

    public function setViews(array $views)
    {
        $this->views = $views;
        return $this;
    }

    public function getData()
    {
        return $this->contextData;
    }

    public function setData(array $data)
    {
        $this->contextData = $data;
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
}
