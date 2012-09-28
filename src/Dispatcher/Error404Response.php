<?php
namespace Dispatcher;

final class Error404Response extends HttpResponse
{
    public function __construct()
    {
        parent::__construct(array('status_code' => 404));
    }
}