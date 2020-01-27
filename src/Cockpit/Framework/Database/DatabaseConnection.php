<?php declare(strict_types=1);

namespace Cockpit\Framework\Database;

interface DatabaseConnection
{
    public function type(): string;

    /**
     * @throws DatabaseException
     */
    public function find(string $collection, array $options = []);

    /**
     * @throws DatabaseException
     */
    public function findOne(string $collection, array $filter = [], $projection = null);

    //public function findOneById(string $collection, string $id);

    /**
     * @throws DatabaseException
     */
    public function insert(string $collection, &$doc);

    /**
     * @throws DatabaseException
     */
    public function insertBulk(string $string, array $docs);

    /**
     * @throws DatabaseException
     */
    public function save(string $collection, &$data);

    /**
     * @throws DatabaseException
     */
    public function update(string $collection, array $criteria, $data);

    /**
     * @throws DatabaseException
     */
    public function remove(string $collection, array $filter = []);

    /**
     * @throws DatabaseException
     */
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
