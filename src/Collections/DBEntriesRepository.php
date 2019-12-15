<?php declare(strict_types=1);

namespace Cockpit\Collections;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Framework\IDs;

final class DBEntriesRepository implements EntriesRepository
{
    /** @var Connection */
    private $db;
    /** @var MySQLCollectionTableManager */
    private $tableManager;

    public function __construct(Connection $db, MySQLCollectionTableManager $tableManager)
    {
        $this->db = $db;
        $this->tableManager = $tableManager;
    }

    public function byCollectionFiltered(Collection $collection, $options)
    {
        $sql = 'SELECT * FROM '.$this->tableManager->tableName($collection->name()).';';

        return $this->db->query($sql)->fetchAll();
    }

    public function byId(Collection $collection, string $id): ?Entry
    {
        $sql = 'SELECT * FROM '.$this->tableManager->tableName($collection->name()).' WHERE id=:id';

        $stmt = $this->db->executeQuery($sql, ['id' => $id]);

        return $this->hydrate($stmt->fetch());
    }

    public function save(Collection $collection, array $entry, array $options): Entry
    {
        $options = array_merge(['revision' => false], $options);
        $params = [];

        foreach ($entry as $fieldName => $value) {
            $params[$fieldName] = $value;
        }

        $types['id'] = ParameterType::STRING;
        foreach ($params as $field => $value) {
            switch ($field) {
                case '_modified':
                case '_created':
                    $types[$field] = ParameterType::STRING;
                    break;
                default:
                    $types[$field] = ParameterType::STRING;
                    break;
            }
        }

        $tableName = $this->tableManager->tableName($collection->name());
        if (!isset($params['_id'])) {
            $params['id'] = IDs::new();
            $fieldNames = array_map(function (string $name) {
                return '`' . $name . '`';
            }, array_keys($params));
            $fields = [];
            foreach ($params as $key => $value) {
                $fields[]= ':'.$key;
            }

            $sql = 'INSERT INTO '.$tableName.' (' . implode(', ', $fieldNames) . ') '.
                'VALUES (' . implode(', ', $fields) . ')';
        } else {
            $params['id'] = $params['_id'];
            unset($params['_id']);

            $fields = [];
            foreach ($params as $key => $value) {
                if ($key === 'id') {
                    continue;
                }
                $fields[]= '`'.$key.'`=:'.$key;
            }

            $sql = 'UPDATE '.$tableName.' SET ' . implode(', ', $fields);
            $sql.= 'WHERE id=:id';
        }

        $stmt = $this->db->executeUpdate($sql, $params, $types);

        return $this->hydrate($params);
    }

    private function hydrate(array $data): Entry
    {
        return new Entry($data['id'], $data);
    }
}
