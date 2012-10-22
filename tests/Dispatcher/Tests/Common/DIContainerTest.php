<?php
namespace Dispatcher\Tests\Common;

class DIContainerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Dispatcher\Common\DIContainer
     */
    private $container;

    /**
     * @var string
     */
    public $sharedObjHash;

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

        $this->container = new \Dispatcher\Common\DIContainer();

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

    /**
     * @test
     */
    public function containerOffsetGet_MultipleCallToSharedObjectWithSameKey_ShouldReturnSameObjectHash()
    {
        $sharedObj = $this->container['sharedObj'];
        $this->assertEquals($this->sharedObjHash, spl_object_hash($sharedObj));

        $another = $this->container['sharedObj'];
        $this->assertEquals($this->sharedObjHash, spl_object_hash($another));
    }
}
