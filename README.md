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
        $this->load->view('welcome_message', array('title' => 'Welcome!'));
    }
}
```

With CodeIgniter-Dispatcher's class-based controller

```php
<?php

class Index extends Dispatcher\DispatchableController
{
    protected $views = 'welcome_message';

    public function get($request)
    {
        return $this->renderView(array('title' => 'Welcome!'));
    }
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
    Dispatcher\BootstrapInstaller::run($route);
    ```

`Dispatcher\BootstrapInstaller::run($route);` will create 3 files inside of CodeIgniter's
`APPPATH`, if you want to skip the file creation, add `TRUE` to to skip the file check.

    ```php
    Dispatcher\BootstrapInstaller::run($route, TRUE);
    ```
