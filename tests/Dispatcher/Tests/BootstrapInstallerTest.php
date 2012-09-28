<?php
namespace Dispatcher\Tests;

class BootstrapInstallerTest extends \PHPUnit_Framework_Testcase
{
    public function testRun()
    {
        $route = array();
        \Dispatcher\BootstrapInstaller::run($route, TRUE);
        $this->assertTrue(isset($route['default_controller'], $route['(.*)']));
    }
}
