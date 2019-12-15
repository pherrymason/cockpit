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

    public function tableName(string $collectionName): string
    {
        return 'cockpit_collection_'.$collectionName;
    }

    public function updateCollectionTable(string $collectionName, array $collection)
    {
        $tableName = $this->tableName($collectionName);
        $fields = $collection['fields'];
        /** @var Field[] $fields */
        $fields = array_map([$this, 'hydrateField'], $fields);

        /** @var string $tableName */
        if ($this->tableExists($tableName)) {
            $this->updateTableStructure($tableName, $fields);
        } else {
            $sql = 'CREATE TABLE `' . $tableName . '` (';
            $sql.= '`id` CHAR(36) NOT NULL, ';
            $sql.= '`rev_id` CHAR(36) NULL, ';
            $sql.= '`prev_rev_id` CHAR(36) NULL, ';
            foreach ($fields as $field) {
                $columnType = $this->columnTypeFromField($field);
                $default = 'DEFAULT NULL';
                $sql.= '`'.$field->name().'` '.$columnType . ' ' . $default.', ';
            }

            // Add mandatory columns
            $sql.= '`_by` CHAR(12) DEFAULT NULL, ';
            $sql.= '`_mby` CHAR(12) DEFAULT NULL, ';
            $sql.= '`_created` DATETIME DEFAULT NULL, ';
            $sql.= '`_modified` DATETIME DEFAULT NULL ';

            $sql.= 'PRIMARY KEY (`id`)';
            $sql.= ') ENGINE=InnoDB DEFAULT CHARSET=utf8;';

            $this->db->query($sql);
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
