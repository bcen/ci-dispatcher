<?php
namespace Dispatcher\Common;

use Dispatcher\Http\HttpRequestInterface;
use Dispatcher\Http\HttpResponseInterface;
use Dispatcher\Common\ArrayHelper as a;

class ResourceBundle implements \ArrayAccess
{
    private $request;
    private $response;
    private $_attr = array('data' => null);

    public function setData($data)
    {
        $this->_attr['data'] = $data;
        return $this;
    }

    public function addData($key, $value)
    {
        if (!is_array($this->_attr['data'])) {
            $this->_attr['data'] = array();
        }
        $this->_attr['data'][$key] = $value;
        return $this;
    }

    public function getData($key = null, $default = null)
    {
        if (!$key) {
            return a::ref($this->_attr['data'], null);
        }

        return a::ref($this->_attr['data'][$key], $default);
    }

    public function setResponse(HttpResponseInterface $response)
    {
        $this->response = $response;
        return $this;
    }

    /**
     * @return \Dispatcher\Http\HttpResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }

    public function setRequest(HttpRequestInterface $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @return \Dispatcher\Http\HttpRequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->_attr);
    }

    public function &offsetGet($offset)
    {
        if (!array_key_exists($offset, $this->_attr)) {
            throw new \InvalidArgumentException("$offset does not exists");
        }

        return $this->_attr[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->_attr[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->_attr[$offset]);
    }
}
