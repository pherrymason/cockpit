<?php

namespace Cockpit\App\UI;

final class MenuItem
{
    /** @var string */
    private $label;
    /** @var string */
    private $iconPath;
    /** @var string */
    private $routeName;
    /** @var bool */
    private $active;

    public function __construct(string $label, string $iconPath, string $routeName, bool $active)
    {
        $this->label = $label;
        $this->iconPath = $iconPath;
        $this->routeName = $routeName;
        $this->active = $active;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function iconPath(): string
    {
        return $this->iconPath;
    }

    public function routeName(): string
    {
        return $this->routeName;
    }

    public function active(): bool
    {
        return $this->active;
    }
}
