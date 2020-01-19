<?php declare(strict_types=1);

namespace Cockpit\Collections;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Framework\Database\Constraint;
use Framework\Database\MysqlConstraintQueryBuilder;
use Framework\IDs;

final class DBEntriesRepository implements EntriesRepository
{
    use MysqlConstraintQueryBuilder;

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
        $sql = 'SELECT *, id as _id FROM '.$this->tableManager->tableName($collection->name());

        $limit = $options['limit'] ?? null;
        $skip = $options['skip'] ?? null;
        $sort = $options['sort'] ?? null;
        $constraint = new Constraint(null, $limit, $sort, $skip);
        $sql = $this->applyConstraints($constraint, $sql);

        $entries = $this->db->query($sql)->fetchAll();
        $entries = array_map([$this, 'hydrate'], $entries);

        return $entries;
    }

    public function byId(Collection $collection, string $id): ?Entry
    {
        $sql = 'SELECT * FROM '.$this->tableManager->tableName($collection->name()).' WHERE id=:id ';
        $sql.= 'ORDER BY _modified DESC LIMIT 1';

        $stmt = $this->db->executeQuery($sql, ['id' => $id]);

        return $this->hydrate($stmt->fetch());
    }

    public function revisionsById(Collection $collection, string $id)
    {
        $sql = 'SELECT * FROM '.$this->tableManager->tableName($collection->name()).' WHERE id=:id ';

        $stmt = $this->db->executeQuery($sql, ['id' => $id]);

        return array_map([$this, 'hydrate'], $stmt->fetchAll());
    }

    public function count(Collection $collection, array $filter = []): int
    {
        $sql = 'SELECT * FROM '.$this->tableManager->tableName($collection->name());

        $stmt = $this->db->executeQuery($sql);

        return count($stmt->fetchAll());
    }

    public function save(Collection $collection, array $entry, array $options): Entry
    {
        $options = array_merge(['revision' => false], $options);
        $params = [];
        $types = [];

        foreach ($collection->fields() as $field) {
            switch ($field->type()) {
                default:
                    $types[$field->name()] = ParameterType::STRING;
                    $params[$field->name()] = $entry[$field->name()];
                    break;

                case Field::TYPE_IMAGE:
                    $types[$field->name()] = ParameterType::STRING;
                    $params[$field->name()] = json_encode($entry[$field->name()]);
                    break;
            }
        }

        $types['id'] = ParameterType::STRING;
        $types['_modified'] = ParameterType::STRING;
        $types['_created'] = ParameterType::STRING;
        $params['_modified'] = $entry['_modified'];
        $params['_created'] = $entry['_created'];


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

            $sql = 'UPDATE '.$tableName.' SET ' . implode(', ', $fields) . ' ';
            $sql.= 'WHERE id=:id';
        }

        $stmt = $this->db->executeUpdate($sql, $params, $types);

        return $this->hydrate($params);
    }

    private function hydrate(array $data): Entry
    {
        $revisionID = $data['rev_id'] ?? null;
        $previousRevisionID = $data['prev_rev_id'] ?? null;

        return new Entry($data['id'], $data);
    }
}
