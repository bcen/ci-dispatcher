<?php
namespace Dispatcher\Common;

class DefaultResourceOptions implements ResourceOptionsInterface
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
        $formats = $this->getSupportedFormats();
        return $formats[0];
    }

    public function getSupportedFormats()
    {
        if (isset($this->options['supportedFormats'])) {
            return $this->options['supportedFormats'];
        }

        $this->options['supportedFormats'] = array('application/json');
        return $this->options['supportedFormats'];
    }

    public function setSupportedFormats(array $formats)
    {
        if (count($formats) > 0) {
            $this->options['supportedFormats'] = $formats;
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

    public function getActionMaps()
    {
        if (isset($this->options['actionMaps'])) {
            return $this->options['actionMaps'];
        }

        return array(
            'GET'    => 'read',
            'POST'   => 'create',
            'PUT'    => 'update',
            'DELETE' => 'delete'
        );
    }

    public function setActionMaps(array $actionMaps)
    {
        $this->options['actionMaps'] = $actionMaps;
        return $this;
    }
}
