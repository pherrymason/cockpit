<?php declare(strict_types=1);

namespace Cockpit\Collections;

use Doctrine\DBAL\Connection;
use Cockpit\Framework\IDs;

final class DBCollectionRepository implements CollectionRepository
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

    public function byId(string $id): ?Collection
    {
        $sql = 'SELECT * FROM cockpit_collections WHERE id=:id';
        $stmt = $this->db->executeQuery($sql, ['id' => $id]);

        if ($stmt->rowCount() === 0) {
            return null;
        }

        return $this->hydrate($stmt->fetch());
    }

    public function all()
    {
        $sql = 'SELECT * FROM cockpit_collections ORDER BY name ASC';
        $stmt = $this->db->query($sql);

        return array_map([$this, 'hydrate'], $stmt->fetchAll());
    }

    public function byName(string $name): ?Collection
    {
        $sql = 'SELECT * FROM cockpit_collections WHERE `name`=:name ORDER BY name ASC';
        $stmt = $this->db->executeQuery($sql, ['name' => $name]);

        if ($stmt->rowCount() === 0) {
            return null;
        }

        $data = $stmt->fetch();

        return $this->hydrate($data);
    }

    public function byGroup($group, bool $extended = false)
    {
        //if (!$group) {
        //    $group = $this->app->module('cockpit')->getGroup();
        //}

        $_collections = $this->all();
        $collections = [];

        return $_collections;
        // @todo
        if ($this->app->module('cockpit')->isSuperAdmin()) {
            return $_collections;
        }

        foreach ($_collections as $collection => $meta) {
            if (isset($meta['acl'][$group]['entries_view']) && $meta['acl'][$group]['entries_view']) {
                $collections[$collection] = $meta;
            }
        }

        return $collections;
    }

    public function save(array $collection)
    {
        $saved = $this->byName($collection['name']);

        $params = [
            'name' => $collection['name'],
            'label' => $collection['label'],
            'color' => $collection['color'],
            'description' => $collection['description'],
            'fields' => json_encode($collection['fields'], JSON_PRETTY_PRINT),
            'acl' => json_encode($collection['acl'], JSON_PRETTY_PRINT),
            'sortable' => $collection['sortable'],
            'in_menu' => $collection['in_menu']
        ];

        if ($saved) {
            $sql = 'UPDATE cockpit_collections SET name=:name, label=:label, description=:description, color=:color, fields=:fields, acl=:acl, sortable=:sortable, in_menu=:in_menu WHERE id=:id';
            $params['id'] = $saved->id();
        } else {
            $params['id'] = IDs::new();
            $sql = 'INSERT INTO cockpit_collections (`id`, `name`, `label`, `description`, `color`, `fields`, `acl`, `sortable`, `in_menu`) VALUES(:id, :name, :label, :description, :color, :fields, :acl, :sortable, :in_menu);';
        }

        try {
            $this->db->executeUpdate($sql, $params);
            $this->tableManager->updateCollectionTable($collection['name'], $collection);
        } catch (\Exception $e) {

        }
    }

    private function hydrate($data)
    {
        $fieldsArray = json_decode($data['fields'], true);
        $fields = array_map(function ($data) {
            return Field::fromArray($data);
        }, $fieldsArray);


        return new Collection(
            $data['id'], $data['name'], $data['label'], $data['description'] ?? '', (string)$data['color'], $fields, json_decode($data['acl'], true), (bool)$data['sortable'], (bool)$data['in_menu']
        );
        //json_decode($data['rules'], true)
    }


}
