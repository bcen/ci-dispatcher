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

    /**
     * @return \Dispatcher\Common\ObjectPaginator
     */
    public function getPaginator()
    {
        if (!isset($this->options['paginator'])) {
            $this->options['paginator'] =
                new ObjectPaginator(0, $this->getPageLimit());
        }

        return $this->options['paginator'];
    }

    public function setPaginator(PaginatorInterface $paginator)
    {
        $this->options['paginator'] = $paginator;
        return $this;
    }

    public function getPageLimit()
    {
        if (isset($this->options['pageLimit'])) {
            return $this->options['pageLimit'];
        }

        return 20;
    }

    public function setPageLimit($limit)
    {
        $this->options['pageLimit'] = $limit;
        return $this;
    }

    public function enableSubresource()
    {
        $this->options['handleSubresource'] = true;
        return $this;
    }

    public function disableSubresource()
    {
        $this->options['handleSubresource'] = false;
        return $this;
    }

    public function handleSubresource()
    {
        if (isset($this->options['handleSubresource'])) {
            return $this->options['handleSubresource'];
        }
        return false;
    }
}
