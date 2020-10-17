<?php

namespace Cockpit\User;

use Cockpit\Framework\Authentication\User;
use Cockpit\Framework\Authentication\UserFactory;
use Doctrine\DBAL\Connection;

class MySqlUserRepository implements UserRepository
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function byId($id): ?User
    {
        $sql = 'SELECT * FROM cockpit_accounts WHERE _id=:id';
        $result = $this->connection->executeQuery($sql, ['id' => $id])->fetch();

        if ($result === false) {
            return null;
        }

        return $this->createUser($result);
    }

    public function byUser(string $user): ?User
    {
        $sql = 'SELECT * FROM cockpit_accounts WHERE `user`=:user';
        $result = $this->connection->executeQuery($sql, ['user' => $user])->fetch();

        if ($result === false) {
            return null;
        }

        return $this->createUser($result);
    }

    public function byEmail(string $email): ?User
    {
        $sql = 'SELECT * FROM cockpit_accounts WHERE `email`=:email';
        $result = $this->connection->executeQuery($sql, ['email' => $email])->fetch();

        if ($result === false) {
            return null;
        }

        return $this->createUser($result);
    }

    public function save(array $data)
    {
        $fields = ['user','email','name','active','i18n'];

        $params = [
            '_id' => $data['_id'],
            'user' => $data['user'],
            'name' => $data['name'],
            'email' => $data['email'],
            'active' => $data['active'],
            'i18n' => $data['i18n']
        ];

        if (isset($data['password'])) {
            $fields[] = 'password';
            $params['password'] = $data['password'];
        }

        $sql = 'INSERT INTO cockpit_accounts SET
        _id=:_id';
        foreach ($fields as $field) {
            $sql.= ', `'.$field.'`=:'.$field;
        }
        $sql.= ', _created=now()
         ON DUPLICATE KEY UPDATE ';

        $toUpdate = [];
        foreach ($fields as $field) {
            $toUpdate[] = '`'.$field.'`=:'.$field;
        }
        $sql.= implode(', ', $toUpdate) . ', _modified=NOW()';


        $this->connection->executeUpdate(
            $sql,
            $params
        );
    }

    private function createUser(array $result): User
    {
        $result['id'] = $result['_id'];
        unset($result['_id']);

        return UserFactory::create($result['id'], [$result['group']], $result);
    }
}