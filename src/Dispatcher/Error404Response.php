<?php
namespace Dispatcher;

final class Error404Response extends HttpResponse
{
    public static function create($statusCode = 404,
                                  $content = '',
                                  $headers = array())
    {
        return new static($statusCode, $content, $headers);
    }

    public function __construct($statusCode = 404,
                                $content = '',
                                $headers = array())
    {
        parent::__construct($statusCode, $content, $headers);
    }
}
