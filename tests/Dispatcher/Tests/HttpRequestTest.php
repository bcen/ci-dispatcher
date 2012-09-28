<?php
namespace Dispatcher\Tests;

class HttpRequestTest extends \PHPUnit_Framework_Testcase
{
    /**
     * @var \Dispatcher\HttpRequestInterface
     */
    private $request;

    public function setUp()
    {
        $this->request = new \Dispatcher\HttpRequest();
        if ($this->request === NULL) {
            $this->fail();
        }
    }

    public function testIsCli()
    {
        $this->assertTrue($this->request->isCli());
        $this->assertFalse($this->request->isAjax());
    }

    public function testGetId()
    {
        $id = $this->request->getId();
        $this->assertNotEmpty($id);

        $another = $this->request->getId();
        $this->assertEquals($id, $another);
    }

    public function testGetIp()
    {
        $this->assertNotEmpty($this->request->getIp());
        $this->assertNotEmpty($this->request->getIpv4());

        try {
            $this->request->getIpv6();
        } catch (\InvalidArgumentException $ex) {
            return;
        }

        $this->fail('IPv6 is supported?');
    }

    public function testGET()
    {
        $_GET['var1'] = 'var1';
        $this->assertEquals($_GET['var1'], $this->request->GET('var1'));
        $this->assertEquals($_GET['var1'], $this->request->getParam('var1'));
        $this->assertEquals('default', $this->request->GET('var2', 'default'));
    }

    public function testPOST()
    {
        $_POST['var1'] = 'var1';
        $this->assertEquals($_POST['var1'], $this->request->POST('var1'));
        $this->assertEquals($_POST['var1'], $this->request->getParam('var1'));
        $this->assertEquals('default', $this->request->POST('var2', 'default'));
    }

    public function testGetScheme()
    {
        $this->assertEquals('HTTP', $this->request->getScheme());
    }
}
