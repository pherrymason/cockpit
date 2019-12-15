<?php declare(strict_types=1);

namespace Cockpit\Collections;

interface EntriesRepository
{
    public function byCollectionFiltered(Collection $collection, $options);

    public function byId(Collection $collection, string $id): ?Entry;

    public function save(Collection $collection, array $entry, array $options): Entry;
}
