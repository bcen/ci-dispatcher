<?php

if (!function_exists('getattr')) {
    function getattr(&$attr, $default = null)
    {
        return isset($attr) ? $attr : $default;
    }
}
