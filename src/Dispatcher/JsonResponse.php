<?php
namespace Dispatcher;

class JsonResponse extends HttpResponse
{
    public function __construct($data,
                                $statusCode = 200,
                                array $extra = array())
    {
        $config = array_merge(
            array('content'     => $data,
                  'contentType' => 'application/json',
                  'statusCode'  => $statusCode),
            $extra
        );
        parent::__construct($config);
    }
}
