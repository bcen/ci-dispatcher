<?php
namespace Dispatcher\Tests\Common;

use Dispatcher\Common\ResourceBundle;

class ResourceBundleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function bundle_should_be_array_accessable_on_data()
    {
        $bundle = new ResourceBundle();
        $bundle['other_stuff'] = 'wow';
        $this->assertEquals('wow', $bundle['other_stuff']);
    }

    /**
     * @test
     */
    public function setData_should_able_to_get_from_array_access()
    {
        $bundle = new ResourceBundle();
        $bundle->setData(array(1, 2));
        $this->assertEquals(array(1, 2), $bundle['data']);
    }

    /**
     * @test
     */
    public function addData_should_able_to_get_from_array_access()
    {
        $bundle = new ResourceBundle();
        $bundle->addData('objects', array(1, 2));
        $this->assertEquals(array(1, 2), $bundle['data']['objects']);
    }
}
