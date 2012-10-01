<?php
namespace Dispatcher;

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

    public function GET($key = NULL, $default = NULL, $sanitize = FALSE)
    {
        return $this->_fetch(
            $this->_ci()->input->get($key, $sanitize), $default);
    }

    public function POST($key = NULL, $default = NULL, $sanitize = FALSE)
    {
        return $this->_fetch(
            $this->_ci()->input->post($key, $sanitize), $default);
    }

    public function PUT($key = NULL, $default = NULL, $sanitize = FALSE)
    {
        $params = array();
        parse_str(file_get_contents('php://input'), $params);
        if ($key !== NULL && isset($params[$key])) {
            return $params[$key];
        } else if ($key !== NULL) {
            return $default;
        }
        return $params;
    }

    public function DELETE($key = NULL, $default = NULL, $sanitize = FALSE)
    {
        return $this->PUT($key, $default, $sanitize);
    }

    public function getParam($key, $default = NULL, $sanitize = FALSE)
    {
        return $this->POST($key,
            $this->GET($key, $default, $sanitize), $sanitize);
    }

    public function getCookie($key, $default = NULL, $sanitize = TRUE)
    {
        return $this->_fetch(
            $this->_ci()->input->cookie($key, $sanitize), $default);
    }

    public function getHeader($key, $default = NULL)
    {
        return $this->_fetch(
            $this->_ci()->input->get_request_header($key, TRUE), $default);
    }

    public function getServerParam($key, $default = NULL)
    {
        return $this->_fetch(
            $this->_ci()->input->server($key, TRUE), $default);
    }

    public function getScheme()
    {
        return $this->_ci()->input->server('HTTPS', TRUE) !== FALSE
            ? 'HTTPS' : 'HTTP';
    }

    public function getContentType()
    {
        return $this->_fetch(
            $this->_ci()->input->server('CONTENT_TYPE', TRUE), '');
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
        return $this->_ci()->input->user_agent();
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
        return $this->_ci()->input->server('REQUEST_URI', TRUE);
    }

    public function getSession()
    {
        $this->_ci()->load->library('session');
        return $this->_ci()->session;
    }

    private function _fetch($value, $default)
    {
        return $value !== FALSE ? $value : $default;
    }

    private function _ci()
    {
        return get_instance();
    }
}
