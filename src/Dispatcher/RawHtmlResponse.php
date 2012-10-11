<?php
namespace Dispatcher;

/**
 * This calss indicates we are rendering the content through output->set_output
 */
class RawHtmlResponse extends HttpResponse
{
    public function sendBody(HttpRequestInterface $request)
    {
        $this->getCI()->output->set_output($this->getContent());
        exit();
    }
}
