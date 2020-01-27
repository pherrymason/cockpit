<?php declare(strict_types=1);

namespace Cockpit\Framework;

use Ramsey\Uuid\Uuid;

final class IDs
{
    public static function new(): string
    {
        return Uuid::uuid4()->toString();
    }
}
