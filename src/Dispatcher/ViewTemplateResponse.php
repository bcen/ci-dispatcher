<?php
namespace Dispatcher;

class ViewTemplateResponse extends HttpResponse
{
    private $views = array();

    public function getViews()
    {
        return $this->views;
    }

    public function setViews(array $views)
    {
        $this->views = $views;
        return $this;
    }

    protected function sendBody(HttpRequestInterface $request)
    {
        $data = $this->getContent();
        $data = is_array($data) ? $data : array();
        foreach ($this->getViews() as $v) {
            $this->getCI()->load->view($v, $data);
        }
    }
}
