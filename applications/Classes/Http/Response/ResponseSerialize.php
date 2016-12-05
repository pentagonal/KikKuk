<?php
namespace KikKuk\Http\Response;

use Psr\Http\Message\ResponseInterface;

/**
 * Class ResponseSerialize
 * @package KikKuk\Http\Response
 */
class ResponseSerialize extends ResponseAbstract
{
    /**
     * @var string
     */
    protected $mimeType = 'text/plain';

    /**
     * @var bool
     */
    protected $recheckMimeType = false;

    /**
     * {@inheritdoc}
     * @return ResponseInterface
     */
    public function serve()
    {
        return ResponseText::generate($this->request, $this->response)
            ->setData($this->data)
            ->setCharset($this->charset)
            ->serve();
    }
}
