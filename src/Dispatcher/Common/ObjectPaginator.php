<?php
namespace Dispatcher\Common;

class ObjectPaginator implements PaginatorInterface
{
    private $objects = array();
    private $offset;
    private $limit;

    public function __construct($offset = 0, $limit = 20)
    {
        $this->setOffset($offset)
             ->setLimit($limit);
    }

    public function getObjects()
    {
        return $this->objects;
    }

    public function setObjects($objects)
    {
        $this->objects = $objects;
        return $this;
    }

    public function getCount()
    {
        return count($this->objects);
    }

    public function getPage()
    {
        $objects = array_slice($this->objects,
            $this->getOffset(), $this->getLimit());
        return $objects;
    }

    public function getOffset()
    {
        return $this->offset;
    }

    public function setOffset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    public function getLimit()
    {
        return $this->limit;
    }

    public function setLimit($limit)
    {
        $this->limit = $limit;
        return $this;
    }
}
