<?php declare(strict_types=1);

namespace Cockpit\Collections;

interface CollectionRepository
{
    public function all();

    public function byName(string $name): ?Collection;

    public function byGroup($group, bool $extended = false);

    public function save(array $collection);
}
