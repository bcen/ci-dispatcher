<?php
namespace Dispatcher\Tests;

class DispatchableResourceTest extends \PHPUnit_Framework_Testcase
{
    /**
     * @test
     */
    public function doDispatch_WithoutArgsOnGet_ShouldCallReadList()
    {
        $reqMock = $this->getMock('Dispatcher\\HttpRequest',
            array('getMethod'));
        $reqMock->expects($this->any())
            ->method('getMethod')
            ->will($this->returnValue('GET'));

        $controller = $this->getMock('Dispatcher\\DispatchableResource',
            array('getContextData', 'readList'));
        $controller->expects($this->any())
            ->method('getContextData');
        $controller->expects($this->any())
            ->method('readList');

        $controller->doDispatch($reqMock);
    }

    /**
     * @test
     */
    public function doDispatch_WithArgsOnGet_ShouldCallReadDetail()
    {
        $reqMock = $this->getMock('Dispatcher\\HttpRequest',
            array('getMethod'));
        $reqMock->expects($this->any())
            ->method('getMethod')
            ->will($this->returnValue('GET'));

        $controller = $this->getMock('Dispatcher\\DispatchableResource',
            array('getContextData', 'readDetail'));
        $controller->expects($this->any())
            ->method('getContextData');
        $controller->expects($this->any())
            ->method('readDetail')
            ->with($this->anything(), $this->equalTo('1'));

        $controller->doDispatch($reqMock, array('1'));
    }

    /**
     * @test
     */
    public function doDispatch_WithoutArgsOnInvalidMethod_ShouldReturnJsonResponseWith404()
    {
        $reqMock = $this->getMock('Dispatcher\\HttpRequest',
            array('getMethod'));
        $reqMock->expects($this->any())
            ->method('getMethod')
            ->will($this->returnValue('INVALID_METHOD'));

        $controller = $this->getMock('Dispatcher\\DispatchableResource',
            array('getContextData'));
        $controller->expects($this->any())
            ->method('getContextData');

        $response = $controller->doDispatch($reqMock);
        $this->assertInstanceOf('Dispatcher\\JsonResponse', $response);
        $this->assertEquals(404, $response->getStatusCode());
    }
}
