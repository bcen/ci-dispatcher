<?php
namespace Dispatcher\Tests;

class DispatchableControllerTest extends \PHPUnit_Framework_Testcase
{
    /**
     * @test
     */
    public function get_WithViewAndData_ShouldReturnViewTemplateResponseWithSameViewAndData()
    {
        $requestMock = $this->getMock('Dispatcher\\HttpRequestInterface');

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

        $this->assertInstanceOf('Dispatcher\\ViewTemplateResponse', $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(array('message' => 'Hey'), $response->getData());
        $this->assertContains('index', $response->getViews());
    }

    /**
     * @test
     * @expectedException LogicException
     * @expectedExceptionMessage No views defined.
     */
    public function get_WithoutView_ShouldThrowLogicException()
    {
        $requestMock = $this->getMock('Dispatcher\\HttpRequestInterface');

        $controller = $this->getMock(
            'Dispatcher\\DispatchableController', array('getContextData'));
        $controller->expects($this->once())
            ->method('getContextData')
            ->will($this->returnValue(array('message' => 'Hey')));

        $controller->get($requestMock);
    }

    /**
     * @test
     * @expectedException ReflectionException
     */
    public function doDispatch_OnInvalidRequestMethod_ShouldThrowReflectionException()
    {
        $requestMock = $this->getMock('Dispatcher\\HttpRequest',
            array('getMethod'), array(get_instance()));
        $requestMock->expects($this->any())
            ->method('getMethod')
            ->will($this->returnValue('POST'));

        $controller = $this->getMock(
            'Dispatcher\\DispatchableController', array('getViews'));
        $controller->expects($this->never())
            ->method('getViews')
            ->will($this->returnValue(array('index')));

        $controller->doDispatch($requestMock, array());
    }

    /**
     * @test
     */
    public function doDispatch_OnValidRequestMethod_ShouldReturnValidResponse()
    {
        $requestMock = $this->getMock('Dispatcher\\HttpRequest',
            array('getMethod'), array(get_instance()));
        $requestMock->expects($this->any())
            ->method('getMethod')
            ->will($this->returnValue('GET'));

        $controller = $this->getMock(
            'Dispatcher\\DispatchableController', array('get'));
        $controller->expects($this->once())
            ->method('get')
            ->with($this->isInstanceOf('Dispatcher\\HttpRequestInterface'))
            ->will($this->returnValue(\Dispatcher\JsonResponse::create()));

        $response = $controller->doDispatch($requestMock, array());
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @test
     * @expectedException LogicException
     */
    public function doDispatch_WithoutExpectedParams_ShouldThrowLogicException()
    {
        $requestMock = $this->getMock('Dispatcher\\HttpRequest',
            array('getMethod'), array(get_instance()));
        $requestMock->expects($this->any())
            ->method('getMethod')
            ->will($this->returnValue('POST'));

        $controller = $this->getMock(
            'Dispatcher\\DispatchableController', array('post'));
        $controller->expects($this->any())
            ->method('post')
            ->with($this->isInstanceOf('Dispatcher\\HttpRequestInterface'))
            ->will($this->returnValue(\Dispatcher\JsonResponse::create()));

        $controller->doDispatch($requestMock, array());
    }
}
