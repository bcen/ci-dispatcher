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
    public function get_WithPageLimit_ShouldReturnResultWithinTheLimit()
    {
        $reqMock = $this->mockRequest('GET');

        $options = new \Dispatcher\Common\DefaultResourceOptions();
        $options->setPageLimit(1);

        $controller = $this->getMock('Dispatcher\\DispatchableResource',
            array('readCollection', 'getOptions'));
        $controller->expects($this->any())
            ->method('readCollection')
            ->with($this->isInstanceOf(
                'Dispatcher\\Http\\HttpRequestInterface'))
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
}
