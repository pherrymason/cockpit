<?php declare(strict_types=1);

namespace Cockpit\Framework\Database;

interface DatabaseConnection
{
    public function type(): string;

    public function find(string $collection, array $options = []);

    public function findOne(string $collection, array $filter = [], $projection = null);

    public function findOneById(string $collection, string $id);

    public function insert(string $collection, &$doc);

    public function save(string $collection, &$data, bool $create = false);

    public function update(string $collection, array $criteria, $data);

    public function remove(string $collection, array $filter = []);

    public function count(string $collection, array $filter = []);

    /**
     * @param string|array $key
     */
    public function removeKey(string $collection, $key);

    /**
     * As findOne, but allows to return a default value if register not found.
     */
    public function getKey(string $collection, string $key, $default = null);

    public function setKey(string $collection, string $key, $value);

    public function rpush(string $collection, string $key, $value);
}
