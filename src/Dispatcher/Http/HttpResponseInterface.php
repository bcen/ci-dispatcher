<?php
namespace Dispatcher\Http;

interface HttpResponseInterface
{
    public function getStatusCode();

    /**
     * @param $code
     * @return \Dispatcher\Http\HttpResponseInterface
     */
    public function setStatusCode($code);

    public function getContent();

    /**
     * @param $content
     * @return \Dispatcher\Http\HttpResponseInterface
     */
    public function setContent($content);

    public function getContentType();

    /**
     * @param $type
     * @return \Dispatcher\Http\HttpResponseInterface
     */
    public function setContentType($type);

    public function getHeader($key);

    /**
     * @param $key
     * @param $value
     * @return \Dispatcher\Http\HttpResponseInterface
     */
    public function setHeader($key, $value);

    public function getHeaders();

    /**
     * @param array $headers
     * @return \Dispatcher\Http\HttpResponseInterface
     */
    public function setHeaders(array $headers);

    public function send(HttpRequestInterface $request);
}
