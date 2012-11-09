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
use Dispatcher\Common\ResourceBundle;

abstract class DispatchableResource implements DispatchableInterface
{
    /**
     * @var \Dispatcher\Common\ResourceOptionsInterface
     */
    private $options;

    public function readSchema(ResourceBundle $bundle)
    {
        return array(
            'meta' => array(
                'defaultFormat' => $this->getOptions()->getDefaultFormat(),
                'supportedFormats' => $this->getOptions()->getSupportedFormats(),
                'allowedMethods' => $this->getOptions()->getAllowedMethods()
            )
        );
    }

    public function readObject(ResourceBundle $bundle,
                               $id,
                               array $uriSegments = array())
    {
        $this->notImplemented($bundle->getRequest());
    }

    public function readCollection(ResourceBundle $bundle)
    {
        $this->notImplemented($bundle->getRequest());
    }

    public function createObject(ResourceBundle $bundle,
                                 array $uriSegments = array())
    {
        $this->notImplemented($bundle->getRequest());
    }

    public function updateObject(ResourceBundle $bundle,
                                 $id,
                                 array $uriSegments = array())
    {
        $this->notImplemented($bundle->getRequest());
    }

    public function updateCollection(ResourceBundle $bundle) {
        $this->notImplemented($bundle->getRequest());
    }

    public function get(HttpRequestInterface $request,
                        array $uriSegments = array())
    {
        $bundle = $this->createBundle($request);

        if (count($uriSegments) === 1 && $uriSegments[0] === 'schema') {
            // get.schema
            $bundle->setData($this->readSchema($bundle));
        } elseif (!empty($uriSegments)) {

            // get.detail
            try {
                // Do we handle subresource?
                if (count($uriSegments) > 1
                        && !$this->getOptions()->handleSubresource()) {
                    throw new ResourceNotFoundException(
                        'Does not support subresource');
                }

                $id = array_shift($uriSegments);
                $object = $this->readObject($bundle, $id, $uriSegments);
                $bundle->setData($object);
            } catch (ResourceNotFoundException $ex) {
                $bundle->setData(array('error' => 'Not Found'));
                return $this->finalizeResponse($bundle)->setStatusCode(404);
            }
        } else {

            // get.list
            $objects = $this->readCollection($bundle);
            $objects = is_array($objects) ? $objects : array();
            $bundle->setData(array('objects' => $objects));

            $this->applySortingOn($bundle);
            $this->applyPaginationOn($bundle);
        }

        return $this->finalizeResponse($bundle);
    }

    public function post(HttpRequestInterface $request,
                         array $uriSegments = array())
    {
        $bundle = $this->createBundle($request);

        if (!empty($uriSegments) && !$this->getOptions()->handleSubresource()) {
            $bundle->setData(array('error' => 'Method Not Allowed'));
            return $this->finalizeResponse($bundle)->setStatusCode(405);
        }

        $bundle->setData($this->createObject($bundle, $uriSegments));

        $id = a::ref($bundle['data']['id']);
        if ($id) {
            $bundle->getResponse()->setHeader(
                'Location', rtrim($request->getUrl(), '/') . '/' . $id);
        }

        return $this->finalizeResponse($bundle)->setStatusCode(201);
    }

    public function put(HttpRequestInterface $request,
                        array $uriSegments = array())
    {
        $bundle = $this->createBundle($request);

        if (count($uriSegments) >= 2
                && !$this->getOptions()->handleSubresource()) {
            $bundle->setData(array('error' => 'Method Not Allowed'));
            return $this->finalizeResponse($bundle)->setStatusCode(405);
        } elseif (empty($uriSegments)) {
            $bundle->setData($this->updateCollection($bundle));
        } else {
            $id = array_shift($uriSegments);
            $bundle->setData($this->updateObject($bundle, $id, $uriSegments));
        }

        return $this->finalizeResponse($bundle)->setStatusCode(202);
    }

    public function delete(HttpRequestInterface $request,
                           array $uriSegments = array())
    {
        $bundle = $this->createBundle($request);

        if (empty($uriSegments) || count($uriSegments) >= 2) {
            $bundle->setData(array('error' => 'Method Not Allowed'));
            return $this->finalizeResponse($bundle)->setStatusCode(405);
        }

        $method = 'deleteObject';
        $this->methodCheck($method, $bundle);

        $this->applyHydrationOn($bundle);
        $bundle->setData($this->$method($bundle));

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

    /**
     * @param \Dispatcher\Common\ResourceBundle $bundle
     * @return \Dispatcher\Http\HttpResponseInterface
     */
    protected function finalizeResponse(ResourceBundle $bundle)
    {
        $this->applyDehydrationOn($bundle);

        $contentType = $this->detectSupportedContentType($bundle->getRequest());
        $this->applySerializationOn($bundle, $contentType);

        $response = $bundle->getResponse();
        $response->setContent($bundle->getData())
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
        $bundle = new ResourceBundle();
        $bundle->setRequest($request)
            ->setResponse($this->createRawResponse())
            ->setData($data);
        foreach ($kwargs as $k => $v) {
            $bundle[$k] = $v;
        }
        $this->applyHydrationOn($bundle);
        return $bundle;
    }

    protected function applySortingOn(ResourceBundle $bundle)
    {
    }

    protected function applyPaginationOn(ResourceBundle $bundle)
    {
        $req = $bundle->getRequest();
        $paginator = $this->getOptions()->getPaginator();

        $limit = (int)$req->get('limit', $this->getOptions()->getPageLimit());
        $offset = (int)$req->get('offset', 0);

        $data = $bundle->getData();
        $queryset = a::ref($data['objects'], array());
        $paginator->setQueryset($queryset)
            ->setOffset($offset)
            ->setLimit($limit);

        $bundle->setData($paginator->getPage());

        $response = $bundle->getResponse();
        $response->setHeader('X-Page-Limit', $limit);
        $response->setHeader('X-Page-Offset', $offset);
        $response->setHeader('X-Page-Total', $paginator->getCount());
    }

    protected function applyHydrationOn(ResourceBundle $bundle)
    {
        // Prepares data from unserialized data back to PHP
    }

    protected function applyDehydrationOn(ResourceBundle $bundle)
    {
        // Prepares data from PHP to be serialized
    }

    protected function applySerializationOn(ResourceBundle $bundle, $contentType)
    {
        $data = $bundle->getData();
        $data = (is_array($data) || is_object($data))
            ? json_encode($data) : '';
        $bundle->setData($data);
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

    private function methodCheck($method, ResourceBundle $bundle)
    {
        if (!method_exists($this, $method)) {
            $bundle->setData(array('error' => 'Server Side Error'));
            $response = $this->finalizeResponse($bundle)->setStatusCode(500);
            throw new DispatchingException(
                "Please implement $method for your resource", $response);
        }
    }

    protected function notImplemented(HttpRequestInterface $request)
    {
        $bundle = $this->createBundle($request);
        $bundle['data']['error'] = 'Method Not Implemented';
        $bundle->getResponse()->setStatusCode(501);
        throw new DispatchingException('Method not implemented',
            $this->finalizeResponse($bundle));
    }
}
