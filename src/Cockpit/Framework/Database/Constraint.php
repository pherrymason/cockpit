<?php declare(strict_types=1);

namespace Cockpit\Framework\Database;

final class Constraint
{
    /** @var int|null */
    private $limit;
    /** @var int|null */
    private $sort;
    /** @var int|null */
    private $skip;
    /** @var array|null */
    private $filter;

    public function __construct(?array $filter = null, ?int $limit = null, ?array $sort = null, ?int $skip = null)
    {
        $this->limit = $limit;
        $this->sort = $sort;
        $this->skip = $skip;
        $this->filter = $filter;
    }

    public function limit(): ?int
    {
        return $this->limit;
    }

    public function sort(): ?array
    {
        return $this->sort;
    }

    public function skip(): ?int
    {
        return $this->skip;
    }

    public function filter(): ?array
    {
        return $this->filter;
    }

    public function addFilter(string $key, ?string $value): void
    {
        $this->filter[$key] = $value;
    }
}
