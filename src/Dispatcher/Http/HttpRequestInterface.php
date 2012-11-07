<?php
namespace Dispatcher\Http;

/**
 * Interface for incoming HTTP request.
 */
interface HttpRequestInterface
{
    /**
     * Gets the unique identifier of this request object.
     * @return string
     */
    public function getId();

    /**
     * Gets the request method.
     * @return string The method string in uppercase, e.g. 'POST', 'GET'
     */
    public function getMethod();

    /**
     * Checks whether the request is ajax.
     * @return boolean
     */
    public function isAjax();

    /**
     * Checks whether the request is coming from command line.
     * @return boolean
     */
    public function isCli();

    /**
     * Retrieves the query string with the given $key.
     * @param string  $key      The name of the query string pair
     * @param mixed   $default  The default value if $key is not found
     * @param boolean $sanitize Whether to sanitize the return value
     * @return mixed            Returns all query string pair if $key is null,
     *                          otherwise, returns the value of the pair
     */
    public function get($key = null, $default = null, $sanitize = false);

    /**
     * Retrieves the POST param with the given $key.
     * @param string  $key      The name of the POST param pair
     * @param mixed   $default  The default value if $key is not found
     * @param boolean $sanitize Whether to sanitize the return value
     * @return mixed            Returns all POST param pair if $key is null,
     *                          otherwise, returns the value of the pair
     */
    public function post($key = null, $default = null, $sanitize = false);

    /**
     * Retrieves the PUT param with the given $key.
     * @param string  $key      The name of the PUT param pair
     * @param mixed   $default  The default value if $key is not found
     * @param boolean $sanitize Whether to sanitize the return value
     * @return mixed            Returns all PUT param pair if $key is null,
     *                          otherwise, returns the value of the pair
     */
    public function put($key = null, $default = null, $sanitize = false);

    /**
     * Retrieves the DELETE param with the given $key.
     * @param string  $key      The name of the DELETE param pair
     * @param mixed   $default  The default value if $key is not found
     * @param boolean $sanitize Whether to sanitize the return value
     * @return mixed            Returns all DELETE param pair if $key is null,
     *                          otherwise, returns the value of the pair
     */
    public function delete($key = null, $default = null, $sanitize = false);

    /**
     * Retrieves the param pair from both GET and POST.
     * <i>Note: POST has precedence over GET</i>
     * @param string  $key      The name of the param pair
     * @param mixed   $default  The default value if $key is not found
     * @param boolean $sanitize Whether to sanitize the return value
     * @return mixed            Returns the value of the pair
     */
    public function getParam($key, $default = null, $sanitize = false);

    /**
     * Retrieves the cookie value with the given $key.
     * @param string $key     The name of the cookie
     * @param mixed  $default The default value if cookie is not found
     * @param bool   $sanitize
     * @return mixed          Returns the value or null if cookie is not found
     */
    public function getCookie($key, $default = null, $sanitize = false);

    /**
     * Retrieves the header value with the given $key.
     * @param string $key     The name of the header
     * @param mixed  $default The default value if header is not found
     * @param bool   $sanitize
     * @return mixed          Returns the value or null if header is not found
     */
    public function getHeader($key, $default = null, $sanitize = false);

    /**
     * Retrieves value from $_SERVER with the given $key.
     * @param string $key     The name of the param
     * @param mixed  $default The default value for non-existent param
     * @param bool   $sanitize
     * @return mixed          Returns the value or null
     */
    public function getServerParam($key, $default = null, $sanitize = false);

    /**
     * Gets the server shceme.
     * @return string HTTP|HTTPS
     */
    public function getScheme();

    /**
     * Gets the Content-Type header value.
     * @return string
     */
    public function getContentType();

    /**
     * Gets the client IP.
     * @return string
     */
    public function getIp();

    /**
     * Gets the client IPv4.
     * @return string
     */
    public function getIpv4();

    /**
     * Gets teh client IPv6.
     * @return string
     */
    public function getIpv6();

    /**
     * Gets the client host name.
     * @return string
     */
    public function getHostName();

    /**
     * Gets the client User-Agent header.
     * @return string
     */
    public function getUserAgent();

    public function getBaseUrl();

    public function getUrl();

    public function getUri();

    public function getUriArray();

    public function getFullUri();

    public function getSession();

    public function getAcceptableContentTypes();
}
