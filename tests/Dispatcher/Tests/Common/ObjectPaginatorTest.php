<?php
namespace Dispatcher\Tests\Common;

use Dispatcher\Common\ObjectPaginator;

class ObjectPaginatorTest extends \PHPUnit_Framework_TestCase
{
    private $objects;

    public function setUp()
    {
        $this->objects = range(1, 100);
    }

    /**
     * @test
     */
    public function getCount_on_object_in_range_1_to_100_should_return_100()
    {
        $paginator = new ObjectPaginator();
        $paginator->setQueryset($this->objects);
        $this->assertEquals(100, $paginator->getCount());
    }

    /**
     * @test
     */
    public function getPage_with_100_objects_should_return_first_20_objects()
    {
        $paginator = new ObjectPaginator();
        $paginator->setQueryset($this->objects);
        $page = $paginator->getPage();

        $this->assertEquals(20, count($page['objects']));
        $first = array_shift($page['objects']);
        $last = array_pop($page['objects']);
        $this->assertEquals(1, $first);
        $this->assertEquals(20, $last);
    }

    /**
     * @test
     */
    public function getPage_at_specific_offset_should_return_correct_results()
    {
        $paginator = new ObjectPaginator(5, 5);
        $paginator->setQueryset($this->objects);
        $page = $paginator->getPage();
        $this->assertEquals(array(6, 7, 8, 9, 10), $page['objects']);
    }

    /**
     * @test
     */
    public function getPage_should_return_correct_meta()
    {
        $paginator = new ObjectPaginator(5, 5);
        $paginator->setQueryset($this->objects);
        $page = $paginator->getPage();
        $this->assertEquals(5, $page['meta']['offset']);
        $this->assertEquals(5, $page['meta']['limit']);
        $this->assertEquals(count($this->objects), $page['meta']['total']);
    }
}
