<?php
namespace Dispatcher\Tests;

use Dispatcher\JsonResponse;

class BootstrapControllerTest extends \PHPUnit_Framework_TestCase
{
    public function getUri()
    {
        return array(
            array('method', array('api', 'v1', 'books')),
            array('method', array('api', 'v1')),
            array('method', array())
        );
    }

    /**
     * @test
     * @dataProvider getUri
     */
    public function _remap_OnAnyUri_ShouldCallRenderResponseWithRequestAndResponse($method, $uri)
    {
        $completeUri = $uri;
        array_unshift($completeUri, $method);

        // setup mock
        $controller = $this->getMock('Dispatcher\\BootstrapController',
            array('loadMiddlewares', 'dispatch', 'renderResponse'));
        $controller->expects($this->any())
            ->method('loadMiddlewares')
            ->will($this->returnValue(array()));
        $controller->expects($this->any())
            ->method('dispatch')
            ->with($this->equalTo($completeUri))
            ->will($this->returnValue(\Dispatcher\JsonResponse::create()));
        $controller->expects($this->any())
            ->method('renderResponse')
            ->with($this->isInstanceOf('Dispatcher\\HttpRequestInterface'),
                   $this->isInstanceOf('Dispatcher\\HttpResponseInterface'));

        // run
        $controller->_remap($method, $uri);
    }

    /**
     * @test
     */
    public function loadMiddleware_IncludesNamespace_ShouldCallLoadClass()
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

        $constraints = $this->logicalAnd(
            $this->isInstanceOf('Dispatcher\\ClassInfo'),
            $this->attributeEqualTo('name',
                                    'Dispatcher\\Tests\Stub\\MiddlewareSpy'),
            $this->attributeEqualTo('path', ''));
        $ctrl->expects($this->once())
            ->method('loadClass')
            ->with($constraints);

        $ctrl->_remap('method', array('api', 'v1', 'books'));
    }

    /**
     * @test
     */
    public function loadMiddleware_WithoutNamespace_ShouldDefaultToMiddlewareDir()
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

        $constraints = $this->logicalAnd(
            $this->isInstanceOf('Dispatcher\\ClassInfo'),
            $this->attributeEqualTo('name', 'Debug_Filter'),
            $this->attributeEqualTo('path',
                APPPATH . 'middlewares/filters/debug_filter.php'));
        $ctrl->expects($this->once())
            ->method('loadClass')
            ->with($constraints);

        $ctrl->_remap('method', array('api', 'v1', 'books'));
    }

    /**
     * @test
     */
    public function dispatch_OnNonexistentURI_ShouldReturnError404Response()
    {
        $ctrl = $this->getMock('Dispatcher\\BootstrapController',
            array('renderResponse'));

        $ctrl->expects($this->once())
            ->method('renderResponse')
            ->with($this->isInstanceOf('Dispatcher\\HttpRequestInterface'),
                   $this->isInstanceOf('Dispatcher\\Error404Response'));

        $ctrl->_remap('method', array('api', 'v1', 'books'));
    }

    /**
     * @test
     */
    public function dispatch_OnExistentURI_ShouldReturnNormalResponse()
    {
        $reqMock = $this->getMock('Dispatcher\\HttpRequest', array('getMethod'),
            array(get_instance()));
        $reqMock->expects($this->any())
            ->method('getMethod')
            ->will($this->returnValue('GET'));

        $dispatchableController = $this->getMockForAbstractClass(
            'Dispatcher\\DispatchableController',
            array(),
            '',
            true,
            true,
            true,
            array('getViews'));
        $dispatchableController->expects($this->once())
            ->method('getViews')
            ->will($this->returnValue(array('index')));


        $ctrl = $this->getMock('Dispatcher\\BootstrapController',
            array('renderResponse', 'loadClassInfoOn',
                  'loadClass', 'createHttpRequest'));

        $ctrl->expects($this->once())
            ->method('renderResponse')
            ->with($this->isInstanceOf('Dispatcher\\HttpRequestInterface'),
                   $this->isInstanceOf('Dispatcher\\ViewTemplateResponse'));

        $ctrl->expects($this->once())
            ->method('createHttpRequest')
            ->will($this->returnValue($reqMock));

        $ctrl->expects($this->once())
            ->method('loadClassInfoOn')
            ->will($this->returnValue(new \Dispatcher\ClassInfo('Books', '')));

        $ctrl->expects($this->once())
            ->method('loadClass')
            ->will($this->returnValue($dispatchableController));

        $ctrl->_remap('method', array('api', 'v1', 'books'));
    }
}
