<?php
namespace Dispatcher\Http;

use Dispatcher\Common\ArrayHelper as a;

/**
 * Simple wrapper around CodeIgniter's Input class
 */
final class DummyRequest implements HttpRequestInterface
{
    private $_data = array();

    public function __construct(array $data = array())
    {
        $this->_data = $data;
    }

    public function getId()
    {
        return a::ref($this->_data['id'], md5('DummyRequest'));
    }

    public function getMethod()
    {
        return a::ref($this->_data['method'], 'GET');
    }

    public function isAjax()
    {
        return a::ref($this->_data['isAjax'], false);
    }

    public function isCli()
    {
        return a::ref($this->_data['isCli'], true);
    }

    public function get($key = null, $default = null, $sanitize = false)
    {
        if (!$key) {
            return a::ref($this->_data['data'], array());
        }
        return a::ref($this->_data['data'][$key], $default);
    }

    public function post($key = null, $default = null, $sanitize = false)
    {
        return $this->get($key, $default);
    }

    public function put($key = null, $default = null, $sanitize = false)
    {
        return $this->get($key, $default);
    }

    public function delete($key = null, $default = null, $sanitize = false)
    {
        return $this->get($key, $default);
    }

    public function getParam($key, $default = null, $sanitize = false)
    {
        return $this->get($key, $default);
    }

    public function getCookie($key, $default = null, $sanitize = false)
    {
        return a::ref($this->_data['cookie'], $default);
    }

    public function getHeader($key, $default = null, $sanitize = false)
    {
        return a::ref($this->_data['headers'][$key], $default);
    }

    public function getServerParam($key, $default = null, $sanitize = false)
    {
        return a::ref($this->_data['server'][$key], $default);
    }

    public function getScheme()
    {
        return a::ref($this->_data['scheme'], 'HTTP');
    }

    public function getContentType()
    {
        return a::ref($this->_data['contentType'], '*/*,text/html,application/xml');
    }

    public function getIp()
    {
        return a::href($this->_data['ip'], '192.168.1.100');
    }

    public function getIpv4()
    {
        return $this->getIp();
    }

    public function getIpv6()
    {
        return $this->getIp();
    }

    public function getHostName()
    {
        return a::ref($this->_data['hostName'], 'comp-123');
    }

    public function getUserAgent()
    {
        return a::ref($this->_data['userAgent'], 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.1 (KHTML, like Gecko) Chrome/21.0.1180.79 Safari/537.1');
    }

    public function getBaseUrl()
    {
        return a::ref($this->_data['baseUrl'], 'http://localhost/');
    }

    public function getUrl()
    {
        return a::ref($this->_data['url'], $this->getBaseUrl() . 'myapp');
    }

    public function getUri()
    {
        return a::ref($this->_data['uri'], 'api/v1');
    }

    public function getUriArray()
    {
        return explode('/', $this->getUri());
    }

    public function getFullUri()
    {
        return a::ref($this->_data['fullUri'], $this->getUrl() . '/' . $this->getUri() . '/');
    }

    public function getSession()
    {
        return null;
    }

    public function getAcceptableContentTypes()
    {
        $accept = preg_replace('/[\s]+/', '', $this->getHeader('Accept', ''));
        $types = explode(',', $accept);
        foreach ($types as $k => $v) {
            $v = explode(';', $v);
            $types[$k] = array_shift($v);
        }
        return $types;
    }
}
