<?php declare(strict_types=1);

namespace Cockpit\App\Revisions;

use Doctrine\DBAL\Connection;

final class RevisionsRepository
{
    const TABLE = 'cockpit_revisions';

    /** @var Connection */
    private $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function count($id): int
    {
        $stmt = $this->db->executeQuery('SELECT COUNT(_id) FROM ' . self::TABLE . ' WHERE _oid=:id', ['id' => $id]);

        return $stmt->rowCount() === 0 ? 0 : (int)($stmt->fetch()['COUNT(_id)']);
    }
}
