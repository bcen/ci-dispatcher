<?php
namespace Dispatcher\Common;

class ObjectPaginator implements PaginatorInterface
{
    private $queryset;
    private $offset;
    private $limit;

    public function __construct($offset = 0, $limit = 20)
    {
        $this->setOffset($offset)
             ->setLimit($limit);
    }

    public function getQueryset()
    {
        return $this->queryset;
    }

    public function setQueryset($queryset)
    {
        $this->queryset = $queryset;
        return $this;
    }

    public function getCount()
    {
        return count($this->queryset);
    }

    public function getPage()
    {
        $objects = array_slice(
            $this->getQueryset(),
            $this->getOffset(),
            $this->getLimit()
        );

        $data['meta']['offset'] = (int)$this->getOffset();
        $data['meta']['limit'] = (int)$this->getLimit();
        $data['meta']['total'] = (int)$this->getCount();
        $data['objects'] = $objects;

        return $data;
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
