<?php
namespace Dispatcher\Tests;

class DispatchableResourceTest extends \PHPUnit_Framework_Testcase
{
    /**
     * @test
     */
    public function get_WithNoUriSegments_ShouldCallReadCollectionWithResults()
    {
        $reqMock = $this->getMock('Dispatcher\\Http\\HttpRequest',
            array('getMethod'));
        $reqMock->expects($this->any())
            ->method('getMethod')
            ->will($this->returnValue('GET'));

        $controller = $this->getMock('Dispatcher\\DispatchableResource',
            array('readCollection'));
        $controller->expects($this->any())->method('readCollection')
            ->with($this->isInstanceOf(
                'Dispatcher\\Http\\HttpRequestInterface'))
            ->will($this->returnValue(array(array('username' => 'someone'))));

        $response = $controller->get($reqMock);
        $this->assertInstanceOf('Dispatcher\\Http\\HttpResponseInterface',
            $response);
        $this->assertEquals('{"objects":[{"username":"someone"}]}',
            $response->getContent());
    }
}
