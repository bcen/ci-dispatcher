<?php
namespace Dispatcher\Tests;

use Dispatcher\Tests\Stub\SimpleBootstrapControllerStub;

class BootstrapControllerTest extends \PHPUnit_Framework_TestCase
{
    private $controller;

    public function setUp()
    {
        $this->controller = new SimpleBootstrapControllerStub();
        if($this->controller === null) {
            $this->fail();
        }
    }

    public function test_test_ShouldNotFail()
    {
        $this->assertTrue(1 == 1);
    }
}
