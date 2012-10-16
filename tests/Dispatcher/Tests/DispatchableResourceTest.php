<?php
namespace Dispatcher\Tests;

class DispatchableResourceTest extends \PHPUnit_Framework_Testcase
{
    /**
     * @test
     */
    public function doDispatch_WithNoGetHandler_ShouldThrowDispatchingExceptionWith501NotImplementedResponse()
    {
        $reqMock = $this->getMock('Dispatcher\\HttpRequest',
            array('getMethod'));
        $reqMock->expects($this->any())
            ->method('getMethod')
            ->will($this->returnValue('GET'));

        $controller = $this->getMock('Dispatcher\\DispatchableResource',
            array('dummy'));
        $controller->expects($this->any())
            ->method('dummy');

        try {
            $controller->doDispatch($reqMock);
        } catch (\Dispatcher\DispatchingException $ex) {
            $this->assertEquals(501, $ex->getResponse()->getStatusCode());
            return;
        }

        $this->fail('Expects \\Dispatcher\\DispatchingException');
    }

    /**
     * @test
     */
    public function doDispatch_WithNoGetMethodAllowed_ShouldThrowDispatchingExceptionWith405NotAllowedResponse()
    {
        $reqMock = $this->getMock('Dispatcher\\HttpRequest',
            array('getMethod'));
        $reqMock->expects($this->any())
            ->method('getMethod')
            ->will($this->returnValue('GET'));

        $options = new \Dispatcher\DefaultResourceOptions();
        $options->setAllowedMethods(array('PUT'));

        $controller = $this->getMock('Dispatcher\\DispatchableResource',
            array('getOptions'));
        $controller->expects($this->any())
            ->method('getOptions')
            ->will($this->returnValue($options));

        try {
            $controller->doDispatch($reqMock);
        } catch (\Dispatcher\DispatchingException $ex) {
            $this->assertEquals(405, $ex->getResponse()->getStatusCode());
            return;
        }

        $this->fail('Expects \\Dispatcher\\DispatchingException');
    }

    /**
     * @test
     */
    public function placeholder()
    {
        $reqMock = $this->getMock('Dispatcher\\HttpRequest',
            array('getMethod'));
        $reqMock->expects($this->any())
            ->method('getMethod')
            ->will($this->returnValue('GET'));

        $controller = $this->getMock('Dispatcher\\DispatchableResource',
            array('get'));
        $controller->expects($this->any())
            ->method('get');

        $controller->doDispatch($reqMock, array(':id', 'hey'));
    }
}
