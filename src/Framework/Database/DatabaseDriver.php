<?php declare(strict_types=1);

namespace Cockpit\Framework\Database;

interface DatabaseDriver
{
    public function getCollection(string $name, $db = null);

    public function dropCollection(string $name, $db = null);

    public function findOne($collection, $filter = [], $projection = null);

    public function findOneById($collection, $id);

    public function find($collection, $options = []);

    public function insert($collection, &$doc);

    public function save($collection, &$data, $create = false);

    public function update($collection, $criteria, $data);

    public function remove($collection, $filter=[]);

    public function count($collection, $filter = []);
}
