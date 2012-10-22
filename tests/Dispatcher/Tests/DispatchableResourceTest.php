<?php
namespace Dispatcher\Tests;

class DispatchableResourceTest extends \PHPUnit_Framework_TestCase
{
    public function mockRequest($method)
    {
        $mock = $this->getMock('Dispatcher\\Http\\HttpRequest');
        $mock->expects($this->any())
            ->method('getMethod')
            ->will($this->returnValue($method));
        return $mock;
    }

    /**
     * @test
     */
    public function get_WithNoUriSegments_ShouldCallReadCollectionWithResults()
    {
        $reqMock = $this->mockRequest('GET');

        $controller = $this->getMock('Dispatcher\\DispatchableResource',
            array('readCollection'));
        $controller->expects($this->any())
            ->method('readCollection')
            ->with($this->isInstanceOf(
                'Dispatcher\\Http\\HttpRequestInterface'))
            ->will($this->returnValue(array(
                array('username' => 'someone'), array('username' => 'someoneelse'))));

        $response = $controller->get($reqMock);

        $this->assertEquals(
            '{"meta":{"offset":0,"limit":20,"total":2},"objects":[{"username":"someone"},{"username":"someoneelse"}]}',
            $response->getContent());
    }

    /**
     * @test
     */
    public function get_WithNoUriSegments_ShouldCallReadCollectionWithEmptyResults()
    {
        $reqMock = $this->mockRequest('GET');

        $controller = $this->getMock('Dispatcher\\DispatchableResource',
            array('readCollection'));
        $controller->expects($this->any())
            ->method('readCollection')
            ->with($this->isInstanceOf(
            'Dispatcher\\Http\\HttpRequestInterface'))
            ->will($this->returnValue(array()));

        $response = $controller->get($reqMock);

        $this->assertEquals(
            '{"meta":{"offset":0,"limit":20,"total":0},"objects":[]}',
            $response->getContent());
    }
}
