<?php
namespace Dispatcher;

abstract class DispatchableResource extends DispatchableController
{
    /**
     * @var \Dispatcher\DefaultResourceOptions
     */
    private $options;

    private $actionMethods = array(
        'GET'    => 'read',
        'POST'   => 'create',
        'PUT'    => 'update',
        'DELETE' => 'delete'
    );

    public function doDispatch(HttpRequestInterface $request,
                               array $args = array())
    {
        $this->requestHandlerCheck($request);
        $this->methodCheck($request);
    }

    protected function requestHandlerCheck(HttpRequestInterface $request)
    {
        // checks whether GET, POST, PUT, DELETE is handling correctly
        throw new \LogicException('Failed');
    }

    protected function methodCheck(HttpRequestInterface $request)
    {
        // checks whether request method is allowed
        throw new \LogicException('Failed');
    }

    protected function getOptions()
    {
        if (!$this->options) {
            // creates default options if nothing found
            $this->options = new DefaultResourceOptions();
        }
        return $this->options;
    }

    protected function setOptions(ResourceOptionsInterface $options)
    {
        $this->options = $options;
        return $this;
    }
}
