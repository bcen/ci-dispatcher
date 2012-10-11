<?php
namespace Dispatcher\Tests;

use stdClass;

use Dispatcher\ViewTemplateResponse;
use Dispatcher\JsonResponse;
use Dispatcher\Error404Response;

class HttpResponseTest extends \PHPUnit_Framework_Testcase
{
    /**
     * @test
     */
    public function getContentType_OnDefaultViewTemplate_ShouldReturn200StatusCode()
    {
        $response = ViewTemplateResponse::create();
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function setData_FromArray_ShouldReturnSameObjectHash()
    {
        $obj = new stdClass();
        $objHash = spl_object_hash($obj);

        $response = JsonResponse::create()->setContent(array('obj' => $obj));
        $data = $response->getContent();

        $this->assertEquals($objHash, spl_object_hash($data['obj']));
        $response->setContent(NULL);
    }

    /**
     * @test
     */
    public function Error404Response_OnDefault_ShouldReturn404StatusCode()
    {
        $response = new Error404Response();
        $this->assertEquals(404, $response->getStatusCode());

        $response = Error404Response::create();
        $this->assertEquals(404, $response->getStatusCode());
    }
}
