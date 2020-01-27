<?php declare(strict_types=1);

namespace Cockpit\Collections;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Cockpit\Framework\Database\Constraint;
use Cockpit\Framework\Database\MysqlConstraintQueryBuilder;
use Cockpit\Framework\IDs;

final class DBEntriesRepository implements EntriesRepository
{
    use MysqlConstraintQueryBuilder;

    /** @var Connection */
    private $db;
    /** @var MySQLCollectionTableManager */
    private $tableManager;
    /** @var array */
    private $languages;

    public function __construct(Connection $db, MySQLCollectionTableManager $tableManager, array $languages = [])
    {
        $this->db = $db;
        $this->tableManager = $tableManager;
        $this->languages = $languages;
    }

    public function byCollectionFiltered(Collection $collection, $options)
    {
        $tableName = $this->tableManager->tableName($collection->name());
        $sql = 'SELECT *, entry.id as _id FROM ' . $tableName . ' as entry '.
            'LEFT JOIN ' . $tableName . '_content as content ON content.entry_id=entry.id';
// SELECT *, post.id as _id, content.id as content_id FROM cockpit_collection_post as post
//LEFT JOIN cockpit_collection_post_content as content ON content.entry_id=post.id
        $limit = $options['limit'] ?? null;
        $skip = $options['skip'] ?? null;
        $sort = $options['sort'] ?? null;
        $constraint = new Constraint(null, $limit, $sort, $skip);
        $sql = $this->applyConstraints($constraint, $sql);

        $entries = $this->db->query($sql)->fetchAll();

        $harmonized = $this->mergeResults($collection, $entries);

        $entries = array_map([$this, 'hydrate'], $harmonized);

        return array_values($entries);
    }

    public function byId(Collection $collection, string $id): ?Entry
    {
        $tableName = $this->tableManager->tableName($collection->name());
        $sql = 'SELECT *, entry.id as _id FROM '. $tableName . ' as entry '.
            'LEFT JOIN ' . $tableName . '_content as content ON content.entry_id=entry.id ' .
            'WHERE entry.id=:id ' .
            'ORDER BY _modified DESC LIMIT 1';

        $stmt = $this->db->executeQuery($sql, ['id' => $id]);
        $harmonized = $this->mergeResults($collection, $stmt->fetchAll());

        return $this->hydrate(array_values($harmonized)[0]);
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
        $localizedValues = [];
        $localizedFields = [];
        $types = [];

        foreach ($collection->fields() as $field) {
            switch ($field->type()) {
                default:
                    $types[$field->name()] = ParameterType::STRING;
                    if ($field->localize()) {
                        $localizedFields[] = $field;
                        foreach ($this->languages as $langCode => $lang) {
                            $localizedValues[$field->name().'_'.$langCode] = $entry[$field->name().'_'.$langCode];
                        }
                    } else {
                        $params[$field->name()] = $entry[$field->name()];
                    }
                    break;

                case Field::TYPE_IMAGE:
                    $types[$field->name()] = ParameterType::STRING;
                    if ($field->localize()) {
                        $localizedValues[$field->name()] = json_encode($entry[$field->name()]);
                    } else {
                        $params[$field->name()] = json_encode($entry[$field->name()]);
                    }
                    break;
            }
        }

        $types['id'] = ParameterType::STRING;
        $types['_modified'] = ParameterType::STRING;
        $types['_created'] = ParameterType::STRING;
        $params['_modified'] = $entry['_modified'];
        $params['_created'] = $entry['_created'];

        // Main Entry table
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

        // Localizable data
        $tableName = $this->tableManager->tableName($collection->name().'_content');
        $lParams = [];
        foreach ($this->languages as $langCode => $langName) {
            $lParams['id'] = IDs::new();
            $lParams['entry_id'] = $params['id'];
            $fieldNames = $fieldPlaceholders = [];
            /*
            $fields = [];
            foreach ($localizedParams as $key => $value) {
                $fields[] = ':' . $key;
            }*/
            foreach ($localizedFields as $field) {
                $name = $field->name();
                $lParams[$name] = $localizedValues[$name.'_'.$langCode];
            }

            $fieldNames = array_map(function (string $name) {
                return '`' . $name . '`';
            }, array_keys($lParams));

            foreach ($lParams as $key => $value) {
                $fieldPlaceholders[] = ':' . $key;
            }

            $sql = 'INSERT INTO ' . $tableName . ' (' . implode(', ', $fieldNames) . ')' .
                'VALUES (' . implode(', ', $fieldPlaceholders) . ');';
            $stmt = $this->db->executeUpdate($sql, $lParams, $types);
        }

        $entry = $this->hydrate(array_merge($params, $localizedValues));

        return $entry;
    }

    private function hydrate(array $data): Entry
    {
        $revisionID = $data['rev_id'] ?? null;
        $previousRevisionID = $data['prev_rev_id'] ?? null;

        return new Entry($data['id'], $data);
    }

    protected function mergeResults(Collection $collection, array $entries): array
    {
        /** @var Field[] $baseFields */
        $baseFields = array_filter($collection->fields(), function (Field $field) {
            return !$field->localize();
        });
        /** @var Field[] $localizedFields */
        $localizedFields = array_filter($collection->fields(), function (Field $field) {
            return $field->localize();
        });

        $harmonized = [];
        foreach ($entries as $entry) {
            $id = $entry['_id'];
            if (!isset($harmonized[$id])) {
                // Copy fields
                $harmonized[$id] = [
                    'id' => $id,
                    '_created' => $entry['_created'],
                    '_modified' => $entry['_modified'],
                    '_by' => $entry['_by'],
                    '_mby' => $entry['_mby']
                ];
                foreach ($baseFields as $field) {
                    $harmonized[$id][$field->name()] = $entry[$field->name()] ?? null;
                }
            }

            // localized Fields
            $harmonized[$id]['localized'][$entry['language']] = [];
            foreach ($localizedFields as $field) {
                $language = $entry['language'];
                if (!isset($harmonized[$id]['localized'][$language])) {
                    $harmonized[$id]['localized'][$language] = ['language' => $language];
                }
                $harmonized[$id]['localized'][$language][$field->name()] = $entry[$field->name()] ?? null;
            }
        }
        return $harmonized;
    }
}
