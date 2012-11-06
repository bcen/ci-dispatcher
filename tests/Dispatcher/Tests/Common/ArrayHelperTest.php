<?php
namespace Dispatcher\Tests\Common;

use Dispatcher\Common\ArrayHelper as a;

class ArrayHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function ref_should_return_same_value()
    {
        $ary = array('key' => 'value');
        $this->assertEquals('value', a::ref($ary['key']));
    }

    /**
     * @test
     */
    public function ref_should_return_default_for_nonexistent_key()
    {
        $ary = array('key' => 'value');
        $this->assertEquals('default_value',
            a::ref($ary['somekey'], 'default_value'));

        $this->assertNull(a::ref($ary['somekey']));
    }
}
