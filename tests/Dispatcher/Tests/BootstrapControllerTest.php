<?php
namespace Dispatcher\Tests;

use Dispatcher\Http\JsonResponse;
use Dispatcher\Common\ClassInfo;

class BootstrapControllerTest extends \PHPUnit_Framework_Testcase
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
    public function initializeConfig_WithNullConfig_ShouldPass($method, $uri)
    {
        $controller = $this->getMock('Dispatcher\\BootstrapController',
            array('loadDispatcherConfig', 'renderResponse', 'dispatch',
                  'createContainer'));
        $controller->expects($this->any())
            ->method('renderResponse');
        $controller->expects($this->any())
            ->method('dispatch')
            ->will($this->returnValue(new JsonResponse()));
        $controller->expects($this->once())
            ->method('loadDispatcherConfig')
            ->will($this->returnValue(null));
        $controller->expects($this->once())
            ->method('createContainer');

        $controller->_remap($method, $uri);
    }

    /**
     * @test
     * @dataProvider getUri
     */
    public function _remap_OnAnyUri_ShouldCallRenderResponseWithRequestAndResponse($method, $uri)
    {
        $completeUri = $uri;
        array_unshift($completeUri, $method);

        $requestClass = 'Dispatcher\\Http\\HttpRequestInterface';
        $responseClass = 'Dispatcher\\Http\\HttpResponseInterface';

        // setup mock
        $controller = $this->getMock('Dispatcher\\BootstrapController',
            array('loadMiddlewares', 'dispatch', 'renderResponse'));
        $controller->expects($this->any())
            ->method('loadMiddlewares')
            ->will($this->returnValue(array()));
        $controller->expects($this->any())
            ->method('dispatch')
            ->with($this->anything(), $this->equalTo($completeUri))
            ->will($this->returnValue(new JsonResponse()));
        $controller->expects($this->any())
            ->method('renderResponse')
            ->with($this->isInstanceOf($requestClass),
                   $this->isInstanceOf($responseClass));

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
            ->will($this->returnValue(new JsonResponse()));

        $ctrl->expects($this->once())
            ->method('loadDispatcherConfig')
            ->will($this->returnValue(array(
                'middlewares' => array(
                    'Dispatcher\\Tests\\Stub\\MiddlewareSpy'
                ),
                'debug' => false)));

        $constraints = $this->logicalAnd(
            $this->isInstanceOf('Dispatcher\\Common\\ClassInfo'),
            $this->attributeEqualTo('name',
                                    'Dispatcher\\Tests\\Stub\\MiddlewareSpy'),
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
            ->will($this->returnValue(new JsonResponse()));

        $ctrl->expects($this->once())
            ->method('loadDispatcherConfig')
            ->will($this->returnValue(array(
            'middlewares' => array(
                'filters/debug_filter'
            ),
            'debug' => false)));

        $constraints = $this->logicalAnd(
            $this->isInstanceOf('Dispatcher\\Common\\ClassInfo'),
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

        $ctrl->expects($this->once())->method('renderResponse')->with(
            $this->isInstanceOf('Dispatcher\\Http\\HttpRequestInterface'),
            $this->isInstanceOf('Dispatcher\\Http\\Error404Response'));

        $ctrl->_remap('method', array('api', 'v1', 'books'));
    }

    /**
     * @test
     */
    public function dispatch_OnExistentURI_ShouldReturnNormalResponse()
    {
        $reqMock = $this->getMock('Dispatcher\\Http\\HttpRequest',
            array('getMethod'));
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

        $ctrl->expects($this->once())->method('renderResponse')->with(
            $this->isInstanceOf('Dispatcher\\Http\\HttpRequestInterface'),
            $this->isInstanceOf('Dispatcher\\Http\\ViewTemplateResponse'));

        $ctrl->expects($this->once())
            ->method('createHttpRequest')
            ->will($this->returnValue($reqMock));

        $ctrl->expects($this->once())
            ->method('loadClassInfoOn')
            ->will($this->returnValue(new ClassInfo('Books', '')));

        $ctrl->expects($this->once())
            ->method('loadClass')
            ->will($this->returnValue($dispatchableController));

        $ctrl->_remap('method', array('api', 'v1', 'books'));
    }
}
