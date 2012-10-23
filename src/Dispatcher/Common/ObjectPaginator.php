<?php
namespace Dispatcher\Common;

class ObjectPaginator
{
    private $objects;
    private $offset;
    private $limit;

    public function __construct(array &$objects, $offset = 0, $limit = 20)
    {
        $this->objects = &$objects;
        $this->setOffset($offset)
             ->setLimit($limit);
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
