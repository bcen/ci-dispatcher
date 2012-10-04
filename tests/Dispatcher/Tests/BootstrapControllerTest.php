<?php
namespace Dispatcher\Tests;

use Dispatcher\Tests\Stub\BootstrapControllerLoadMiddlewareSpy;

class BootstrapControllerTest extends \PHPUnit_Framework_TestCase
{
    public function test__remap_OnNormalControlFlow_ShouldPass()
    {
        $ctrl = $this->getMock('Dispatcher\\BootstrapController',
            array('loadMiddlewares', 'dispatch', 'renderResponse'));

        $ctrl->expects($this->once())
            ->method('loadMiddlewares')
            ->will($this->returnValue(array()));

        $ctrl->expects($this->once())
            ->method('dispatch')
            ->with($this->equalTo(array('method', 'api', 'v1', 'books')))
            ->will($this->returnValue(\Dispatcher\JsonResponse::create()));

        $ctrl->expects($this->once())
            ->method('renderResponse')
            ->with($this->isInstanceOf('Dispatcher\\HttpRequestInterface'),
                   $this->isInstanceOf('Dispatcher\\HttpResponseInterface'));

        $ctrl->_remap('method', array('api', 'v1', 'books'));
    }

    public function test_loadMiddlewares_WithoutClasspath_ShouldBeCalled()
    {
        $ctrl = new BootstrapControllerLoadMiddlewareSpy();
        $ctrl->_remap('method', array('api', 'v1', 'books'));
        $this->assertEquals(1, count($ctrl->middlewares));
        $mw = array_pop($ctrl->middlewares);
        $this->assertTrue($mw->processRequestCalled);
        $this->assertTrue($mw->processResponseCalled);
    }
}
