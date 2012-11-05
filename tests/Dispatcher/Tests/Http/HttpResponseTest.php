<?php
namespace Dispatcher\Tests\Http;

use Dispatcher\Http\HttpResponse;

class HttpResponseTestextends extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function getHeader_return_default_for_nonexistent_key()
    {
        $response = new HttpResponse(200, '', array('Location' => 'somewhere'));

        $this->assertNull($response->getHeader('nonexistent'));
        $this->assertEquals('somevalue',
                            $response->getHeader('somekey', 'somevalue'));
    }
}
