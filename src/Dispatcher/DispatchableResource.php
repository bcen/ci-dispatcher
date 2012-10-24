<?php
namespace Dispatcher;

use Dispatcher\Http\HttpRequestInterface;
use Dispatcher\Http\HttpResponseInterface;
use Dispatcher\Http\HttpResponse;
use Dispatcher\Exception\DispatchingException;
use Dispatcher\Common\DefaultResourceOptions;
use Dispatcher\Common\ResourceOptionsInterface;

abstract class DispatchableResource implements DispatchableInterface
{
    /**
     * @var \Dispatcher\Common\DefaultResourceOptions
     */
    private $options;

    public function get(HttpRequestInterface $request, array $args = array())
    {
        $bundle = array();

        if (!empty($args) && $args[0] === 'schema') {
        } elseif (!empty($args)) {
        } else {
            $objects = $this->{'readCollection'}($request);
            $objects = is_array($objects) ? $objects : array();
            $bundle = $this->createBundle($request,
                array('objects' => $objects));
            $this->applyPaginationOn($bundle);
            // $this->applySortingOn($bundle);
        }

        // $this->applyDehydrationOn($bundle);

        return $this->createResponse($bundle);
    }

    public function doDispatch(HttpRequestInterface $request,
                               array $args = array())
    {
        $this->methodAccessCheck($request);
        $this->methodHandlerCheck($request);
        // $this->authenticationCheck($request);
        // $this->authorizationCheck($request);

        $response = $this->{strtolower($request->getMethod())}(
            $request, $args);

        if (!$response instanceof HttpResponseInterface) {
            throw new DispatchingException('Response must implement '
                . 'Dispatcher\\Http\\HttpResponseInterface',
                $this->createResponse(array('request' => $request)));
        }

        return $response;
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
                new HttpResponse(405)); // Should be a resource response
        }
    }

    protected function methodHandlerCheck(HttpRequestInterface $request)
    {
        if (!method_exists($this, strtolower($request->getMethod()))) {
            throw new DispatchingException(
                'No request method handler implemented for '
                . $request->getMethod(),
                new HttpResponse(501)); // Should be a resource response
        }
    }

    protected function authenticationCheck(HttpRequestInterface $request)
    {
    }

    protected function authorizationCheck(HttpRequestInterface $request)
    {
    }

    protected function createResponse(array &$bundle)
    {
        $this->applySerializationOn($bundle);
        $response = new HttpResponse(200, getattr($bundle['data'], ''));
        $response->setContentType('application/json');
        return $response;
    }

    protected function createBundle(HttpRequestInterface $request,
                                    array $data = array(),
                                    array $kwargs = array())
    {
        $bundle = array_merge($kwargs, array(
            'request' => $request,
            'data' => $data
        ));
        return $bundle;
    }

    protected function applyPaginationOn(array &$bundle)
    {
        $paginatorClass = $this->getOptions()->getPaginatorClass();
        $limit = $this->getOptions()->getPageLimit();
        $offset = $bundle['request']->get('offset', 0);
        $paginator = new $paginatorClass(
            getattr($bundle['data']['objects'], array()), $offset, $limit);

        $bundle['data']['objects'] = $paginator->getPage();

        $meta = array(
            'offset' => $offset,
            'limit' => $limit,
            'total' => $paginator->getCount()
        );

        $bundle['data'] = array_merge(
            array('meta' => $meta), getattr($bundle['data']));
    }

    protected function applySerializationOn(array &$bundle)
    {
        // TODO: detect accept header and serialize to that format
        $bundle['data'] = json_encode(getattr($bundle['data']));
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
