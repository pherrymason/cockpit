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
        $sql = 'SELECT *, entry.id as _id FROM ' . $tableName . ' as entry ';

        if ($collection->hasLocalizedFields()) {
            $sql .= 'LEFT JOIN ' . $tableName . '_content as content ON content.entry_id=entry.id';
        }

        $limit = $options['limit'] ?? null;
        $skip = $options['skip'] ?? null;
        $sort = $options['sort'] ?? null;
        $constraint = new Constraint(null, $limit, $sort, $skip);
        list($sql, $params) = $this->applyConstraints($constraint, $sql);

        $entries = $this->db->query($sql)->fetchAll();

        $harmonized = $this->mergeResults($collection, $entries);

        $entries = array_map(function ($item) use ($collection) {
            return $this->hydrate($item, $collection);
        }, $harmonized);

        return array_values($entries);
    }

    public function byId(Collection $collection, string $id): ?Entry
    {
        $tableName = $this->tableManager->tableName($collection->name());
        $sql = 'SELECT *, entry.id as _id FROM '. $tableName . ' as entry ';

        if ($collection->hasLocalizedFields()) {
            $sql.= 'LEFT JOIN ' . $tableName . '_content as content ON content.entry_id=entry.id ';
        }

        $sql.= 'WHERE entry.id=:id ' .
            'ORDER BY _modified DESC';

        $stmt = $this->db->executeQuery($sql, ['id' => $id]);
        $harmonized = $this->mergeResults($collection, $stmt->fetchAll());

        if (count($harmonized)===0) {
            return null;
        }

        return $this->hydrate(array_values($harmonized)[0], $collection);
    }

    public function revisionsById(Collection $collection, string $id)
    {
        $sql = 'SELECT * FROM '.$this->tableManager->tableName($collection->name()).' WHERE id=:id ';

        $stmt = $this->db->executeQuery($sql, ['id' => $id]);

        return array_map(function ($item) use ($collection) {
            return $this->hydrate($item, $collection);
        }, $stmt->fetchAll());
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
                            if (!isset($entry[$field->name().'_'.$langCode])) {
                                continue;
                            }
                            $localizedValues[$field->name().'_'.$langCode] = $entry[$field->name().'_'.$langCode];
                        }
                    } else {
                        $params[$field->name()] = $entry[$field->name()];
                    }
                    break;

                case Field::TYPE_IMAGE:
                case Field::TYPE_ASSET:
                case Field::TYPE_GALLERY:
                    $types[$field->name()] = ParameterType::STRING;
                    $value = empty($entry[$field->name()]) ? null : json_encode($entry[$field->name()]);
                    if ($field->localize()) {
                        $localizedValues[$field->name()] = $value;
                    } else {
                        $params[$field->name()] = $value;
                    }
                    break;
            }
        }

        $params['_modified'] = $entry['_modified'];
        $params['_created'] = $entry['_created'];

        // Main Entry table
        $tableName = $this->tableManager->tableName($collection->name());
        if (!isset($entry['_id'])) {
            $params['id'] = IDs::new();
        } else {
            $params['id'] = $entry['_id'];
        }

        $fieldNames = array_map(function (string $name) {
            return '`' . $name . '`';
        }, array_keys($params));
        $fields = [];
        $updateFields = [];
        foreach ($params as $key => $value) {
            $fields[] = ':' . $key;
            $types[$key] = ParameterType::STRING;
            $updateFields[] = '`' . $key . '`=:' . $key;
        }

        $sql = 'INSERT INTO '.$tableName.' (' . implode(', ', $fieldNames) . ') '.
                'VALUES (' . implode(', ', $fields) . ') ' .
            'ON DUPLICATE KEY UPDATE ' . implode(', ', $updateFields);

        $stmt = $this->db->executeUpdate($sql, $params, $types);
        $toHydrateEntry = $params;

        // Localizable data
        $tableName = $this->tableManager->tableName($collection->name().'_content');
        foreach ($this->languages as $langCode => $langName) {
            $lParams = [
                'id' => IDs::new(),
                'entry_id' => $params['id'],
                'language' => $langCode
            ];
            $fieldPlaceholders = [];

            foreach ($localizedFields as $field) {
                $name = $field->name();
                $index = $name.'_'.$langCode;
                if (isset($localizedValues[$index])) {
                    $lParams[$name] = $localizedValues[$index];
                }
            }

            $fieldNames = array_map(function (string $name) {
                return '`' . $name . '`';
            }, array_keys($lParams));

            $updateFields = [];
            foreach ($lParams as $key => $value) {
                $fieldPlaceholders[] = ':' . $key;
                $updateFields[] = '`' . $key . '`=:' . $key;
            }

            $sql = 'INSERT INTO ' . $tableName . ' (' . implode(', ', $fieldNames) . ')' .
                'VALUES (' . implode(', ', $fieldPlaceholders) . ') ' .
                'ON DUPLICATE KEY UPDATE ' . implode (', ', $updateFields);

            $stmt = $this->db->executeUpdate($sql, $lParams, $types);
            $toHydrateEntry['localized'][$langCode] = $lParams;
        }

        $entry = $this->hydrate($toHydrateEntry, $collection);

        return $entry;
    }

    private function hydrate(array $data, Collection $collection): Entry
    {
        $revisionID = $data['rev_id'] ?? null;
        $previousRevisionID = $data['prev_rev_id'] ?? null;

        foreach ($collection->fields() as $field) {
            if ($field->localize() === false) {
                switch ($field->type()) {
                    case Field::TYPE_GALLERY:
                        $fieldName = $field->name();
                        $value = $data[$fieldName];
                        $data[$fieldName] = $value === null ? [] : json_decode($data[$fieldName], true);
                        break;
                }
            }
        }

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
