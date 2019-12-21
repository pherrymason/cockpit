<?php declare(strict_types=1);

namespace Cockpit\Singleton;

interface SingletonRepository
{
    /**
     * @return Singleton[]
     */
    public function byGroup($userGroup): array;

    public function byName($name): ?Singleton;

    public function save(Singleton $singleton);
}
