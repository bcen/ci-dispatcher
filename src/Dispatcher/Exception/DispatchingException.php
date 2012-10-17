<?php
namespace Dispatcher;

class DispatchingException extends \Exception
{
    private $response;

    public function __construct($message,
                                HttpResponseInterface $response,
                                $code = 0,
                                $exception = null)
    {
        parent::__construct($message, $code, $exception);
        $this->setResponse($response);
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function setResponse(HttpResponseInterface $response)
    {
        $this->response = $response;
        return $this;
    }
}
