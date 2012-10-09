<?php
namespace Dispatcher\Tests;

class DispatchableControllerTest extends \PHPUnit_Framework_Testcase
{
    /**
     * @var \Dispatcher\DispatchableController
     */
    private $controller;

    public function test_GET_OnRequest_ShouldReturn200ResponseWithIndexViews()
    {
        $requestMock = $this->getMock('Dispatcher\\HttpRequestInterface');
        $ctrl = $this->getMockForAbstractClass(
            'Dispatcher\\DispatchableController',
            array(),
            '',
            true,
            true,
            true,
            array('getViews'));
        $ctrl->expects($this->once())
            ->method('getViews')
            ->will($this->returnValue(array('index')));

        $response = $ctrl->get($requestMock);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('index', $response->getViews());
    }

    public function test_doDispatch_OnInvalidRequestMethod_ShouldReturnError404()
    {
        $requestMock = $this->getMock('Dispatcher\\HttpRequest',
            array('getMethod'));
        $requestMock->expects($this->any())
            ->method('getMethod')
            ->will($this->returnValue('SOMETHING'));

        $ctrl = $this->getMockForAbstractClass(
            'Dispatcher\\DispatchableController',
            array(),
            '',
            true,
            true,
            true,
            array('getViews'));
        $ctrl->expects($this->never())
            ->method('getViews')
            ->will($this->returnValue(array('index')));

        $response = $ctrl->doDispatch($requestMock, array(), true);
        $this->assertEquals(404, $response->getStatusCode());
    }
}
