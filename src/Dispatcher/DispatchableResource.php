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

    public function deleteObject(ResourceBundle $bundle,
                                 $id,
                                 array $uriSegments = array())
    {
        $this->notImplemented($bundle->getRequest());
    }

    public function deleteCollection(ResourceBundle $bundle)
    {
        $this->notImplemented($bundle->getRequest());
    }

    public function get(HttpRequestInterface $request,
                        array $uriSegments = array())
    {
        $bundle = $this->createBundle($request);

        if (count($uriSegments) === 1 && $uriSegments[0] === 'schema') {
            // GET /schema
            $bundle->setData($this->readSchema($bundle));
        } elseif (!empty($uriSegments)) {
            // GET /{resourceName}/{uriSegments}+
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
                $bundle->setData(array('error' => 'Not Found'))
                       ->getResponse()->setStatusCode(404);
                return $this->finalizeResponse($bundle);
            }
        } else {
            // GET /{resourceName}/
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
            $bundle->setData(array('error' => 'Method Not Allowed'))
                   ->getResponse()->setStatusCode(405);
            return $this->finalizeResponse($bundle);
        }

        $bundle->setData($this->createObject($bundle, $uriSegments));

        $id = a::ref($bundle['data']['id']);
        if ($id) {
            $bundle->getResponse()->setHeader(
                'Location', rtrim($request->getUrl(), '/') . '/' . $id
            );
        }
        $bundle->getResponse()->setStatusCode(201);
        return $this->finalizeResponse($bundle);
    }

    public function put(HttpRequestInterface $request,
                        array $uriSegments = array())
    {
        $bundle = $this->createBundle($request);

        if (count($uriSegments) >= 2
                && !$this->getOptions()->handleSubresource()) {
            $bundle->setData(array('error' => 'Method Not Allowed'))
                   ->getResponse()->setStatusCode(405);
            return $this->finalizeResponse($bundle);
        } elseif (empty($uriSegments)) {
            $bundle->setData($this->updateCollection($bundle));
        } else {
            $id = array_shift($uriSegments);
            $bundle->setData($this->updateObject($bundle, $id, $uriSegments));
        }
        $bundle->getResponse()->setStatusCode(202);
        return $this->finalizeResponse($bundle);
    }

    public function delete(HttpRequestInterface $request,
                           array $uriSegments = array())
    {
        $bundle = $this->createBundle($request);

        if (count($uriSegments) >= 2
                && !$this->getOptions()->handleSubresource()) {
            $bundle->setData(array('error' => 'Method Not Allowed'))
                   ->getResponse()->setStatusCode(405);
            return $this->finalizeResponse($bundle);
        } elseif (empty($uriSegments)) {
            $bundle->setData($this->deleteCollection($bundle));
        } else {
            $id = array_shift($uriSegments);
            $bundle->setData($this->deleteObject($bundle, $id));
        }

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
            $bundle->setData(array('error' => 'Server Side Error'))
                   ->getResponse()->setStatusCode(500);
            throw new DispatchingException(
                "$method must return HttpResponseInterface",
                $this->finalizeResponse($bundle)
            );
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
            $bundle->setData(array('error' => 'Method Not Allowed'))
                   ->getResponse()->setStatusCode(405);
            throw new HttpErrorException('Method Not Allowed',
                $this->finalizeResponse($bundle));
        }
    }

    protected function authenticationCheck(HttpRequestInterface $request)
    {
    }

    protected function authorizationCheck(HttpRequestInterface $request)
    {
    }

    /**
     * Dehydrates and serializes the data, then finalizes the resposne from
     * bundle.
     * @param \Dispatcher\Common\ResourceBundle $bundle
     * @return \Dispatcher\Http\HttpResponseInterface
     */
    protected function finalizeResponse(ResourceBundle $bundle)
    {
        $this->applyDehydrationOn($bundle);

        // It is safe to not checking for null,
        // since we passed the contentNegotiationCheck already.
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

        // bind all extra stuff too
        // NOTE: Can override data
        foreach ($kwargs as $k => $v) {
            $bundle[$k] = $v;
        }
        $this->applyDeserializationOn($bundle);
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

        $paginator->setQueryset($bundle->getData('objects', array()))
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

        if (strtolower($contentType) === 'application/xml') {
            // stolen from codeigniter-restserver
            // https://github.com/philsturgeon/codeigniter-restserver/blob/master/application/libraries/Format.php#L87
            $toXml = function($data = null, $structure = null, $basenode = 'response') use(&$toXml) {
                if (ini_get('zend.ze1_compatibility_mode') == 1) {
                    ini_set('zend.ze1_compatibility_mode', 0);
                }
                if ($structure === null) {
                    $structure = simplexml_load_string(
                        "<?xml version='1.0' encoding='utf-8'?><$basenode />");
                }

                if (!is_array($data) && !is_object($data)) {
                    $data = (array)$data;
                }

                foreach ($data as $k => $v) {
                    if (is_bool($v)) {
                        $v = $v ? 'true' : 'false';
                    }

                    if (is_numeric($k)) {
                        $k = 'item';
                    }

                    $k = preg_replace('/[^a-z_\-0-9]/i', '', $k);

                    if (is_array($v) || is_object($v)) {
                        $node = $structure->addChild($k);
                        $toXml($v, $node, $k);
                    } else {
                        $v = htmlspecialchars(
                            html_entity_decode($v, ENT_QUOTES, 'UTF-8'),
                            ENT_QUOTES,
                            "UTF-8"
                        );

                        $structure->addChild($k, $v);
                    }
                }

                return $structure->asXML();
            };

            $data = $toXml($data);
        } else {
            $data = (is_array($data) || is_object($data))
                ? json_encode($data) : '';
        }

        $bundle->setData($data);
    }

    protected function applyDeserializationOn(ResourceBundle $bundle)
    {
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

    protected function notImplemented(HttpRequestInterface $request)
    {
        $bundle = $this->createBundle($request);
        $bundle->setData(array('error' => 'Method Not Implemented'))
               ->getResponse()->setStatusCode(501);
        throw new DispatchingException('Method not implemented',
            $this->finalizeResponse($bundle));
    }

    protected function methodNotAllowed(HttpRequestInterface $request)
    {
        $bundle = $this->createBundle($request);
        $bundle->setData(array('error' => 'Method Not Allowed'))
               ->getResponse()->setStatusCode(405);
        throw new HttpErrorException('Method Not Allowed',
            $this->finalizeResponse($bundle));
    }
}
