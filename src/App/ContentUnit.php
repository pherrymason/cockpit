<?php declare(strict_types=1);

namespace Cockpit\App;

interface ContentUnit
{
    public function id(): string;

    public function toArray(): array;
}
