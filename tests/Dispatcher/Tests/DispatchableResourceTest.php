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

        $controller->expects($this->any())
            ->method('readCollection')
            ->with($this->isInstanceOf(
                'Dispatcher\\Http\HttpRequestInterface'));


        $controller->get($reqMock);
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
    public function invoke_get_should_serialize_objects_from_readCollection_to_response_content()
    {
        $reqMock = $this->mockRequest('GET');

        $controller = $this->getMock('Dispatcher\\DispatchableResource',
            array('readCollection'));

        $controller->expects($this->any())
            ->method('readCollection')
            ->will($this->returnValue(array(
                array('username' => 'someone'),
                array('username' => 'someoneelse'),
                array('username' => 'anotherguy'))));

        $response = $controller->get($reqMock);

        $this->assertEquals(
            '{"meta":{"offset":0,"limit":20,"total":3},"objects":[{"username":"someone"},{"username":"someoneelse"},{"username":"anotherguy"}]}',
            $response->getContent());
    }

    /**
     * @test
     */
    public function invoke_get_should_serialize_only_objects_from_readCollection_with_the_page_limits_to_response_content()
    {
        $reqMock = $this->mockRequest('GET');

        $options = new \Dispatcher\Common\DefaultResourceOptions();
        $options->setPageLimit(1);

        $controller = $this->getMock('Dispatcher\\DispatchableResource',
            array('readCollection', 'getOptions'));

        $controller->expects($this->any())
            ->method('readCollection')
            ->will($this->returnValue(array(
                    array('username' => 'someone'),
                    array('username' => 'someoneelse'),
                    array('username' => 'anotherguy'))));

        $controller->expects($this->any())
            ->method('getOptions')
            ->will($this->returnValue($options));

        $response = $controller->get($reqMock);

        $this->assertEquals(
            '{"meta":{"offset":0,"limit":1,"total":3},"objects":[{"username":"someone"}]}',
            $response->getContent());
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
            ->method('readSchema')
            ->will($this->returnValue(
                array('username' => array('helpText' => 'something'))));

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
            ->method('readObject')
            ->will($this->returnValue(null));

        $controller->get($reqMock, array('some-id'));
    }
}
