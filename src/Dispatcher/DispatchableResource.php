<?php
namespace Dispatcher;

use Dispatcher\Http\HttpRequestInterface;
use Dispatcher\Http\HttpResponseInterface;
use Dispatcher\Http\HttpResponse;
use Dispatcher\Http\Exception\HttpErrorException;
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
        $bundle = $this->createBundle($request);

        if (count($args) === 1 && $args[0] === 'schema') {
        } elseif (!empty($args)) {
            $method = 'readObject';
            $this->methodCheck($method, $bundle);

            $object = $this->$method($request, $args);
            $bundle['data'] = $object;
        } else {
            $method = 'readCollection';
            $this->methodCheck($method, $bundle);

            $objects = $this->$method($request);
            $objects = is_array($objects) ? $objects : array();
            $bundle['data']['objects'] = $objects;
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

        $method = strtolower($request->getMethod());
        $response = $method($request, $args);

        if (!$response instanceof HttpResponseInterface) {
            $bundle = $this->createBundle($request,
                array('error' => 'Server Side Error'));
            $response = $this->createResponse($bundle,
                array('statusCode' => 500));
            throw new DispatchingException(
                "$method must return HttpResponseInterface", $response);
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
            $bundle = $this->createBundle($request,
                array('error' => 'Method Not Allowed'));
            $response = $this->createResponse($bundle,
                array('statusCode' => 405));

            throw new HttpErrorException('Method Not Allowed', $response);
        }
    }

    protected function methodHandlerCheck(HttpRequestInterface $request)
    {
        if (!method_exists($this, strtolower($request->getMethod()))) {
            $bundle = $this->createBundle($request,
                array('error' => 'Not Implemented'));
            $response = $this->createResponse($bundle,
                array('statusCode' => 501));

            throw new HttpErrorException(
                'No request method handler implemented for '
                . $request->getMethod(), $response);
        }
    }

    protected function authenticationCheck(HttpRequestInterface $request)
    {
    }

    protected function authorizationCheck(HttpRequestInterface $request)
    {
    }

    protected function createResponse(array $bundle,
                                      array $kwargs = array())
    {
        $this->applySerializationOn($bundle);

        $statusCode = getattr($kwargs['statusCode'], 200);
        $headers = getattr($kwargs['headers'], array());

        $response = new HttpResponse(
            $statusCode, getattr($bundle['data'], ''), $headers);
        $response->setContentType('application/json');

        return $response;
    }

    protected function createBundle(HttpRequestInterface $request,
                                    $data = null,
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
        $limit = (int)$bundle['request']->get('limit',
            $this->getOptions()->getPageLimit());
        $offset = (int)$bundle['request']->get('offset', 0);
        $paginator = new $paginatorClass(
            getattr($bundle['data']['objects'], array()), $offset, $limit);

        $bundle['data']['objects'] = $paginator->getPage();

        $meta = array(
            'offset' => $offset,
            'limit'  => $limit,
            'total'  => $paginator->getCount()
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

    private function methodCheck($method, $bundle)
    {
        if (!method_exists($this, $method)) {
            $bundle['data'] = array('error' => 'Server Side Error');
            $response = $this->createResponse(
                $bundle, array('statusCode' => 500));
            throw new DispatchingException(
                "Please implement $method for your resource",
                $response);
        }
    }
}
