<?php
namespace Dispatcher\Tests;

class BootstrapControllerTest extends \PHPUnit_Framework_TestCase
{
    public function test__remap_OnNormalControlFlow_ShouldPass()
    {
        $ctrl = $this->getMock('Dispatcher\\BootstrapController',
            array('loadMiddlewares', 'dispatch', 'renderResponse'),
            array(),
            '',
            false);

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

        $this->assertTrue(1 == 1);
    }
}
