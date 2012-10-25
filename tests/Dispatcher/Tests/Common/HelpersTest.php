<?php
namespace Dispatcher\Tests\Common;

class HelpersTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function getattr_WithExistingAttr_ShouldReturnAttr()
    {
        $ary = array('key' => 'value');
        $this->assertEquals('value', getattr($ary['key']));
    }

    /**
     * @test
     */
    public function getattr_WithNonExistingAttr_ShouldReturnDefaultNull()
    {
        $ary = array();
        $this->assertNull(getattr($ary['woot']));
    }

    /**
     * @test
     */
    public function getattr_WithUndefinedArray_ShouldReturnUserProvidedDefault()
    {
        $this->assertEquals('n/a', getattr($ary['woot'], 'n/a'));
    }

    /**
     * @test
     */
    public function getattr_WithNonExistingObjVar_ShouldReturnUserProvidedDefault()
    {
        $obj = new \stdClass();
        $this->assertEquals('n/a', getattr($obj->somekey, 'n/a'));
    }
}
