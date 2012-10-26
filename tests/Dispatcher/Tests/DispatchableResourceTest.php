<?php
namespace Dispatcher\Tests;

class DispatchableResourceTest extends \PHPUnit_Framework_TestCase
{
    public function mockRequest($method)
    {
        $mock = $this->getMock('Dispatcher\\Http\\HttpRequest',
            array('getMethod'));
        $mock->expects($this->any())
            ->method('getMethod')
            ->will($this->returnValue($method));
        return $mock;
    }

    /**
     * @test
     */
    public function invoke_get_without_uri_segments_should_invoke_readCollection_with_request_as_argument()
    {
        $reqMock = $this->mockRequest('GET');

        $controller = $this->getMock(
            'Dispatcher\\DispatchableResource',
            array('readCollection'));

        $controller->expects($this->once())
            ->method('readCollection')
            ->with($this->isInstanceOf(
                'Dispatcher\\Http\HttpRequestInterface'));


        $controller->get($reqMock);
    }

    /**
     * @test
     */
    public function invoke_get_with_schema_as_argument_and_without_readSchema_should_throw_DispatchingException_with_response()
    {
        $reqMock = $this->mockRequest('GET');

        $controller = $this->getMock('Dispatcher\\DispatchableResource',
            array('some'));
        $controller->expects($this->never())
            ->method('some');

        try {
            $controller->get($reqMock, array('schema'));
        } catch (\Dispatcher\Exception\DispatchingException $ex) {
            $this->assertNotNull($ex->getResponse());
            return;
        }

        $this->fail();
    }

    /**
     * @test
     */
    public function invoke_get_with_uri_segments_and_without_readObject_should_throw_DispatchingException_with_response()
    {
        $reqMock = $this->mockRequest('GET');

        $controller = $this->getMock('Dispatcher\\DispatchableResource',
            array('some'));
        $controller->expects($this->never())
            ->method('some');

        try {
            $controller->get($reqMock, array(1, 2, 3));
        } catch (\Dispatcher\Exception\DispatchingException $ex) {
            $this->assertNotNull($ex->getResponse());
            return;
        }

        $this->fail();
    }

    /**
     * @test
     */
    public function invoke_get_without_uri_segments_and_without_readCollection_should_throw_DispatchingException_with_response()
    {
        $reqMock = $this->mockRequest('GET');

        $controller = $this->getMock('Dispatcher\\DispatchableResource',
            array('some'));
        $controller->expects($this->never())
            ->method('some');

        try {
            $controller->get($reqMock);
        } catch (\Dispatcher\Exception\DispatchingException $ex) {
            $this->assertNotNull($ex->getResponse());
            return;
        }

        $this->fail();
    }

    /**
     * @test
     */
    public function invoke_get_with_schema_as_uri_argument_should_invoke_readSchema()
    {
        $reqMock = $this->mockRequest('GET');

        $controller = $this->getMock('Dispatcher\\DispatchableResource',
            array('readSchema'));

        $controller->expects($this->once())
            ->method('readSchema');

        $controller->get($reqMock, array('schema'));
    }

    /**
     * @test
     */
    public function invoke_get_with_uri_arguments_should_invoke_readObject()
    {
        $reqMock = $this->mockRequest('GET');

        $controller = $this->getMock('Dispatcher\\DispatchableResource',
            array('readObject'));

        $controller->expects($this->once())
            ->method('readObject');

        $controller->get($reqMock, array('some-id'));
    }

    /**
     * @test
     */
    public function invoke_get_with_schema_and_uri_as_arguments_should_invoke_readObject()
    {
        $reqMock = $this->mockRequest('GET');

        $controller = $this->getMock('Dispatcher\\DispatchableResource',
            array('readObject'));

        $controller->expects($this->once())
            ->method('readObject')
            ->with($this->anything(), $this->contains('schema'));

        $controller->get($reqMock, array('schema', 'someargs', 'arg3'));
    }

    /**
     * @test
     */
    public function response_for_readCollection_on_get_should_have_correct_paginated_meta_and_objects()
    {
        $reqMock = $this->mockRequest('GET');

        $options = new \Dispatcher\Common\DefaultResourceOptions();
        $options->setPageLimit(2);

        $controller = $this->getMock('Dispatcher\\DispatchableResource',
            array('readCollection', 'getOptions'));

        $controller->expects($this->any())
            ->method('getOptions')
            ->will($this->returnValue($options));

        $controller->expects($this->once())
            ->method('readCollection')
            ->will($this->returnValue(array(
                array('username' => 'user1'),
                array('username' => 'user2'),
                array('username' => 'user3')
            )));


        $response = $controller->get($reqMock);
        $this->assertEquals(
            '{"meta":{"offset":0,"limit":2,"total":3},"objects":[{"username":"user1"},{"username":"user2"}]}',
            $response->getContent());
    }
}
