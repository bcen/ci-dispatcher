<?php
namespace Dispatcher\Common;

class ArrayHelper
{
    public static function &ref(&$attr, $default = null)
    {
        $ret = isset($attr) ? $attr : $default;
        return $ret;
    }
}
