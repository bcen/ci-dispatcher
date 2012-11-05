<?php
namespace Dispatcher\Common;

interface PaginatorInterface
{
    public function getCount();
    public function getPage();
    public function getQueryset();
    public function setQueryset($queryset);
    public function getOffset();
    public function setOffset($offset);
    public function getLimit();
    public function setLimit($limit);
}
