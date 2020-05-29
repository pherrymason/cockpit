<?php

namespace Cockpit\Framework\Authentication;

use Doctrine\DBAL\Connection;
use Mezzio\Authentication\UserInterface;
use Mezzio\Authentication\UserRepositoryInterface;
use Mezzio\Authentication\Exception;

final class MySQLUserRepository implements UserRepositoryInterface
{
    const TABLE = 'cockpit_accounts';

    /** @var Connection */
    private $connection;
    /** @var \Mezzio\Authentication\DefaultUserFactory */
    private $userFactory;

    public function __construct(Connection $connection, \Mezzio\Authentication\DefaultUserFactory $userFactory)
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
        if (! $result) {
            return null;
        }

        if (password_verify($password ?? '', $result->{$this->config['field']['password']} ?? '')) {
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
    protected function getUserRoles(string $identity) : array
    {
        if (! isset($this->config['sql_get_roles'])) {
            return [];
        }

        if (false === strpos($this->config['sql_get_roles'], ':identity')) {
            throw new Exception\InvalidConfigException(
                'The sql_get_roles configuration setting must include an :identity parameter'
            );
        }

        try {
            $stmt = $this->pdo->prepare($this->config['sql_get_roles']);
        } catch (PDOException $e) {
            throw new Exception\RuntimeException(sprintf(
                                                     'Error preparing retrieval of user roles: %s',
                                                     $e->getMessage()
                                                 ));
        }
        if (false === $stmt) {
            throw new Exception\RuntimeException(sprintf(
                                                     'Error preparing retrieval of user roles: unknown error'
                                                 ));
        }
        $stmt->bindParam(':identity', $identity);

        if (! $stmt->execute()) {
            return [];
        }

        $roles = [];
        foreach ($stmt->fetchAll(PDO::FETCH_NUM) as $role) {
            $roles[] = $role[0];
        }
        return $roles;
    }

    /**
     * Get the user details if present.
     *
     * @param string $identity
     * @return string[]
     */
    protected function getUserDetails(string $identity) : array
    {
        if (! isset($this->config['sql_get_details'])) {
            return [];
        }

        if (false === strpos($this->config['sql_get_details'], ':identity')) {
            throw new Exception\InvalidConfigException(
                'The sql_get_details configuration setting must include a :identity parameter'
            );
        }

        try {
            $stmt = $this->pdo->prepare($this->config['sql_get_details']);
        } catch (PDOException $e) {
            throw new Exception\RuntimeException(sprintf(
                                                     'Error preparing retrieval of user details: %s',
                                                     $e->getMessage()
                                                 ));
        }
        if (false === $stmt) {
            throw new Exception\RuntimeException(sprintf(
                                                     'Error preparing retrieval of user details: unknown error'
                                                 ));
        }
        $stmt->bindParam(':identity', $identity);

        if (! $stmt->execute()) {
            return [];
        }
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
