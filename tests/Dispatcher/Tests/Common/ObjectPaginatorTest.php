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
    public function getTotalCount_OnObjectInRange1To100_ShouldReturn100()
    {
        $paginator = new ObjectPaginator($this->objects);
        $this->assertEquals(100, $paginator->getCount());
    }

    /**
     * @test
     */
    public function getPage_With100ObjectAndDefaultParams_ShouldReturnTheFirst20Objects()
    {
        $paginator = new ObjectPaginator($this->objects);
        $objects = $paginator->getPage();

        $this->assertEquals(20, count($objects));
        $first = array_shift($objects);
        $last = array_pop($objects);
        $this->assertEquals(1, $first);
        $this->assertEquals(20, $last);
    }

    /**
     * @test
     */
    public function getPage_AtSpecificOffset_ShouldReturnCorrectResults()
    {
        $paginator = new ObjectPaginator($this->objects, 5, 5);
        $objects = $paginator->getPage();
        $this->assertEquals(array(6, 7, 8, 9, 10), $objects);
    }

    /**
     * @test
     */
    public function getPage_WithLargerCountOffset_ShouldReturnEmptyArray()
    {
        $paginator = new ObjectPaginator($this->objects, 5000);
        $objects = $paginator->getPage();
        $this->assertEmpty($objects);

        $paginator->setOffset(2000);
        $this->assertEmpty($paginator->getPage());
    }
}
