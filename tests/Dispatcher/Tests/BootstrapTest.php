<?php
namespace Dispatcher\Tests;

class BootstrapTest extends \PHPUnit_Framework_Testcase
{
    public function testBootstrapVars()
    {
        $this->assertNotEmpty(FCPATH);
        $this->assertNotEmpty(APPPATH);
        $this->assertNotEmpty(BASEPATH);
        $this->assertTrue(function_exists('get_instance'));
        $CI = get_instance();
        $this->assertNotNull($CI);
        $this->assertEquals(CI_HASH, spl_object_hash($CI));
    }
}
