<?php declare(strict_types=1);

namespace Cockpit\Framework\Database;

final class ResultSet
{
    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function toArray(): array
    {
        return $this->data;
    }

    public function count(): int
    {
        return count($this->data);
    }
}
