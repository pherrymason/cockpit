<?php declare(strict_types=1);

namespace Cockpit\Framework;

use Cockpit\AuthController;
use League\Plates\Engine;
use Lime\App;

abstract class Controller extends AuthController
{
    /** @var Engine */
    protected $templateEngine;

    public function __construct(App $app, Engine $templateEngine)
    {
        parent::__construct($app);
        $this->templateEngine = $templateEngine;
    }

    public function renderTemplate(string $template, array $data = [])
    {
        return $this->templateEngine->render($template, $data);
    }
}
