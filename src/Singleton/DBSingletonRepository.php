<?php declare(strict_types=1);

namespace Cockpit\Singleton;

use Cockpit\Collections\Field;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;

final class DBSingletonRepository implements SingletonRepository
{
    const TABLE = 'cockpit_singletons';

    /** @var Connection */
    private $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function byGroup(string $userGroup): array
    {
        $stmt = $this->db->executeQuery('SELECT * FROM '.self::TABLE . ' ORDER BY `label` ASC');

        return array_map([$this, 'hydrate'], $stmt->fetchAll());
    }

    public function byName(string $name): ?Singleton
    {
        $stmt = $this->db->executeQuery('SELECT * FROM '.self::TABLE .
            ' WHERE `name`=:name ' .
            ' ORDER BY `label` ASC', ['name' => $name], ['name' => ParameterType::STRING]);


        return $stmt->rowCount() === 0 ? null : $this->hydrate($stmt->fetch());
    }

    public function save(Singleton $singleton)
    {
        $params = [
            '_id' => $singleton->id(),
            'name' => $singleton->name(),
            'label' => $singleton->label(),
            'description' => $singleton->description(),
            'template' => $singleton->description(),
            'fields' => json_encode(array_map(function (Field $field) {return $field->toArray();}, $singleton->fields()))
        ];
        $updateFields = [];
        $types = [];
        $fieldNames = array_keys($params);
        foreach ($params as $key => $value) {
            $fields[] = ':'.$key;
            $types[$key] = ParameterType::STRING;
            $updateFields[] = '`' . $key . '`=:'.$key;
        }

        $sql = 'INSERT INTO ' . self::TABLE . ' (' . implode(', ', $fieldNames) . ') '.
                'VALUES (' . implode(', ', $fields) . ') ' .
            'ON DUPLICATE KEY UPDATE ' . implode(', ', $updateFields);

        $this->db->executeUpdate($sql, $params, $types);
    }

    public function saveData(string $name, $data)
    {
        $sql = 'UPDATE `' . self::TABLE . '` SET `data`=:data WHERE name=:name';
        $this->db->executeUpdate(
            $sql,
            [
                'data' => json_encode($data),
                'name' => $name
            ],
            [
                'data' => ParameterType::STRING,
                'name' => ParameterType::STRING
            ]);
    }

    private function hydrate(array $data): Singleton
    {
        return Singleton::create(
            $data['_id'], $data['name'], $data['label'], $data['description'], json_decode($data['fields'], true), $data['template'], json_decode($data['data'], true)
        );
    }
}
