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
            ->with($this->anything(), $this->equalTo('schema'));

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

    /**
     * @test
     */
    public function get_for_readObject_should_have_correct_serialized_contents_in_response()
    {
        $reqMock = $this->mockRequest('GET');

        $controller = $this->getMock('Dispatcher\\DispatchableResource',
            array('readObject'));

        $controller->expects($this->once())
            ->method('readObject')
            ->will($this->returnValue(
                array('username' => 'someone', 'id' => 5)));

        $response = $controller->get($reqMock, array('id'));
        $this->assertEquals(
            '{"username":"someone","id":5}',
            $response->getContent());
    }

    /**
     * @test
     */
    public function resource_not_found_in_readObject_should_have_404_in_response()
    {
        $reqMock = $this->mockRequest('GET');

        $controller = $this->getMock('Dispatcher\\DispatchableResource',
            array('readObject'));

        $controller->expects($this->once())
            ->method('readObject')
            ->will($this->throwException(
                new \Dispatcher\Exception\ResourceNotFoundException()));


        $response = $controller->get($reqMock, array('id'));

        $this->assertEquals(
            '{"error":"Not Found"}',
            $response->getContent());
        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function invoke_post_without_uri_segments_should_invoke_writeObject()
    {
        $reqMock = $this->mockRequest('POST');

        $controller = $this->getMock('Dispatcher\\DispatchableResource',
            array('writeObject'));
        $controller->expects($this->once())
            ->method('writeObject');

        $controller->post($reqMock);
    }

    /**
     * @test
     */
    public function invoke_post_with_uri_segments_should_return_method_not_allowed_response()
    {
        $reqMock = $this->mockRequest('POST');

        $controller = $this->getMock('Dispatcher\\DispatchableResource',
            array('writeObject'));
        $controller->expects($this->never())
            ->method('writeObject');

        $response = $controller->post($reqMock, array('someid'));
        $this->assertEquals(405, $response->getStatusCode());
    }
}
