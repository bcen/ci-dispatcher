<?php
namespace Dispatcher\Tests;

use Dispatcher\BootstrapInstaller;

class BootstrapInstallerTest extends \PHPUnit_Framework_Testcase
{
    /**
     * @test
     */
    public function run_OnRouteWithoutInstallation_ShouldAlterRouteWithDefaultController()
    {
        $route = array();
        BootstrapInstaller::run($route, true);
        $this->assertTrue(isset($route['default_controller'], $route['(.*)']));
    }
}
