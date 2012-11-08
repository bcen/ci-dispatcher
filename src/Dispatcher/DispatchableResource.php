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
use Dispatcher\Common\ArrayHelper as a;

abstract class DispatchableResource implements DispatchableInterface
{
    /**
     * @var \Dispatcher\Common\ResourceOptionsInterface
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
                return $this->finalizeResponse($bundle)->setStatusCode(404);
            }
        } else {
            $method = 'readCollection';
            $this->methodCheck($method, $bundle);

            $objects = $this->$method($request);
            $objects = is_array($objects) ? $objects : array();
            $bundle['data']['objects'] = $objects;

            $this->applySortingOn($bundle);
            $this->applyPaginationOn($bundle);
        }

        $this->applyDehydrationOn($bundle);

        return $this->finalizeResponse($bundle);
    }

    public function post(HttpRequestInterface $request,
                         array $uriSegments = array())
    {
        $bundle = $this->createBundle($request);

        if (!empty($uriSegments)) {
            $bundle['data']['error'] = 'Method Not Allowed';
            return $this->finalizeResponse($bundle)->setStatusCode(405);
        }

        $method = 'createObject';
        $this->methodCheck($method, $bundle);

        $this->applyHydrationOn($bundle);
        $bundle['data'] = $this->$method($request, $bundle);

        // TODO: add a real location header
        return $this->finalizeResponse($bundle)
            ->setStatusCode(201)
            ->setHeader('Location', 'http://www.google.com/');
    }

    public function put(HttpRequestInterface $request,
                        array $uriSegments = array())
    {
        $bundle = $this->createBundle($request);

        if (empty($uriSegments) || count($uriSegments) >= 2) {
            $bundle['data']['error'] = 'Method Not Allowed';
            return $this->finalizeResponse($bundle)->setStatusCode(405);
        }

        $method = 'updateObject';
        $this->methodCheck($method, $bundle);

        $this->applyHydrationOn($bundle);
        $bundle['data'] = $this->$method($request, $bundle);

        return $this->finalizeResponse($bundle)->setStatusCode(202);
    }

    public function delete(HttpRequestInterface $request,
                           array $uriSegments = array())
    {
        $bundle = $this->createBundle($request);

        if (empty($uriSegments) || count($uriSegments) >= 2) {
            $bundle['data']['error'] = 'Method Not Allowed';
            return $this->finalizeResponse($bundle)->setStatusCode(405);
        }

        $method = 'deleteObject';
        $this->methodCheck($method, $bundle);

        $this->applyHydrationOn($bundle);
        $bundle['data'] = $this->$method($request, $bundle);

        return $this->finalizeResponse($bundle);
    }

    public function doDispatch(HttpRequestInterface $request,
                               array $uriSegments = array())
    {
        $this->contentNegotiationCheck($request);
        $this->methodAccessCheck($request);
        $this->authenticationCheck($request);
        $this->authorizationCheck($request);

        $method = strtolower($request->getMethod());
        $response = $this->$method($request, $uriSegments);

        if (!$response instanceof HttpResponseInterface) {
            $bundle = $this->createBundle($request);
            $bundle['data']['error'] = 'Server Side Error';
            $response = $this->finalizeResponse($bundle)->setStatusCode(500);
            throw new DispatchingException(
                "$method must return HttpResponseInterface", $response);
        }

        return $response;
    }

    /**
     * Checks for correct Accept header and supported formats from options.
     * @param \Dispatcher\Http\HttpRequestInterface $request
     * @throws \Dispatcher\Http\Exception\HttpErrorException If no supported format found
     */
    protected function contentNegotiationCheck(HttpRequestInterface $request)
    {
        $contentType = $this->detectSupportedContentType($request);

        if ($contentType === null) {
            throw new HttpErrorException('Format not supported',
                new HttpResponse(406));
        }
    }

    /**
     * Checks for allowed methods from options
     * @param Http\HttpRequestInterface $request
     * @throws Http\Exception\HttpErrorException If no supported request method found
     */
    protected function methodAccessCheck(HttpRequestInterface $request)
    {
        $reqMethod = $request->getMethod();
        $allowed = array_filter($this->getOptions()->getAllowedMethods(),
                                function($ele) use($reqMethod) {
            return strtolower($ele) === strtolower($reqMethod);
        });

        if (empty($allowed)) {
            $bundle = $this->createBundle($request);
            $bundle['data']['error'] = 'Method Not Allowed';
            $response = $this->finalizeResponse($bundle)->setStatusCode(405);
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

    /**
     * @param array $bundle
     * @return \Dispatcher\Http\HttpResponseInterface
     */
    protected function finalizeResponse(array $bundle)
    {
        $contentType = $this->detectSupportedContentType($bundle['request']);
        $this->applySerializationOn($bundle, $contentType);

        $response = a::ref($bundle['response'], $this->createRawResponse());
        $response->setContent(a::ref($bundle['data'], ''))
                 ->setContentType($contentType);

        return $response;
    }

    /**
     * @return \Dispatcher\Http\HttpResponseInterface;
     */
    protected function createRawResponse()
    {
        return new HttpResponse();
    }

    protected function createBundle(HttpRequestInterface $request,
                                    $data = null,
                                    array $kwargs = array())
    {
        $bundle = array_merge($kwargs, array(
            'request' => $request,
            'response' => $this->createRawResponse(),
            'data' => $data
        ));
        return $bundle;
    }

    protected function applySortingOn(array &$bundle)
    {
    }

    protected function applyPaginationOn(array &$bundle)
    {
        $req = $bundle['request'];
        $paginator = $this->getOptions()->getPaginator();

        $limit = (int)$req->get('limit', $this->getOptions()->getPageLimit());
        $offset = (int)$req->get('offset', 0);

        $paginator->setQueryset(a::ref($bundle['data']['objects'], array()))
            ->setOffset($offset)
            ->setLimit($limit);

        $bundle['data'] = $paginator->getPage();
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
        $data = a::ref($bundle['data'], '');
        $bundle['data'] = (is_array($data) || is_object($data))
            ? json_encode($data) : '';
    }

    /**
     * Detects and returns the requested content type from supported formats.
     * @param Http\HttpRequestInterface $request
     * @return string|null Supported format in string, or null if no supported format found
     */
    protected function detectSupportedContentType(HttpRequestInterface $request)
    {
        // TODO: maybe a better way to detect which format to use?
        $formats = $this->getOptions()->getSupportedFormats();
        $contentType = null;

        // Look for content type in query string
        // e.g. /?format=application/json
        if (in_array($request->get('format'), $formats)) {
            $contentType = $request->get('format');
        }

        // Suppress the query string from Accept header
        // e.g. Accept: text/html,application/json
        foreach ($request->getAcceptableContentTypes() as $format) {
            if (in_array($format, $formats)) {
                $contentType = $format;
                break;
            }
        }

        // if nothing found in query string and Accept header,
        // then use default format if */* present
        if ($contentType === null) {
            if (in_array('*/*', $request->getAcceptableContentTypes())) {
                $contentType = $this->getOptions()->getDefaultFormat();
            }
        }

        return $contentType;
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
            $bundle['data']['error'] = 'Server Side Error';
            $response = $this->finalizeResponse($bundle)->setStatusCode(500);
            throw new DispatchingException(
                "Please implement $method for your resource", $response);
        }
    }
}
