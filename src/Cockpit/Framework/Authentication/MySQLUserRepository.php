<?php

namespace Cockpit\Framework\Authentication;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Mezzio\Authentication\DefaultUserFactory;
use Mezzio\Authentication\UserInterface;
use Mezzio\Authentication\UserRepositoryInterface;
use Mezzio\Authentication\Exception;

final class MySQLUserRepository implements UserRepositoryInterface
{
    const TABLE = 'cockpit_accounts';

    /** @var Connection */
    private $connection;
    /** @var */
    private $userFactory;

    public function __construct(Connection $connection, $userFactory)
    {
        $this->connection = $connection;
        $this->userFactory = $userFactory;
    }

    public function authenticate(string $credential, string $password = null): ?UserInterface
    {
        $sql = sprintf(
            "SELECT %s FROM %s WHERE %s = :identity",
            'password',
            self::TABLE,
            'user'
        );

        $stmt = $this->connection->prepare($sql);
        if (false === $stmt) {
            throw new Exception\RuntimeException(
                'An error occurred when preparing to fetch user details from ' .
                'the repository; please verify your configuration'
            );
        }
        $stmt->bindParam(':identity', $credential);
        $stmt->execute();

        $result = $stmt->fetch();
        if (!$result) {
            return null;
        }

        if (password_verify($password ?? '', $result['password'] ?? '')) {
            return ($this->userFactory)(
                $credential,
                $this->getUserRoles($credential),
                $this->getUserDetails($credential)
            );
        }
        return null;
    }

    /**
     * Get the user roles if present.
     *
     * @param string $identity
     * @return string[]
     */
    protected function getUserRoles(string $identity): array
    {
        try {
            $sql = 'SELECT * FROM ' . self::TABLE . ' WHERE `user`=:user LIMIT 1';
            $result = $this->connection->executeQuery($sql, ['user' => $identity])->fetch();
        } catch (DBALException $e) {
            throw new Exception\RuntimeException(
                sprintf(
                    'Error preparing retrieval of user details: %s',
                    $e->getMessage()
                )
            );
        }

        if (false === $result) {
            throw new Exception\RuntimeException(
                sprintf(
                    'Error preparing retrieval of user roles: unknown error'
                )
            );
        }

        return [$result['group']];
    }

    /**
     * Get the user details if present.
     *
     * @param string $identity
     * @return string[]
     */
    protected function getUserDetails(string $identity): array
    {
        try {
            $sql = 'SELECT * FROM ' . self::TABLE . ' WHERE `user`=:user LIMIT 1';
            $result = $this->connection->executeQuery($sql, ['user' => $identity])->fetch();
        } catch (DBALException $e) {
            throw new Exception\RuntimeException(
                sprintf(
                    'Error preparing retrieval of user details: %s',
                    $e->getMessage()
                )
            );
        }

        if ($result === false) {
            throw new Exception\RuntimeException(
                sprintf(
                    'Error preparing retrieval of user details: unknown error'
                )
            );
        }

        return [
            'id' => $result['_id'],
            'user' => $result['user'],
            'name' => $result['name'],
            'i18n' => $result['i18n']
        ];
    }
}
