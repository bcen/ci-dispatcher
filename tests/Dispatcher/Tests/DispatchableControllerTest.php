<?php
namespace Dispatcher\Tests;

use Dispatcher\Exception\DispatchingException;

class DispatchableControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function doDispatch_with_unavailable_request_handler_should_return_501_response()
    {
        $req = new \Dispatcher\Http\DummyRequest(array('method' => 'HEAD'));

        $sut = $this->getMockBuilder('Dispatcher\\DispatchableController')
            ->setMethods(array('somemethod'))
            ->getMock();

        $response = $sut->doDispatch($req);
        $this->assertEquals(501, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function doDispatch_with_more_args_than_request_handler_expected_should_return_404_response()
    {
        // GET request
        $req = new \Dispatcher\Http\DummyRequest();

        $sut = $this->getMockBuilder('Dispatcher\\DispatchableController')
            ->setMethods(array('somemethod'))
            ->getMock();

        $response = $sut->doDispatch($req, array('id'));
        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function doDispatch_should_throw_DispatchingException_for_invalid_response_from_request_handler()
    {
        // GET request
        $req = new \Dispatcher\Http\DummyRequest();

        $sut = $this->getMockBuilder('Dispatcher\\DispatchableController')
            ->setMethods(array('get'))
            ->getMock();
        $sut->expects($this->once())
            ->method('get')
            ->will($this->returnValue('hey'));

        try {
            $sut->doDispatch($req);
        } catch (DispatchingException $ex) {
            return;
        }

        $this->fail('Expected DispatchingException');
    }

    /**
     * @test
     */
    public function doDispatch_for_default_get_request_handler_should_return_response_with_view_in_response_content()
    {
        $expected = array('index');

        $req = new \Dispatcher\Http\DummyRequest();

        $sut = $this->getMockBuilder('Dispatcher\\DispatchableController')
            ->setMethods(array('getViews'))
            ->getMock();
        $sut->expects($this->once())
            ->method('getViews')
            ->will($this->returnValue($expected));

        $response = $sut->doDispatch($req);

        $this->assertEquals($expected, $response->getContent());
    }
}
