<?php
namespace Dispatcher\Tests;

use Dispatcher\Common\DefaultResourceOptions;
use Dispatcher\Exception\DispatchingException;

class DispatchableResourceTest extends \PHPUnit_Framework_Testcase
{
    /**
     * @test
     */
    public function doDispatch_WithNoGetMethodAllowed_ShouldThrowDispatchingExceptionWith405NotAllowedResponse()
    {
        $reqMock = $this->getMock('Dispatcher\\Http\\HttpRequest',
            array('getMethod'));
        $reqMock->expects($this->any())
            ->method('getMethod')
            ->will($this->returnValue('GET'));

        $options = new DefaultResourceOptions();
        $options->setAllowedMethods(array('PUT'));

        $controller = $this->getMock('Dispatcher\\DispatchableResource',
            array('getOptions'));
        $controller->expects($this->any())
            ->method('getOptions')
            ->will($this->returnValue($options));

        try {
            $controller->doDispatch($reqMock);
        } catch (DispatchingException $ex) {
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
        $reqMock = $this->getMock('Dispatcher\\Http\\HttpRequest',
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
