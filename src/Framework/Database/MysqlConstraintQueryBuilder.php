<?php declare(strict_types=1);

namespace Framework\Database;

trait MysqlConstraintQueryBuilder
{
    protected function applyConstraints(Constraint $constraint, string $sql): string
    {
        if ($constraint->filter() && count($constraint->filter())) {
            $conditions = [];
            $i = 0;
            foreach ($constraint->filter() as $field => $value) {
                $conditions[] = '`' . $field . '`= :value' . $i;
                $params['value' . $i] = $value;
                $i++;
            }

            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        if ($constraint->sort()) {
            $sortFields = [];
            foreach ($constraint->sort() as $field => $order) {
                $sortFields[] = '`' . $field . '` ' . ($order === -1 ? 'DESC' : 'ASC');
            }

            $sql .= ' ORDER BY ' . implode(', ', $sortFields);
        }

        if ($constraint->limit()) {
            $sql.= ' LIMIT ' . $constraint->limit();

            if ($constraint->skip()) {
                $sql.= ' OFFSET ' . $constraint->skip();
            }
        }

        return $sql;
    }
}