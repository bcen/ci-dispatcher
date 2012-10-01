<?php
namespace Dispatcher\Tests;

class DIContainerTest extends \PHPUnit_Framework_Testcase
{
    /**
     * @var \Dispatcher\DIContainer
     */
    private $container;

    /**
     * @var string
     */
    private $sharedObjHash;

    public function setUp()
    {
        $container['encryptionKey'] = '123456';
        $container['stdObj'] = function($c) {
            $obj = new \stdClass();
            $obj->encryptionKey = $c['encryptionKey'];
            return $obj;
        };

        $self = $this;
        $sharedContainer['sharedObj'] = function($c) use($self) {
            $obj = new \stdClass();
            $obj->encryptionKey = $c['encryptionKey'];
            $self->sharedObjHash = spl_object_hash($obj);
            return $obj;
        };

        $this->container = new \Dispatcher\DIContainer();

        foreach ($container as $k => $v) {
            $this->container[$k] = $v;
        }

        foreach ($sharedContainer as $k => $v) {
            $this->container->share($k, $v);
        }

        if ($this->container === NULL) {
            $this->fail('Unable to create DI Container');
        }
    }

    public function testGetContainerDependency()
    {
        $stdObj = $this->container['stdObj'];
        $hash = spl_object_hash($stdObj);

        $this->assertNotNull($stdObj);
        $this->assertEquals('123456', $stdObj->encryptionKey);

        $another = $this->container['stdObj'];
        $this->assertNotEquals($hash, spl_object_hash($another));
    }

    public function testGetSharedContainerDependency()
    {
        $sharedObj = $this->container['sharedObj'];
        $this->assertEquals($this->sharedObjHash, spl_object_hash($sharedObj));

        $another = $this->container['sharedObj'];
        $this->assertEquals($this->sharedObjHash, spl_object_hash($another));
    }
}