<?php
namespace Dispatcher;

abstract class DispatchableResource extends DispatchableController
{
    /**
     * @var \Dispatcher\DefaultResourceOptions
     */
    private $options;

    private $methodMaps = array(
        'GET'    => 'read',
        'POST'   => 'create',
        'PUT'    => 'update',
        'DELETE' => 'delete'
    );

    public function __construct()
    {
        $this->setOptions(new DefaultResourceOptions());
    }

    public function doDispatch(HttpRequestInterface $request,
                               array $args = array())
    {
        $method = $this->mapToMethod($request, $args);
        if (!$method) {
            return $this->renderJson(
                array('userMessage' => 'Method Not Found'), 404);
        }

        $bundle = $this->createBundle($request);

        array_unshift($args, $bundle);
        call_user_func_array(array($this, $method), $args);

        return $this->createResponse($bundle);
    }

    protected function mapToMethod(HttpRequestInterface $request,
                                   array $args = array())
    {
        $allowed = $this->getOptions()->getAllowedMethods();
        if (in_array($request->getMethod(), $allowed)) {
            $method = $this->methodMaps[$request->getMethod()];
            $method .= !empty($args) ? 'Detail' : 'List';
            return $method;
        }
        return null;
    }

    protected function getOptions()
    {
        return $this->options;
    }

    protected function setOptions(ResourceOptionsInterface $options)
    {
        $this->options = $options;
        return $this;
    }

    protected function createBundle($request)
    {
        return array('request' => $request);
    }

    protected function createResponse($bundle)
    {
        $statusCode = isset($bundle['statusCode'])
            ? $bundle['statusCode'] : 200;
        return null;
    }
}
