<?php declare(strict_types=1);

namespace Framework\Database\MongoLite;

use Framework\Database\DatabaseConnection;
use Framework\Database\MongoLite\MongoHybrid\MongoLite as Driver;

/**
 * Wraps \MongoHybrid\MongoLite
 */
final class MongoLite implements DatabaseConnection
{
    /** @var Driver */
    protected $driver;

    public static function create(string $schema, array $options = [], array $driverOptions = []): DatabaseConnection
    {
        return new MongoLite($schema, $options);
    }

    public function __construct(string $databasesPath, array $parameters = [])
    {
        $this->driver = new Driver($databasesPath, $parameters);
    }

    public function type(): string
    {
        return 'mongolite';
    }

    public function find(string $collection, array $options = [])
    {
        return $this->driver->find($collection, $options);
    }

    public function findOne(string $collection, array $filter = [], $projection = null)
    {
        return $this->driver->getCollection($collection)->findOne($filter, $projection);
    }

    public function getKey(string $collection, string $key, $default = null)
    {
        $entry = $this->driver->findOne($collection, ['key' => $key]);

        return $entry ? $entry['val'] : $default;
    }

    public function setKey(string $collection, string $key, $value)
    {
        $entry = $this->driver->findOne($collection, ['key' => $key]);

        if ($entry) {
            $entry['val'] = $value;
        } else {
            $entry = [
                'key' => $key,
                'val' => $value
            ];
        }

        return $this->driver->save($collection, $entry);
    }

    public function removeKey(string $collection, $key)
    {
        return $this->driver->remove($collection, ['key' => (is_array($key) ? ['$in' => $key] : $key)]);
    }

    public function rpush(string $collection, string $key, $value)
    {
        $list = $this->getKey($collection, $key, []);

        $list[] = $value;

        $this->setKey($collection, $key, $list);

        return count($list);
    }

    public function insert(string $collection, &$doc)
    {
        return $this->driver->insert($collection, $doc);
    }

    public function save(string $collection, &$data)
    {
        return $this->driver->save($collection, $data);
    }

    public function update(string $collection, array $criteria, $data)
    {
        return $this->driver->update($collection, $criteria, $data);
    }

    public function remove(string $collection, array $filter = [])
    {
        return $this->driver->remove($collection, $filter);
    }

    public function count(string $collection, array $filter = [])
    {
        return $this->driver->count($collection, $filter);
    }
}
