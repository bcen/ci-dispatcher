<?php
namespace Dispatcher;

abstract class HttpResponse
{
    protected $_statusCode = 200;
    protected $_content = '';
    protected $_views = array();
    protected $_contextData = array();
    protected $_contentType = 'text/html';
    protected $_cookies = array();
    protected $_header = array();

    public function __construct(array $config = array())
    {
        foreach ($config as $k => $v) {
            if (property_exists($this, '_'.$k)) {
                $this->{'_'.$k} = $v;
            }
        }
    }

    public function set_header($arg0, $arg1)
    {
        if (is_string($arg0) && is_string($arg1)) {
            $this->_header[$arg0] = $arg1;
        }
    }

    public function __get($key)
    {
        if (method_exists($this, 'get'.ucfirst($key))) {
            return $this->{'get'.$key};
        } else if (property_exists($this, '_'.$key)) {
            return $this->{'_'.$key};
        }

        // stolen from
        // http://www.php.net/manual/en/language.oop5.overloading.php#object.get
        $trace = debug_backtrace();
        trigger_error(
            'Undefined property via __get(): ' . $name .
            ' in ' . $trace[0]['file'] .
            ' on line ' . $trace[0]['line'],
            E_USER_NOTICE);

        return NULL;
    }
}
