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
    use EntriesMergeResultsTrait;

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

    public function byCollectionFiltered(Collection $collection, array $fieldsFilter, $options)
    {
        $tableName = $this->tableManager->tableName($collection->name());
        $sql = 'SELECT *, entry.id as _id FROM ' . $tableName . ' as entry ';

        $params = [];
        if ($collection->hasLocalizedFields()) {
            $sql .= 'LEFT JOIN ' . $tableName . '_content as content ON content.entry_id=entry.id AND content.language=:lang';
            $params['lang'] = $options['lang'];
        }

        $limit = $options['limit'] ?? null;
        $skip = $options['skip'] ?? null;
        $sort = $options['sort'] ?? null;
        $constraint = new Constraint($fieldsFilter, $limit, $sort, $skip);
        list($sql, $constraintParams) = $this->applyConstraints($constraint, $sql);
        $params = array_merge($params, $constraintParams);

        $entries = $this->db->executeQuery($sql, $params)->fetchAll();

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
                case Field::TYPE_BOOLEAN:
                    $type = ParameterType::BOOLEAN;
                    break;
                default:
                    $type = ParameterType::STRING;
                    break;
            }



            switch ($field->type()) {
                default:
                    $types[$field->name()] = $type;
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

                /*case Field::TYPE_BOOLEAN:
                    $types[$field->name()] = ParameterType::STRING;
                    if ($field->localize()) {

                    } else {
                        $params[$field->name()] =
                    }

                    break;
*/
                case Field::TYPE_TAGS:
                    $params[$field->name()] = json_encode($entry[$field->name()]);
                    break;

                case Field::TYPE_IMAGE:
                case Field::TYPE_ASSET:
                case Field::TYPE_GALLERY:
                case Field::TYPE_SET:
                    $types[$field->name()] = $type;
                    if ($field->localize()) {
                        $localizedFields[] = $field;
                        foreach ($this->languages as $langCode => $lang) {
                            if (!isset($entry[$field->name().'_'.$langCode])) {
                                continue;
                            }

                            $fieldName = $field->name() . '_' . $langCode;
                            $value = $entry[$fieldName];
                            $localizedValues[$fieldName] = json_encode($value);
                        }
                    } else {
                        $value = empty($entry[$field->name()]) ? null : json_encode($entry[$field->name()]);
                        $params[$field->name()] = $value;
                    }
                    break;
            }
        }

        $params['_modified'] = $entry['_modified'];
        $params['_created'] = is_numeric($entry['_created']) ? date('Y-m-d H:i:s', $entry['_created']): $entry['_created'];

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

    public function hydrate(array $data, Collection $collection): Entry
    {
        $revisionID = $data['rev_id'] ?? null;
        $previousRevisionID = $data['prev_rev_id'] ?? null;

        foreach ($collection->fields() as $field) {
            if ($field->localize() === false) {
                switch ($field->type()) {
                    case Field::TYPE_GALLERY:
                    case Field::TYPE_SET:
                    case Field::TYPE_TAGS:
                        $fieldName = $field->name();
                        $value = $data[$fieldName];
                        $data[$fieldName] = $value === null ? [] : json_decode($value, true);
                        break;
                }
            } else {
                foreach ($this->languages as $langCode => $lang) {
                    switch($field->type()) {
                        case Field::TYPE_SET:
                        case Field::TYPE_IMAGE:
                        case Field::TYPE_ASSET:
                        case Field::TYPE_GALLERY:
                            $value = $data['localized'][$langCode][$field->name()] ?? null;
                            $data['localized'][$langCode][$field->name()] = $value === null ? [] : json_decode($value, true);
                            break;
                    }
                }
            }
        }

        return new Entry($data['id'], $data, $collection->fields());
    }
}
