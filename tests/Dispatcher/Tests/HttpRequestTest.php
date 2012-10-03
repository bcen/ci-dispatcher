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

    public function test_isCli_InUnitTest_ShouldReturnTrue()
    {
        $this->assertTrue($this->request->isCli());
    }

    public function test_isAjax_InUnitTest_ShouldReturnFalse()
    {
        $this->assertFalse($this->request->isAjax());
    }

    public function test_getId_MultipleCall_ShouldReturnSameId()
    {
        $id = $this->request->getId();

        $another = $this->request->getId();
        $this->assertEquals($id, $another);
    }

    public function test_getIp_OnInvalidHost_ShouldNotBeEmpty()
    {
        $this->assertNotEmpty($this->request->getIp());
    }

    public function test_GET_FromSuperGlobal_ShouldBeEqual()
    {
        $_GET['var1'] = 'var1';
        $this->assertEquals($_GET['var1'], $this->request->GET('var1'));
    }

    public function test_GET_OnInvalidKey_ShouldReturnDefaultValue()
    {
        $_GET['key'] = 'value';
        $this->assertEquals('default',
            $this->request->GET('invalidKey', 'default'));
    }

    public function test_POST_FromSuperGlobal_ShouldBeEqual()
    {
        $_POST['var1'] = 'var1';
        $this->assertEquals($_POST['var1'], $this->request->POST('var1'));
    }

    public function test_POST_OnInvalidKey_ShouldReturnDefaultValue()
    {
        $_POST['key'] = 'value';
        $this->assertEquals('default',
            $this->request->POST('invalidKey', 'default'));
    }

    public function test_getParam_FromSuperGlobal_ShouldHavePostPrecedence()
    {
        $_GET['var1'] = '1';
        $_POST['var1'] = '2';
        $this->assertEquals('2', $this->request->getParam('var1'));
    }

    public function test_getParam_WithSanitizeOnAmpersand_ShouldAppendSemiColon()
    {
        $_POST['key'] = '&thiswilldo';
        $this->assertEquals('&thiswilldo;',
            $this->request->getParam('key', NULL, TRUE));
    }

    public function test_getScheme_InUnitTest_ShouldReturnHttp()
    {
        $this->assertEquals('HTTP', $this->request->getScheme());
    }

    public function test_getCookie_FromSuperGlobal_ShouldBeEqual()
    {
        $_COOKIE['somecookie'] = 'value';
        $this->assertEquals('value', $this->request->getCookie('somecookie'));
    }
}
