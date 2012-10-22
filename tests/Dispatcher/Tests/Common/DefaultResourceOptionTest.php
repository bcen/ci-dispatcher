<?php
namespace Dispatcher\Tests\Common;

use Dispatcher\Common\DefaultResourceOptions;

class DefaultResourceOptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function setSupportedFormats_WithEmptyArray_ShouldNotAffectDefaultFormat()
    {
        $options = DefaultResourceOptions::create();
        $expected = $options->getDefaultFormat();
        $options->setSupportedFormats(array());

        $this->assertEquals($expected, $options->getDefaultFormat());
    }

    /**
     * @test
     */
    public function setSupportedFormats_WithAtLeastOneFormat_ShouldAlsoSetAsDefaultFormat()
    {
        $expected = 'application/yaml';
        $options = DefaultResourceOptions::create()
            ->setSupportedFormats(array('application/yaml'));

        $this->assertEquals($expected, $options->getDefaultFormat());
    }

    /**
     * @test
     */
    public function setSupportedFormats_WithMultipleFormats_ShouldSetFirstFormatAsDefaultFormat()
    {
        $expected = 'application/yaml';
        $options = DefaultResourceOptions::create()->setSupportedFormats(array(
            'application/yaml', 'application/json', 'text/html'));

        $this->assertEquals($expected, $options->getDefaultFormat());
    }
}
