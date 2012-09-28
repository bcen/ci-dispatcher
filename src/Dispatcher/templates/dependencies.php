<?php

/*
| -------------------------------------------------------------------
| Poor man's IoC container
| -------------------------------------------------------------------
|
| e.g.
| $config['container']['my_key'] = 'key_to_something';
| $config['container']['logger'] = function($c) {
|     return new DBLogger($connStr);
| };
|
| If you want to share the dependencies everywhere, you should use
| $config['sharedContainer'] instead of $config['container'].
|
| e.g.
| $config['sharedContainer']['myLogger'] = function($c) {
|     return new DBLogger($connStr);
| };
|
| 'myLogger' will be created once across all controllers and middlewares.
*/
$config['container']       = array();
$config['sharedContainer'] = array();
