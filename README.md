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

4. Include `autoload.php` to your project and add this to `routes.php`
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

```php
// dependencies.php
$config['container']['userDao'] = function($container) {
    return new UserDao();
};

// user_status.php
<?php

class User_Status extends \Dispatcher\DispatchableController
{
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

`processRequest` and `processResponse` are optional, middleware can implement either or both
to alter the request/response cycle.


##### CodeIgniter Awareness #####

Any class instantiation that is managed by the Dispatcher can implement `CodeIgniterAware`
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
