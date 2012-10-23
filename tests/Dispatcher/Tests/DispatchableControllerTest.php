<?php
namespace Dispatcher\Tests;

use Dispatcher\Http\JsonResponse;
use Dispatcher\Exception\DispatchingException;

class DispatchableControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function get_WithViewAndData_ShouldReturnViewTemplateResponseWithSameViewAndData()
    {
        $requestMock = $this->getMock('Dispatcher\\Http\\HttpRequestInterface');

        $controller = $this->getMock(
            'Dispatcher\\DispatchableController',
            array('getViews', 'getContextData'));
        $controller->expects($this->once())
            ->method('getViews')
            ->will($this->returnValue(array('index')));
        $controller->expects($this->once())
            ->method('getContextData')
            ->will($this->returnValue(array('message' => 'Hey')));

        $response = $controller->get($requestMock);

        $this->assertInstanceOf(
            'Dispatcher\\Http\\ViewTemplateResponse',$response);
        $this->assertEquals(array('message' => 'Hey'), $response->getContent());
        $this->assertContains('index', $response->getViews());
    }

    /**
     * @test
     * @expectedException \Dispatcher\Exception\DispatchingException
     * @expectedExceptionMessage No views defined.
     */
    public function get_WithoutView_ShouldThrowDispatchingException()
    {
        $requestMock = $this->getMock('Dispatcher\\Http\\HttpRequestInterface');

        $controller = $this->getMock(
            'Dispatcher\\DispatchableController', array('getContextData'));
        $controller->expects($this->once())
            ->method('getContextData')
            ->will($this->returnValue(array('message' => 'Hey')));

        $controller->get($requestMock);
    }

    /**
     * @test
     */
    public function doDispatch_OnNotImplementedRequestMethod_ShouldReturn501NotImplementedResponse()
    {
        $requestMock = $this->getMock('Dispatcher\\Http\\HttpRequest',
            array('getMethod'));
        $requestMock->expects($this->any())
            ->method('getMethod')
            ->will($this->returnValue('POST'));

        $controller = $this->getMock(
            'Dispatcher\\DispatchableController', array('getViews'));
        $controller->expects($this->never())
            ->method('getViews')
            ->will($this->returnValue(array('index')));

        $response = $controller->doDispatch($requestMock, array());
        $this->assertEquals(501, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function doDispatch_OnValidRequestMethod_ShouldReturn200ValidResponse()
    {
        $requestMock = $this->getMock('Dispatcher\\Http\\HttpRequest',
            array('getMethod'));
        $requestMock->expects($this->any())
            ->method('getMethod')
            ->will($this->returnValue('GET'));

        $controller = $this->getMock(
            'Dispatcher\\DispatchableController', array('get'));
        $controller->expects($this->once())->method('get')->with(
            $this->isInstanceOf('Dispatcher\\Http\\HttpRequestInterface'))
            ->will($this->returnValue(new JsonResponse()));

        $response = $controller->doDispatch($requestMock, array());
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function doDispatch_WithoutExpectedParams_ShouldReturn404ErrorResponse()
    {
        $requestMock = $this->getMock('Dispatcher\\Http\\HttpRequest',
            array('getMethod'));
        $requestMock->expects($this->any())
            ->method('getMethod')
            ->will($this->returnValue('POST'));

        $controller = $this->getMock(
            'Dispatcher\\DispatchableController', array('post'));
        $controller->expects($this->any())->method('post')->with(
            $this->isInstanceOf('Dispatcher\\Http\\HttpRequestInterface'))
            ->will($this->returnValue(new JsonResponse()));

        $response = $controller->doDispatch($requestMock, array());
        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * @test
     * @expectedException \Dispatcher\Exception\DispatchingException
     */
    public function doDispatch_FromNullResponse_ShouldThrowDispatchingException()
    {
        $requestMock = $this->getMock('Dispatcher\\Http\\HttpRequest',
            array('getMethod'));
        $requestMock->expects($this->any())
            ->method('getMethod')
            ->will($this->returnValue('get'));

        $controller = $this->getMock(
            'Dispatcher\\DispatchableController', array('get'));
        $controller->expects($this->any())
            ->method('get')
            ->will($this->returnValue(null));

        $controller->doDispatch($requestMock, array());
    }
}
