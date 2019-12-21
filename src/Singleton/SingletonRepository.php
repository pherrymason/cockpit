<?php declare(strict_types=1);

namespace Cockpit\Singleton;

interface SingletonRepository
{
    /**
     * @param string $userGroup
     * @return Singleton[]
     */
    public function byGroup(string $userGroup): array;

    public function byName(string $name): ?Singleton;

    public function save(Singleton $singleton);

    public function saveData(string $name, $data);
}
