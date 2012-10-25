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
Set `true` to show exception, `false` to return error 404 response when stuff gone wrong.


`middlewares`:  
An array of middleware class(es) to be processed before/after dispatch.  
When specifying the middlewares, it can be a fully qualified class name if it is autoloaded, otherwise
the class must live under `application/middlewares/` in order for CI-Dispatcher to load it (Note: naming convention applies).


##### dependencies.php #####

This configuration file is used for `DIContainer` to load dependencies and inject them
into Middlewares, DispatchableController and DispatchableResource's constructor.  
Note: `DIContainer` is a copy cat of [`Pimple`](http://pimple.sensiolabs.org/).

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


Conventions
-----------

##### URL to Controller mappings #####

URL mapping convention follows almost excatly like CodeIgniter's default strategy.

E.g.  
`http://domain.com/` maps to `application/controllers/index.php` with the class name `Index`  
`http://domain.com/about_me` maps to `application/controllers/about_me.php` with the class name `About_Me`

###### Directory nesting:  
`http://domain.com/blog` and `http://domain.com/blog/ajax/fetch_all_posts` can be mapped to:
```
controllers
|
+-- blog
    |-- index.php
    +-- ajax
        |
        +-- fetch_all_posts.php

```

###### Mapping strategy:  
CI-Dispatcher will search through each URI segments for the exact file name under `application/controllers`.
If it doesn't exists, it will search `index.php` under that URI segments.

E.g.  
`http://domain.com/blog` has the URI segment: `blog`.  
First CI-Dispatcher will search for `application/controllers/blog.php`.  
If it doesn't exists, then it will try for `application/controllers/blog/index.php`.


###### URI variable:  
Sometime URI segments are not fixed, thus we cannot mapped to a directory or class. However,
we can mapped to the function arguments of the request handler.

E.g.
`http://domain.com/blog/posts/this-is-a-crayz-blog-post-about-my-blog/` can be mapped to
`application/controllers/blog/posts.php` with the follow class:
```php
<?php

class Posts extends \Dispatcher\DispatchableController
{
    protected $views = array('header', 'post_body', 'footer');

    // request handler for GET
    public function get($request, $slug)
    {
        $post = $posts->find($slug);
        return $this->renderView(array('post' => $post);
    }

    // request handler for POST
    // providing a default value means that that URI segment is optional.
    // e.g. POST http://domain.com/blog/posts/some-slug
    // or POST http://domain.com/blog/posts
    public function post($request, $slug = null)
    {
        // do something and return response
    }
}
```
Note: The request handler must accept at least one argument for the `Request` object.