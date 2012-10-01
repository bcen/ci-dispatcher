<?php
namespace Dispatcher\Tests;

use Dispatcher\ViewTemplateResponse;
use Dispatcher\JsonResponse;
use Dispatcher\Error404Response;

class HttpResponseTest extends \PHPUnit_Framework_Testcase
{
    /**
     * @var \Dispatcher\HttpResponseInterface
     */
    private $vtResponse;

    public function setUp()
    {
        $this->vtResponse = new ViewTemplateResponse();

        if ($this->vtResponse === NULL) {
            $this->fail();
        }
    }

    public function testDefaultValues()
    {
        $this->assertEquals('text/html', $this->vtResponse->getContentType());
        $this->assertEquals(200, $this->vtResponse->getStatusCode());
    }

    public function testJsonResponse()
    {
        $obj = new \stdClass();
        $objHash = spl_object_hash($obj);
        $response = JsonResponse::create()->setData(array('obj' => $obj));
        $data = $response->getData();
        $this->assertEquals($objHash, spl_object_hash($data['obj']));
        $this->assertEquals('application/json', $response->getContentType());
    }

    public function testErrorResponse()
    {
        $response = new Error404Response();
        $this->assertEquals(404, $response->getStatusCode());

        $response = Error404Response::create();
        $this->assertEquals(404, $response->getStatusCode());
    }
}
