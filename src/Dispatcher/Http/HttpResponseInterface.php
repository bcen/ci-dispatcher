<?php
namespace Dispatcher\Http;

interface HttpResponseInterface
{
    public function getStatusCode();

    public function setStatusCode($code);

    public function getContent();

    public function setContent($content);

    public function getContentType();

    public function setContentType($type);

    public function getHeader($key);

    public function setHeader($key, $value);

    public function getHeaders();

    public function setHeaders(array $headers);

    public function send(HttpRequestInterface $request);
}
