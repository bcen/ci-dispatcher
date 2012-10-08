<?php
namespace Dispatcher\Tests;

use Dispatcher\JsonResponse;

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

    public function test_loadMiddleware_IncludesNamespace_ShouldCallLoadClass()
    {
        $ctrl = $this->getMock('Dispatcher\\BootstrapController',
            array('dispatch', 'renderResponse',
                'loadDispatcherConfig', 'loadClass'));

        $ctrl->expects($this->once())
            ->method('dispatch')
            ->will($this->returnValue(JsonResponse::create()));

        $ctrl->expects($this->once())
            ->method('loadDispatcherConfig')
            ->will($this->returnValue(array(
                'middlewares' => array(
                    'Dispatcher\\Tests\Stub\\MiddlewareSpy'
                ),
                'debug' => false)));

        $arg0Constrains = $this->logicalAnd(
            $this->equalTo('Dispatcher\\Tests\Stub\\MiddlewareSpy'),
            $this->classHasAttribute('processRequestCalled'),
            $this->classHasAttribute('processResponseCalled'));

        $ctrl->expects($this->once())
            ->method('loadClass')
            ->with($arg0Constrains, $this->isEmpty());

        $ctrl->_remap('method', array('api', 'v1', 'books'));
    }

    public function test_loadMiddleware_WithoutNamespace_ShouldDefaultToMiddlewareDir()
    {
        $ctrl = $this->getMock('Dispatcher\\BootstrapController',
            array('dispatch', 'renderResponse',
                'loadDispatcherConfig', 'loadClass'));

        $ctrl->expects($this->once())
            ->method('dispatch')
            ->will($this->returnValue(JsonResponse::create()));

        $ctrl->expects($this->once())
            ->method('loadDispatcherConfig')
            ->will($this->returnValue(array(
            'middlewares' => array(
                'filters/debug_filter'
            ),
            'debug' => false)));

        $ctrl->expects($this->once())
            ->method('loadClass')
            ->with($this->equalTo('Debug_Filter'),
                   $this->equalTo(
                       APPPATH . 'middlewares/filters/debug_filter.php'));

        $ctrl->_remap('method', array('api', 'v1', 'books'));
    }
}
