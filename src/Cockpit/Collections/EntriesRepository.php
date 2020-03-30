<?php declare(strict_types=1);

namespace Cockpit\Collections;

interface EntriesRepository
{
    public function byCollectionFiltered(Collection $collection, array $fieldsFilter, $options);

    public function byId(Collection $collection, string $id): ?Entry;

    public function revisionsById(Collection $collection, string $id);

    public function count(Collection $collection, array $filter = []) : int;

    public function save(Collection $collection, array $entry, array $options): Entry;
}
