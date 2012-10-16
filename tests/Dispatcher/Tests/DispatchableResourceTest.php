<?php
namespace Dispatcher\Tests;

class DispatchableResourceTest extends \PHPUnit_Framework_Testcase
{
    /**
     * @test
     * @expectedException LogicException
     */
    public function doDispatch_WithHalfAssBakedApi_ShouldThrowException()
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

        $controller->doDispatch($reqMock);
    }
}
