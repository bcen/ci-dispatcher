<?php
namespace Dispatcher;

interface HttpRequestInterface
{
    /**
     * Gets the unique identifier of this request object.
     * @return string
     */
    public function getId();

    /**
     * Gets the request method.
     * @return string
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

    public function GET($key = NULL, $default = NULL, $sanitize = FALSE);

    public function POST($key = NULL, $default = NULL, $sanitize = FALSE);

    public function PUT($key = NULL, $default = NULL, $sanitize = FALSE);

    public function DELETE($key = NULL, $default = NULL, $sanitize = FALSE);

    public function getParam($key = NULL, $default = NULL, $sanitize = FALSE);

    public function getCookie($key, $default = NULL);

    public function getHeader($key, $default = NULL);

    public function getServerParam($key, $default = NULL);

    public function getScheme();

    public function getContentType();

    public function getIp();

    public function getIpv4();

    public function getIpv6();

    public function getHostName();

    public function getUserAgent();

    public function getBaseUrl();

    public function getUrl();

    public function getUri();

    public function getUriArray();

    public function getFullUri();

    public function getSession();
}
