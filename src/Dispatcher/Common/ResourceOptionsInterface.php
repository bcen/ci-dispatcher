<?php
namespace Dispatcher\Common;

interface ResourceOptionsInterface
{
    public function getAllowedMethods();
    public function setAllowedMethods(array $methods);
    public function getDefaultFormat();
    public function getSupportedFormats();
    public function setSupportedFormats(array $formats);
    public function getAllowedFields();
    public function setAllowedFields(array $fields);
    public function getActionMaps();
    public function setActionMaps(array $actionMaps);
    public function getPaginator();
    public function setPaginator(PaginatorInterface $paginator);
    public function getPageLimit();
    public function setPageLimit($limit);
    public function enableSubresource();
    public function disableSubresource();
    public function handleSubresource();
}
