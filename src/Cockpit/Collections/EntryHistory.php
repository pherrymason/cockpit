<?php declare(strict_types=1);

namespace Cockpit\Collections;

final class EntryHistory
{
    /** @var Entry[] */
    private $entries;

    public function __construct(array $entries)
    {
        $this->entries = $entries;
    }

    /**
     * @return Entry[]
     */
    public function entries(): array
    {
        return $this->entries;
    }

    // 1 [previousID] -> null
    // 2 [previousID] -> 1
    public function addRevision(Entry $entry): void
    {
        $lastEntry = $this->entries[ count($this->entries) - 1];
        $lastEntry->freezeRevision($entry);

        $this->entries[] = $entry;
    }
}
