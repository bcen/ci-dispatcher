<?php

if (!function_exists('getattr')) {
    function &getattr(&$attr, $default = null)
    {
        $ret = isset($attr) ? $attr : $default;
        return $ret;
    }
}
