<?php

if (!function_exists('array_get')) {
    function array_get(&$attr, $default = null)
    {
        return isset($attr) ? $attr : $default;
    }
}
