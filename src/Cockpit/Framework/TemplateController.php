<?php

namespace Cockpit\Framework;

use League\Plates\Engine;
use Psr\Http\Message\ResponseInterface;

abstract class TemplateController
{
    /** @var Engine */
    protected $templateEngine;

    public function __construct(Engine $templateEngine)
    {
        $this->templateEngine = $templateEngine;
    }

    public function renderResponse(ResponseInterface $response, string $template, array $data = [], array $globalData = [])
    {
        if (count($globalData)) {
            $this->templateEngine->addData($globalData);
        }

        $html = $this->templateEngine->render($template, $data);

        $response->getBody()->write($html);

        return $response;
    }
}
