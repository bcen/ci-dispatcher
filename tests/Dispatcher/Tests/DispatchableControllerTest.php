<?php
namespace Dispatcher\Tests;

class DispatchableControllerTest extends \PHPUnit_Framework_Testcase
{
    /**
     * @test
     */
    public function GET_WithIndexViewAndData_ShouldReturnViewTemplateResponse()
    {
        $requestMock = $this->getMock('Dispatcher\\HttpRequestInterface');

        $controller = $this->getMockForAbstractClass(
            'Dispatcher\\DispatchableController',
            array(),
            '',
            true,
            true,
            true,
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
     */
    public function doDispatch_OnInvalidRequestMethod_ShouldReturnError404()
    {
        $request = $this->getMock('Dispatcher\\HttpRequest',
            array('getMethod'));
        $request->expects($this->any())
            ->method('getMethod')
            ->will($this->returnValue('POST'));

        $controller = $this->getMockForAbstractClass(
            'Dispatcher\\DispatchableController',
            array(),
            '',
            true,
            true,
            true,
            array('getViews'));
        $controller->expects($this->never())
            ->method('getViews')
            ->will($this->returnValue(array('index')));

        $response = $controller->doDispatch($request, array(), true);
        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function doDispatch_OnValidRequestMethod_ShouldReturnValidResponse()
    {
        $request = $this->getMock('Dispatcher\\HttpRequest',
            array('getMethod'));
        $request->expects($this->any())
            ->method('getMethod')
            ->will($this->returnValue('GET'));

        $controller = $this->getMockForAbstractClass(
            'Dispatcher\\DispatchableController',
            array(),
            '',
            true,
            true,
            true,
            array('getViews', 'get'));
        $controller->expects($this->never())
            ->method('getViews')
            ->will($this->returnValue(array('index')));
        $controller->expects($this->once())
            ->method('get')
            ->with($this->isInstanceOf('Dispatcher\\HttpRequestInterface'))
            ->will($this->returnValue(\Dispatcher\JsonResponse::create()));

        $response = $controller->doDispatch($request, array(), true);
        $this->assertEquals(200, $response->getStatusCode());
    }
}
