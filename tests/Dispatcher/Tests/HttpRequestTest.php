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
        $this->request = new \Dispatcher\HttpRequest(get_instance());
        if ($this->request === NULL) {
            $this->fail();
        }
    }

    /**
     * @test
     */
    public function isCli_InUnitTest_ShouldReturnTrue()
    {
        $this->assertTrue($this->request->isCli());
    }

    /**
     * @test
     */
    public function isAjax_InUnitTest_ShouldReturnFalse()
    {
        $this->assertFalse($this->request->isAjax());
    }

    /**
     * @test
     */
    public function getId_WithMultipleCall_ShouldReturnSameId()
    {
        $id = $this->request->getId();

        $another = $this->request->getId();
        $this->assertEquals($id, $another);
    }

    /**
     * @test
     */
    public function getIp_OnInvalidHost_ShouldNotBeEmpty()
    {
        $this->assertNotEmpty($this->request->getIp());
    }

    /**
     * @test
     */
    public function GET_FromSuperGlobal_ShouldBeEqual()
    {
        $_GET['var1'] = 'var1';
        $this->assertEquals($_GET['var1'], $this->request->GET('var1'));
    }

    /**
     * @test
     */
    public function GET_OnInvalidKey_ShouldReturnDefaultValue()
    {
        $defaultValue = 'asdf';
        $_GET['key'] = 'value';

        $this->assertEquals($defaultValue,
            $this->request->GET('invalidKey', $defaultValue));
    }

    /**
     * @test
     */
    public function POST_FromSuperGlobal_ShouldBeEqual()
    {
        $_POST['var1'] = 'var1';
        $this->assertEquals($_POST['var1'], $this->request->POST('var1'));
    }

    /**
     * @test
     */
    public function POST_OnInvalidKey_ShouldReturnDefaultValue()
    {
        $defaultValue = 'somevalue';
        $_POST['key'] = 'value';
        $this->assertEquals($defaultValue,
            $this->request->POST('invalidKey', $defaultValue));
    }

    /**
     * @test
     */
    public function getParam_FromSuperGlobal_ShouldHavePostPrecedence()
    {
        $expected = '2';
        $_GET['var1'] = '1';
        $_POST['var1'] = $expected;
        $this->assertEquals($expected, $this->request->getParam('var1'));
    }

    /**
     * @test
     */
    public function getParam_WithSanitizeOnAmpersand_ShouldAppendSemiColon()
    {
        $_POST['key'] = '&thiswilldo';
        $this->assertEquals('&thiswilldo;',
            $this->request->getParam('key', null, true));
    }

    /**
     * @test
     */
    public function getScheme_InUnitTest_ShouldReturnHttp()
    {
        $this->assertEquals('HTTP', $this->request->getScheme());
    }

    /**
     * @test
     */
    public function getCookie_FromSuperGlobal_ShouldBeEqual()
    {
        $_COOKIE['somecookie'] = 'value';
        $this->assertEquals('value', $this->request->getCookie('somecookie'));
    }

    /**
     * @test
     */
    public function getUserAgent_InUnitTest_ShouldBeEmpty()
    {
        $this->assertEquals('', $this->request->getUserAgent());
    }

    /**
     * @test
     */
    public function getUri_InUnitTest_ShouldBeEmpty()
    {
        $this->assertEmpty($this->request->getUri());
    }

    /**
     * @test
     */
    public function getBaseUrl_InUnitTest_ShouldDefaultToLocalHost()
    {

        $this->assertEquals('http://localhost/', $this->request->getBaseUrl());
    }
}
