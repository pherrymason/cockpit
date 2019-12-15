<?php declare(strict_types=1);

namespace Cockpit\Collections;

use Doctrine\DBAL\Connection;

final class MySQLCollectionTableManager
{
    /** @var Connection */
    private $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function updateCollectionTable(string $tableName, array $collection)
    {
        $fields = $collection['fields'];
        /** @var Field[] $fields */
        $fields = array_map([$this, 'hydrateField'], $fields);

        /** @var string $tableName */
        if ($this->tableExists($tableName)) {
            // Update table
            $this->updateTableStructure($tableName, $fields);
        } else {
            $sql = 'CREATE TABLE `' . $tableName . '` (';
            $sql.= '`id` CHAR(36) NOT NULL, ';
            foreach ($fields as $field) {
                $columnType = $this->columnTypeFromField($field);
                $default = 'DEFAULT NULL';
                $sql.= '`'.$field->name().'` '.$columnType . ' ' . $default.', ';
            }
            $sql.= 'PRIMARY KEY (`id`)';
            $sql.= ') ENGINE=InnoDB DEFAULT CHARSET=utf8;';

            $this->db->query($sql);
            /*
            CREATE TABLE `shop_order_status` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `value` char(20) NOT NULL,
  `color` char(7) DEFAULT NULL,
  `email` char(50) DEFAULT NULL,
  `order` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            */
        }
    }

    private function columnTypeFromField(Field $field): string
    {
        switch ($field->type()) {
            default:
                return 'TEXT';
        }
    }

    private function tableExists(string $tableName): bool
    {
        $sql = 'SHOW TABLES LIKE \'' . $tableName . '\';';
        $stmt = $this->db->query($sql);

        return $stmt->rowCount() === 1;
    }


    private function hydrateField(array $field): Field
    {
        return new Field(
            $field['name'],
            $field['label'],
            $field['type'],
            $field['default'],
            $field['info'],
            $field['group'],
            $field['localize'],
            $field['options'],
            $field['width'],
            $field['lst'],
            $field['acl']
        );
    }

    /**
     * @param Field[] $fields
     */
    private function updateTableStructure(string $tableName, array $fields)
    {
        $sql = 'DESCRIBE `'.$tableName.'`';
        $stmt = $this->db->query($sql);
        $info = $stmt->fetchAll();

        $currentTable = [];
        $skippableFields = ['id'];
        foreach ($info as $row) {
            if (in_array($row['Field'], $skippableFields)) {
                continue;
            }

            $currentTable[$row['Field']] = $row;
        }

        $newTable = [];
        foreach ($fields as $field) {
            $newTable[$field->name()] = [
                'Field' => $field->name(),
                'Type' => $this->columnTypeFromField($field),
                'Null' => 'YES'
            ];
        }

        // Find new fields
        $fieldsToCreate = array_diff_key($newTable, $currentTable);
        // Find to remove fields
        $fieldsToRemove = array_diff_key($currentTable, $newTable);
        // Find to update fields
        $fieldsToUpdate = [];

        $changes = [];
        foreach ($fieldsToCreate as $field) {
            $default = ' NULL ';
            $name = $field['Field'];
            $changes[] = 'ADD `'.$name.'` '.$fieldsToCreate[$name]['Type'] . ' ' . $default;
        }

        foreach ($fieldsToRemove as $field) {
            $changes[] = ' DROP `'.$field['Field'].'`';
        }

        $sql = 'ALTER TABLE `'.$tableName.'` ';
        $sql.= implode(', ', $changes) . ';';
        $this->db->query($sql);
    }
}
