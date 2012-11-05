<?php
namespace Dispatcher\Http;

/**
 * Simple wrapper around CodeIgniter's Input class
 */
class HttpRequest implements HttpRequestInterface
{
    private $_id;

    public function __construct()
    {
        $this->_id = md5(uniqid($this->getUri() . $this->getIp()));
    }

    public function getId()
    {
        return $this->_id;
    }

    public function getMethod()
    {
        return $this->_ci()->input->server('REQUEST_METHOD');
    }

    public function isAjax()
    {
        return $this->_ci()->input->is_ajax_request();
    }

    public function isCli()
    {
        return $this->_ci()->input->is_cli_request();
    }

    public function get($key = null, $default = null, $sanitize = false)
    {
        return $this->_fetch(
            $this->_ci()->input->get($key, $sanitize), $default);
    }

    public function post($key = null, $default = null, $sanitize = false)
    {
        return $this->_fetch(
            $this->_ci()->input->post($key, $sanitize), $default);
    }

    public function put($key = null, $default = null, $sanitize = false)
    {
        $params = array();
        parse_str(file_get_contents('php://input'), $params);
        if ($key !== null && isset($params[$key])) {
            return $params[$key];
        } else if ($key !== null) {
            return $default;
        }
        return $params;
    }

    public function delete($key = null, $default = null, $sanitize = false)
    {
        return $this->put($key, $default, $sanitize);
    }

    public function getParam($key, $default = null, $sanitize = false)
    {
        return $this->post($key,
            $this->get($key, $default, $sanitize), $sanitize);
    }

    public function getCookie($key, $default = null, $sanitize = true)
    {
        return $this->_fetch(
            $this->_ci()->input->cookie($key, $sanitize), $default);
    }

    public function getHeader($key, $default = null)
    {
        return $this->_fetch(
            $this->_ci()->input->get_request_header(ucfirst($key), true),
            $default);
    }

    public function getServerParam($key, $default = null)
    {
        return $this->_fetch(
            $this->_ci()->input->server($key, true), $default);
    }

    public function getScheme()
    {
        return $this->_ci()->input->server('HTTPS', true) !== false
            ? 'HTTPS' : 'HTTP';
    }

    public function getContentType()
    {
        return $this->_fetch(
            $this->_ci()->input->server('CONTENT_TYPE', true), '');
    }

    public function getIp()
    {
        return $this->_ci()->input->ip_address();
    }

    public function getIpv4()
    {
        return $this->getIp();
    }

    public function getIpv6()
    {
        throw new \InvalidArgumentException('Not supported');
    }

    public function getHostName()
    {
        return gethostbyaddr($this->getIp());
    }

    public function getUserAgent()
    {
        return $this->_fetch($this->_ci()->input->user_agent(), '');
    }

    public function getBaseUrl()
    {
        return $this->_ci()->config->base_url();
    }

    public function getUrl()
    {
        return $this->_ci()->config->site_url($this->_ci()->uri->uri_string());
    }

    public function getUri()
    {
        return $this->_ci()->uri->uri_string();
    }

    public function getUriArray()
    {
        return explode('/', $this->getUri());
    }

    public function getFullUri()
    {
        return $this->_ci()->input->server('REQUEST_URI', true);
    }

    public function getSession()
    {
        $this->_ci()->load->library('session');
        return $this->_ci()->session;
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

    private function _fetch($value, $default)
    {
        return $value !== false ? $value : $default;
    }

    private function _ci()
    {
        return get_instance();
    }
}
