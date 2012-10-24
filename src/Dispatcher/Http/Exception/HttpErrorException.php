<?php
namespace Dispatcher\Http\Exception;

use Exception;
use Dispatcher\Http\HttpResponseInterface;

class HttpErrorException extends Exception
{
    private $response;

    public function __construct($message,
                                HttpResponseInterface $response,
                                $code = 0,
                                Exception $exception = null)
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
