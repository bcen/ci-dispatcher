<?php
namespace Dispatcher;

abstract class DispatchableResource implements DispatchableInterface
{
    /**
     * @var \Dispatcher\DefaultResourceOptions
     */
    private $options;

    public function get($bundle)
    {
    }

    public function doDispatch(HttpRequestInterface $request,
                               array $args = array())
    {
        $this->methodAccessCheck($request);
        $this->methodHandlerCheck($request);
        // $this->authenticationCheck($request);
        // $this->authorizationCheck($request);
    }

    protected function methodAccessCheck(HttpRequestInterface $request)
    {
        $reqMethod = $request->getMethod();
        $allowed = array_filter($this->getOptions()->getAllowedMethods(),
                                function($ele) use($reqMethod) {
            return strtolower($ele) === strtolower($reqMethod);
        });

        if (empty($allowed)) {
            throw new DispatchingException('Method Not Allowed',
                new RawHtmlResponse(405)); // Should be a resource response
        }
    }

    protected function methodHandlerCheck(HttpRequestInterface $request)
    {
        if (!method_exists($this, $request->getMethod())) {
            throw new DispatchingException(
                'No request method handler implemented for '
                . $request->getMethod(),
                new RawHtmlResponse(501)); // Should be a resource response
        }
    }

    protected function mapMethodToAction(HttpRequestInterface $request,
                                         array $args = array())
    {
        $actionMaps = $this->getOptions()->getActionMaps();
        $action = $actionMaps[strtoupper($request->getMethod())];
        $type = count($args) >= 1 ? 'Object' : 'Collection';
        return $action . $type;
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
