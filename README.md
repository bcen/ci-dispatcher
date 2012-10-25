CodeIgniter-Dispatcher (WIP)
============================


Introduction
------------

CodeIgniter-Dispatcher uses CodeIgniter's _remap function to do an extra routing
to a class-based controller instead of function-based.


Example
-------

CodeIgniter's function-based controller

```php
<?php

class Welcome extends CI_Controller
{
    public function index()
    {
        $this->load->view('welcome_message');
    }
}
```

With CodeIgniter-Dispatcher's class-based controller

```php
<?php

class Index extends \Dispatcher\DispatchableController
{
    protected $views = 'welcome_message';
}
```


Installtion
-----------

1. Download the [`composer.phar`](http://getcomposer.org/composer.phar) executable or use the installer

    ```sh
    curl -s http://getcomposer.org/installer | php
    ```

2. Add this to composer.json

    ```json
    {
        "require": {
            "bcen/ci-dispatcher": "*"
        }
    }
    ```

3. Run composer `php composer.phar install`

4. Include `autoload.php` in your project and add this to `routes.php`
    ```php
    \Dispatcher\Common\BootstrapInstaller::run($route);
    ```

    `Dispatcher\BootstrapInstaller::run($route)` will create 3 files inside of
    CodeIgniter's `APPPATH`, if you want to skip the file creation, add `true`
    to to skip the file check.

    ```php
    \Dispatcher\Common\BootstrapInstaller::run($route, true);
    ```

Features
--------

##### Poor man's dependency injection #####

Middlewares, DispatchableController and DispatchableResource will be injected
by default.

```php
// config/dependencies.php
$config['container']['userDao'] = function($container) {
    return new UserDao();
};


$config['container']['dsn'] = 'mydsn_string';
// sharedContainer will return the same instance throughout the request/response cycle
$config['sharedContainer']['pdo'] = function($container) {
    return new PDO($container['dsn']);
};

// user_status.php
<?php

class User_Status extends \Dispatcher\DispatchableController
{
    // CI-Dispatcher will inject $userDao from config/dependencies.php
    // for you.
    public function __construct($userDao)
    {
        $userDao->findUserById(1);
    }
}
```

##### Middlewares #####

```php
<?php

class DebugFilter
{
    public function processRequest(Dispatcher\Http\HttpRequestInterface $req)
    {
        // do something
    }

    public function processResponse(Dispatcher\Http\HttpResponseInterface $res)
    {
        // do something
    }
}
```

`processRequest` and `processResponse` are optional, middleware can implement either one or both
to alter the request/response cycle.


##### CodeIgniter Awareness #####

Any class that is created by the Dispatcher can implement `CodeIgniterAware`
to have `CI` injection.

E.g.
```php
<?php

// Controller
class User_Status extends \Dispatcher\DispatchableController implements \Dispatcher\Common\CodeIgniterAware
{
    public function __construct($userDao)
    {
        $userDao->findUserById(1);
    }
    
    public function setCI($ci)
    {
        $this->CI = $ci;
    }
}

// Middleware
class DebugFilter implements \Dispatcher\Common\CodeIgniterAware
{
    public function processRequest(Dispatcher\Http\HttpRequestInterface $req)
    {
        $cipher = $this->CI->encrypt->encode('plaintext', 'key');
    }

    public function processResponse(Dispatcher\Http\HttpResponseInterface $res)
    {
        // do something
    }
    
    public function setCI($ci)
    {
        $this->CI = $ci;
    }
}
```


Configurations
--------------

There are two configuration files, `config/dispatcher.php` and `config/dependencies.php`.

##### dispatcher.php #####

```php
<?php

$config['middlewares'] = array(
    'MyProject\\Namespace\\Middlewares\\SomeFilter',
    'debug_filter'
);

$config['debug'] = true;
```

`debug`:

    Whether to show or hide debug information.
    Set to `true` to show exception.
    Set to `false` to return error 404 response when stuff gone wrong.


`middlewares`:

    An array of middleware class(es) to be processed before/after dispatch.
    When specifying the middlewares, it can be a fully qualified class name if it is autoloaded, otherwise
the class must live under `application/middlewares/` in order for CI-Dispatcher to load it (Note: naming convention applies).


##### dependencies.php #####

This configuration file is used for `DIContainer` to load dependencies and inject them
into Middlewares, DispatchableController and DispatchableResource's constructor.

`DIContainer` is a copy cat of [`Pimple`](http://pimple.sensiolabs.org/).

```php
<?php

$config['container'] = array();
$config['sharedContainer'] = array();


$config['container']['dsnString'] = 'dsn:user@192.168.1.100';
$config['container']['userDao'] = function($c) {
    return new UserDao($c['dsnString']);
};
```

Note:

`container` can have anonymous function or simple value like string, array, etc...

`sharedContainer` must contian only anonymous function.
