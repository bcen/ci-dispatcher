<?php
namespace Dispatcher\Http;

class ViewTemplateResponse extends HttpResponse
{
    private $views = array();

    public function __construct(array $views,
                                $statusCode = 200,
                                $content = '',
                                array $headers = array())
    {
        parent::__construct($statusCode, $content, $headers);
        $this->setViews($views);
    }

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
