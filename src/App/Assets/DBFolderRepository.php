<?php declare(strict_types=1);

namespace Cockpit\App\Assets;

use Cockpit\Framework\Database\Constraint;
use Cockpit\Framework\Database\MysqlConstraintQueryBuilder;
use Doctrine\DBAL\Connection;

final class DBFolderRepository implements FolderRepository
{
    use MysqlConstraintQueryBuilder;
    const TABLE = 'cockpit_assets_folders';

    /** @var Connection */
    private $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function all(Constraint $constraint)
    {
        $sql = 'SELECT * FROM '.self::TABLE . ' ';
        $sql = $this->applyConstraints($constraint, $sql);

        return $this->db->query($sql)->fetchAll();
    }
}
