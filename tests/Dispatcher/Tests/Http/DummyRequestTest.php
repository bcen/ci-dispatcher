<?php
namespace Dispatcher\Tests\Http;

class DummyRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Dispatcher\Http\HttpRequestInterface
     */
    private $request;

    public function setUp()
    {
        $this->request = new \Dispatcher\Http\DummyRequest(array(

        ));
    }

    /**
     * @test
     */
    public function getMethod_should_return_default_GET()
    {
        $this->assertEquals('GET', $this->request->getMethod());
    }

    /**
     * @test
     */
    public function isAjax_should_return_false()
    {
        $this->assertFalse($this->request->isAjax());
    }

    /**
     * @test
     */
    public function get_with_no_args_should_return_empty_data()
    {
        $this->assertEmpty($this->request->get());
    }

    /**
     * @test
     */
    public function get_with_args_should_return_default()
    {
        $req = new \Dispatcher\Http\DummyRequest();

        $this->assertNull($req->get('somekey'));
        $this->assertEquals('value', $req->get('somekey', 'value'));
    }
}
