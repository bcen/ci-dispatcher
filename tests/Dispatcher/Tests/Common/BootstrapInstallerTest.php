<?php
namespace Dispatcher\Tests\Common;

use Dispatcher\Common\BootstrapInstaller;

class BootstrapInstallerTest extends \PHPUnit_Framework_TestCase
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
