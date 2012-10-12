<?php
namespace Dispatcher\Tests;

class DefaultResourceOptionTest extends \PHPUnit_Framework_Testcase
{
    /**
     * @var \Dispatcher\ResourceOptionsInterface
     */
    private $option;

    public function setUp()
    {
        $this->option = \Dispatcher\DefaultResourceOptions::create();
    }

    public function test_getAllowedMethods_ShouldReturnGetPostPutDelete()
    {
        $this->assertEquals(array('GET', 'POST', 'PUT', 'DELETE'),
            $this->option->getAllowedMethods());
    }

    public function test_setAllowedMethods_ShouldPassWithoutError()
    {
        $option = new \Dispatcher\DefaultResourceOptions();
        try {
            $option->setAllowedFields(array('GET', 'POST'));
        } catch (\Exception $ex) {
            $this->fail();
        }
    }

    public function test_getDefaultFormat_ShouldReturnJson()
    {
        $this->assertEquals('json', $this->option->getDefaultFormat());
    }

    public function test_setDefaultFormat_ShouldPassWithoutError()
    {
        try {
            $option = \Dispatcher\DefaultResourceOptions::create()
                ->setDefaultFormat('xml');
        } catch (\Exception $ex) {
            $this->fail();
        }
    }

    public function test_getSupportedFormat_ShouldReturnJson()
    {
        $this->assertEquals(array('json'),
            $this->option->getSupportedFormats());
    }

    public function test_setSupportedFormat_ShouldPassWithoutError()
    {
        try {
            $option = \Dispatcher\DefaultResourceOptions::create()
                ->setSupportedFormats(array('xml', 'yaml'));

            $this->assertEquals(array('xml', 'yaml'),
                $option->getSupportedFormats());
            $this->assertEquals('xml', $option->getDefaultFormat());

            $option->setSupportedFormats(array());
            $this->assertEquals(array('xml', 'yaml'),
                $option->getSupportedFormats());

            $option->setSupportedFormats(array('csv'));
            $this->assertEquals(array('csv'),
                $option->getSupportedFormats());
            $this->assertEquals('csv', $option->getDefaultFormat());
        } catch (\Exception $ex) {
            $this->fail();
        }
    }

    public function test_getAllowedFields_ShouldReturnEmpty()
    {
        $this->assertEmpty($this->option->getAllowedFields());
    }

    public function test_setAllowedFields_ShouldPassWithoutError()
    {
        try {
            $option = \Dispatcher\DefaultResourceOptions::create()
                ->setAllowedFields(array('firstName', 'lastName'));
        } catch (\Exception $ex) {
            $this->fail();
        }
    }
}
