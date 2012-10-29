<?php
namespace Dispatcher\Tests\Http;

class HttpRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Dispatcher\Http\HttpRequestInterface
     */
    private $request;

    public function setUp()
    {
        $this->request = new \Dispatcher\Http\HttpRequest();
        if ($this->request === NULL) {
            $this->fail();
        }
    }

    /**
     * @test
     */
    public function isCli_in_cli_should_return_true()
    {
        $this->assertTrue($this->request->isCli());
    }

    /**
     * @test
     */
    public function isAjax_in_cli_should_return_false()
    {
        $this->assertFalse($this->request->isAjax());
    }

    /**
     * @test
     */
    public function invoke_getId_multiple_time_should_return_same_id()
    {
        $id = $this->request->getId();

        $another = $this->request->getId();
        $this->assertEquals($id, $another);
    }

    /**
     * @test
     */
    public function getIp_on_invalid_host_should_not_be_empty()
    {
        $this->assertNotEmpty($this->request->getIp());
    }

    /**
     * @test
     */
    public function fetch_get_from_super_globals_should_return_the_same_value_as_super_globals()
    {
        $_GET['var1'] = 'var1';
        $this->assertEquals($_GET['var1'], $this->request->get('var1'));
    }

    /**
     * @test
     */
    public function fetch_get_with_invalid_key_should_return_default_value()
    {
        $defaultValue = 'asdf';
        $_GET['key'] = 'value';

        $this->assertEquals($defaultValue,
            $this->request->GET('invalidKey', $defaultValue));
    }

    /**
     * @test
     */
    public function fetch_post_from_super_globals_should_return_the_same_value_as_super_globals()
    {
        $_POST['var1'] = 'var1';
        $this->assertEquals($_POST['var1'], $this->request->POST('var1'));
    }

    /**
     * @test
     */
    public function fetch_post_with_invalid_key_should_return_default_value()
    {
        $defaultValue = 'somevalue';
        $_POST['key'] = 'value';
        $this->assertEquals($defaultValue,
            $this->request->POST('invalidKey', $defaultValue));
    }

    /**
     * @test
     */
    public function getParam_from_super_globals_should_have_POST_precedence_than_GET()
    {
        $expected = '2';
        $_GET['var1'] = '1';
        $_POST['var1'] = $expected;
        $this->assertEquals($expected, $this->request->getParam('var1'));
    }

    /**
     * @test
     */
    public function getParam_with_xss_clean_should_append_double_colon_to_output()
    {
        $_POST['key'] = '&thiswilldo';
        $this->assertEquals('&thiswilldo;',
            $this->request->getParam('key', null, true));
    }

    /**
     * @test
     */
    public function getScheme_in_cli_should_return_HTTP()
    {
        $this->assertEquals('HTTP', $this->request->getScheme());
    }

    /**
     * @test
     */
    public function getCookie_from_super_globals_should_return_same_value()
    {
        $_COOKIE['somecookie'] = 'value';
        $this->assertEquals('value', $this->request->getCookie('somecookie'));
    }

    /**
     * @test
     */
    public function getUserAgent_in_cli_should_return_empty_string()
    {
        $this->assertEquals('', $this->request->getUserAgent());
    }

    /**
     * @test
     */
    public function getUri_in_cli_should_be_empty()
    {
        $this->assertEmpty($this->request->getUri());
    }

    /**
     * @test
     */
    public function getBaseUrl_in_cli_should_return_value_defaults_to_localhost()
    {

        $this->assertEquals('http://localhost/', $this->request->getBaseUrl());
    }
}
