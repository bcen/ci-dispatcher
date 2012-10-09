<?php
namespace Dispatcher;

class DefaultResourceOption implements ResourceOptionInterface
{
    protected $options = array();

    public static function create()
    {
        return new static();
    }

    public function getAllowedMethods()
    {
        if (isset($this->options['allowedMethods'])) {
            return $this->options['allowedMethods'];
        }

        return array('GET', 'POST', 'PUT', 'DELETE');
    }

    public function setAllowedMethods(array $methods)
    {
        $this->options['allowedMethods'] = $methods;
        return $this;
    }

    public function getDefaultFormat()
    {
        if (isset($this->options['defaultFormat'])) {
            return $this->options['defaultFormat'];
        }

        return 'json';
    }

    public function setDefaultFormat($format)
    {
        $this->options['defaultFormat'] = $format;
        return $this;
    }

    public function getSupportedFormats()
    {
        if (isset($this->options['supportedFormats'])) {
            return $this->options['supportedFormats'];
        }

        return array($this->getDefaultFormat());
    }

    public function setSupportedFormats(array $formats)
    {
        if (count($formats) > 0) {
            $this->options['supportedFormats'] = $formats;
            $this->setDefaultFormat($formats[0]);
        }
        return $this;
    }

    public function getAllowedFields()
    {
        if (isset($this->options['allowedFields'])) {
            return $this->options['allowedFields'];
        }

        return array();
    }

    public function setAllowedFields(array $fields)
    {
        $this->options['allowedFields'] = $fields;
        return $this;
    }
}
