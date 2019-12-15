<?php declare(strict_types=1);

namespace Framework\Database\MongoDB;

use Framework\Database\DatabaseConnection;

/**
 * Wraps MongoLite\MongoHybrid\Mongo
 */
final class Mongo implements DatabaseConnection
{
    /** @var \Framework\Database\MongoLite\MongoHybrid\Mongo */
    private $driver;

    public function __construct(string $server, array $options = [], array $driverOptions = [])
    {
        $this->driver = new \Framework\Database\MongoLite\MongoHybrid\Mongo($server, $options, $driverOptions);
    }

    public function type(): string
    {
        return 'mongodb';
    }

    public function find(string $collection, array $options = [])
    {
        return $this->driver->find($collection, $options);
    }

    public function findOne(string $collection, array $filter = [], $projection = null)
    {
        return $this->driver->findOne($collection, $filter, $projection);
    }

    /**
     * Not used
     */
    public function findOneById(string $collection, string $id)
    {
        return $this->driver->findOneById($collection, $id);
    }

    public function insert(string $collection, &$doc)
    {
        return $this->driver->insert($collection, $doc);
    }

    public function save(string $collection, &$data)
    {
        return $this->driver->save($collection, $data, $create);
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

    /**
     * @param string|array $key
     */
    public function removeKey(string $collection, $key)
    {
        return $this->driver->remove($collection, ['key' => (is_array($key) ? ['$in' => $key] : $key)]);
    }

    /**
     * As findOne, but allows to return a default value if register not found.
     */
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

    public function rpush(string $collection, string $key, $value)
    {
        $list = $this->getKey($collection, $key, []);

        $list[] = $value;

        $this->setKey($collection, $key, $list);

        return count($list);
    }
}
