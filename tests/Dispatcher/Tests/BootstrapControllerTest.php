<?php
namespace Dispatcher\Tests;

class BootstrapControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Dispatcher\BootstrapController
     */
    private $controllerMock;

    public function setUp()
    {
        $this->controllerMock = $this->getMock(
            'Dispatcher\\BootstrapController',
            array('loadMiddlewares', 'renderResponse', 'dispatch'),
            array(),
            '',
            false);

        $this->controllerMock
            ->expects($this->once())
            ->method('loadMiddlewares')
            ->will($this->returnValue(array()));

        $this->controllerMock
            ->expects($this->once())
            ->method('dispatch')
            ->will($this->returnValue(\Dispatcher\JsonResponse::create()));
    }

    public function test__remap_OnBlogIndexController_ShouldPass()
    {
        $this->controllerMock->_remap('blog', array());
        $this->assertTrue(1 == 1);
    }
}
