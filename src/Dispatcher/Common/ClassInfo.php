<?php
namespace Dispatcher;

class ClassInfo
{
    private $name;
    private $path;
    private $params;

    public function __construct($name, $path, $params = array())
    {
        $this->setName($name)
             ->setPath($path)
             ->setParams($params);
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setParams(array $params)
    {
        $this->params = $params;
        return $this;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    public function getPath()
    {
        return $this->path;
    }
}
