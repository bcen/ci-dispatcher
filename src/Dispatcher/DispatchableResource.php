<?php
namespace Dispatcher;

use Dispatcher\Http\HttpRequestInterface;
use Dispatcher\Http\HttpResponseInterface;
use Dispatcher\Http\HttpResponse;
use Dispatcher\Http\Exception\HttpErrorException;
use Dispatcher\Exception\DispatchingException;
use Dispatcher\Exception\ResourceNotFoundException;
use Dispatcher\Common\DefaultResourceOptions;
use Dispatcher\Common\ResourceOptionsInterface;

abstract class DispatchableResource implements DispatchableInterface
{
    /**
     * @var \Dispatcher\Common\DefaultResourceOptions
     */
    private $options;

    public function get(HttpRequestInterface $request,
                        array $uriSegments = array())
    {
        $bundle = $this->createBundle($request);

        if (count($uriSegments) === 1 && $uriSegments[0] === 'schema') {
            $method = 'readSchema';
            $this->methodCheck($method, $bundle);

            $bundle['data'] = $this->$method($request);
        } elseif (!empty($uriSegments)) {
            $method = 'readObject';
            $this->methodCheck($method, $bundle);

            $id = array_shift($uriSegments);

            try {
                $object = $this->$method($request, $id, $uriSegments);
                $bundle['data'] = $object;
            } catch (ResourceNotFoundException $ex) {
                $bundle['data']['error'] = 'Not Found';

                return $this->createResponse(
                    $bundle, array('statusCode' => 404));
            }
        } else {
            $method = 'readCollection';
            $this->methodCheck($method, $bundle);

            $objects = $this->$method($request);
            $objects = is_array($objects) ? $objects : array();
            $bundle['data']['objects'] = $objects;
            // $this->applySortingOn($bundle);
            $this->applyPaginationOn($bundle);
        }

        $this->applyDehydrationOn($bundle);

        return $this->createResponse($bundle);
    }

    public function post(HttpRequestInterface $request,
                         array $uriSegments = array())
    {
        $bundle = $this->createBundle($request);

        if (!empty($uriSegments)) {
            $bundle['data']['error'] = 'Method Not Allowed';
            return $this->createResponse($bundle, array('statusCode' => 405));
        }

        $method = 'createObject';
        $this->methodCheck($method, $bundle);

        $this->applyHydrationOn($bundle);
        $bundle['data'] = $this->$method($request, $bundle);

        return $this->createResponse($bundle, array('statusCode' => 201));
    }

    public function put(HttpRequestInterface $request,
                        array $uriSegments = array())
    {
        $bundle = $this->createBundle($request);

        if (empty($uriSegments) || count($uriSegments) >= 2) {
            $bundle['data']['error'] = 'Method Not Allowed';
            return $this->createResponse($bundle, array('statusCode' => 405));
        }

        $method = 'updateObject';
        $this->methodCheck($method, $bundle);

        $this->applyHydrationOn($bundle);
        $bundle['data'] = $this->$method($request, $bundle);

        return $this->createResponse($bundle);
    }

    public function doDispatch(HttpRequestInterface $request,
                               array $uriSegments = array())
    {
        $this->methodAccessCheck($request);
        // $this->authenticationCheck($request);
        // $this->authorizationCheck($request);

        $method = strtolower($request->getMethod());
        $response = $this->$method($request, $uriSegments);

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

    protected function authenticationCheck(HttpRequestInterface $request)
    {
    }

    protected function authorizationCheck(HttpRequestInterface $request)
    {
    }

    protected function readSchema(HttpRequestInterface $request)
    {
        return array('message' => 'readSchema');
    }

    protected function createResponse(array $bundle,
                                      array $kwargs = array())
    {
        $contentType = 'application/json';
        $this->applySerializationOn($bundle, $contentType);

        $statusCode = getattr($kwargs['statusCode'], 200);
        $headers = getattr($kwargs['headers'], array());
        $content = getattr($bundle['data'], '');

        $response = new HttpResponse($statusCode, $content, $headers);
        $response->setContentType($contentType);
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
        $req = $bundle['request'];

        $limit = (int)$req->get('limit', $this->getOptions()->getPageLimit());
        $offset = (int)$req->get('offset', 0);

        $paginator = $this->getOptions()->getPaginator();
        $paginator->setObjects(getattr($bundle['data']['objects'], array()));
        $paginator->setLimit($limit);
        $paginator->setOffset($offset);
        $bundle['data']['objects'] = $paginator->getPage();


        $meta = array(
            'offset' => $offset,
            'limit'  => $limit,
            'total'  => $paginator->getCount()
        );

        $bundle['data'] = array_merge(
            array('meta' => $meta), getattr($bundle['data']));
    }

    protected function applyHydrationOn(array &$bundle)
    {
        // Prepares data from unserialized data back to PHP
    }

    protected function applyDehydrationOn(array &$bundle)
    {
        // Prepares data from PHP to be serialized
    }

    protected function applySerializationOn(array &$bundle, $contentType)
    {
        // TODO: detect accept header and serialize to that format
        $data = getattr($bundle['data'], '');
        $bundle['data'] = (is_array($data) || is_object($data))
            ? json_encode($data) : '';
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

    private function methodCheck($method, array $bundle)
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
