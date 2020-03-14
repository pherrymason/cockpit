<?php declare(strict_types=1);

namespace Cockpit\Framework\Database;

use Doctrine\DBAL\Connection;


final class MySQLStorage implements DatabaseConnection
{
    /** @var Connection */
    private $connection;

    public function __construct(string $host, string $dbName, string $user, string $password)
    {
        $config = new \Doctrine\DBAL\Configuration();
        $connectionParams = [
            'driver' => 'pdo_mysql',
            'host' => $host,
            'dbname' => $dbName,
            'user' => $user,
            'password' => $password,
            'charset' => 'UTF8'
        ];

        $this->connection = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
    }

    public function type(): string
    {
        return 'mysql';
    }

    public function find(string $collection, array $options = [])
    {
        $table = $this->decodeTableName($collection);
//        [$db, $table] = $this->disassembleCollection($collection);
        $filters = $options['filter'] ?? [];
        $fields = isset($options['fields']) && $options['fields'] ? $options['fields'] : null;
        $limit = $options['limit'] ?? null;
        $sort = $options['sort'] ?? null;
        $skip = $options['skip'] ?? null;

        $params = [];
        $query = 'SELECT * FROM ' . $table;

        if (count($filters)) {
            $conditions = [];
            $i = 0;
            foreach ($filters as $field => $value) {
                $conditions[] = '`' . $field . '`= :value' . $i;
                $params['value' . $i] = $value;
                $i++;
            }

            $query .= ' WHERE ' . implode(' AND ', $conditions);
        }

        if ($sort) {
            $sortFields = [];
            foreach ($sort as $field => $order) {
                $sortFields[] = '`' . $field . '` ' . ($order === -1 ? 'DESC' : 'ASC');
            }

            $query .= ' ORDER BY ' . implode(', ', $sortFields);
            //$params['sortField'] = $sort;
        }

        if ($limit) {
            $query .= ' LIMIT ' . $limit;

            if ($skip) {
                $query .= ' OFFSET ' . $skip;
            }
        }

        try {
            $stmt = $this->connection->executeQuery($query, $params);

            return new ResultSet($stmt->fetchAll());
        } catch (\Exception $e) {
            throw new DatabaseException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function findOne(string $collection, array $filter = [], $projection = null)
    {
        $resultSet = $this->find($collection, ['filter' => $filter]);

        return $resultSet->count() > 0 ? $resultSet->toArray()[0] : null;
    }

    public function insert(string $collection, &$doc)
    {
        $this->save($collection, $doc[0]);
    }

    public function insertBulk(string $string, array $docs)
    {
        foreach ($docs as $doc) {
            $this->insert($string, $doc);
        }
    }

    public function save(string $collection, &$data)
    {
        $table = $this->decodeTableName($collection);

        $fieldNames = array_map(function ($value) {
            return $this->connection->quoteIdentifier($value);
        }, array_keys($data));

        [$assignment, $params] = $this->queryAssignmentValues($data);

        $create = false;
        if (!isset($data['_id'])) {
            $create = true;
            $id = createMongoDbLikeId();
            $data['_id'] = $id;
            $assignment[] = $this->connection->quoteIdentifier('_id') . '= :_id';
            $params['_id'] = $id;
        }

        try {
            if ($create) {
                $sql = 'INSERT INTO ' . $table . ' SET ' . implode(', ', $assignment);
            } else {
                $sql = 'UPDATE ' . $table . ' SET ' . implode(', ', $assignment) . ' WHERE _id=:id';
                $params['id'] = $data['_id'];
            }

            $rowsCount = $this->connection->executeUpdate($sql, $params);

            return $data;
        } catch (\Exception $e) {
            throw new DatabaseException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function update(string $collection, array $criteria, $data): int
    {
        $table = $this->decodeTableName($collection);
        [$assignment, $params] = $this->queryAssignmentValues($data);

        $sql = 'UPDATE ' . $table . ' SET ' . implode(', ', $assignment) . ' WHERE _id=:_id';

        try {
            return $this->connection->executeUpdate($sql, $params);
        } catch (\Exception $e) {
            throw new DatabaseException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function remove(string $collection, array $filter = []): int
    {
        [$assignment, $params] = $this->queryAssignmentValues($filter);
        $sql = 'DELETE FROM ' . $this->decodeTableName($collection) . ' WHERE ' . implode(' AND ', $assignment);

        try {
            return $this->connection->executeUpdate($sql, $params);
        } catch (\Exception $e) {
            throw new DatabaseException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function count(string $collection, ?array $filter = []): int
    {
        $filter = $filter ?? [];
        [$assignment, $params] = $this->queryAssignmentValues($filter);
        $sql = 'SELECT COUNT(_id) from ' . $this->decodeTableName($collection);
        if (count($assignment)) {
            $sql.= ' WHERE ' . implode(' AND ', $assignment);
        }

        try {
            $result = $this->connection->executeQuery($sql, $params);
            $data = $result->fetch();
            return (int)($data['COUNT(_id)'] ?? 0);
        } catch (\Exception $e) {
            throw new DatabaseException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function createTable(string $table, array $fields)
    {
        $columns = [
            '_id char(36) NOT NULL'
        ];
        foreach ($fields as $name => $type) {
            $columns[] = $this->connection->quoteIdentifier($name) . ' ' . $this->cockpitFieldTypeToSQLType($type) . ' DEFAULT \'\'';
        }
        $columns[] = '_created';
        $columns[] = '_modified';
        $columns[] = '_by';
        $columns[] = '_mby';

        $sql = 'CREATE TABLE `'.$table.'` (' . implode(', ', $columns) . ', PRIMARY KEY(`_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4';
        $this->connection->executeQuery($sql);
    }

    private function cockpitFieldTypeToSQLType(string $type)
    {
        switch ($type) {
            case 'text':    return 'TEXT';
        }
    }

    /**
     * @param string|array $key
     */
    public function removeKey(string $collection, $key)
    {
        // TODO: Implement removeKey() method.
    }

    /**
     * As findOne, but allows to return a default value if register not found.
     */
    public function getKey(string $collection, string $key, $default = null)
    {
        // TODO: Implement getKey() method.
    }

    public function setKey(string $collection, string $key, $value)
    {
        // TODO: Implement setKey() method.
    }

    public function rpush(string $collection, string $key, $value)
    {
        // TODO: Implement rpush() method.
    }

    protected function decodeTableName(string $collection): string
    {
        return str_replace('/', '_', $collection);
    }

    protected function queryAssignmentValues(array $data): array
    {
        $assignment = [];
        $params = [];
        $i = 0;
        foreach ($data as $field => $value) {
            $assignment[] = $this->connection->quoteIdentifier($field) . '= :value' . $i;
            if (!is_string($value)) {
                $value = \json_encode($value);
            }
            $params['value' . $i] = $value;
            $i++;
        }

        return array($assignment, $params);
    }
}


function createMongoDbLikeId()
{
    // use native MongoDB ObjectId if available
    if (class_exists('MongoDB\\BSON\\ObjectId')) {
        $objId = new \MongoDB\BSON\ObjectId();
        return (string)$objId;
    }

    // based on https://gist.github.com/h4cc/9b716dc05869296c1be6

    $timestamp = \microtime(true);
    $processId = \random_int(10000, 99999);
    $id = \random_int(10, 1000);
    $result = '';

    // Building binary data.
    $bin = \sprintf(
        '%s%s%s%s',
        \pack('N', $timestamp),
        \substr(md5(uniqid()), 0, 3),
        \pack('n', $processId),
        \substr(\pack('N', $id), 1, 3)
    );

    // Convert binary to hex.
    for ($i = 0; $i < 12; $i++) {
        $result .= \sprintf('%02x', ord($bin[$i]));
    }

    return $result;
}