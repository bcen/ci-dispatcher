<?php
namespace Dispatcher\Tests\Stub;

class SimpleBootstrapControllerStub extends \Dispatcher\BootstrapController
{
    public function getDispatcherConfig()
    {
        return array(
            'middlewares' => array(),
            'debug' => FALSE
        );
    }

    public function getDependenciesConfig()
    {
        return array();
    }
}
