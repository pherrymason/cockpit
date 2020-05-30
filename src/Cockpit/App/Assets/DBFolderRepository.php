<?php declare(strict_types=1);

namespace Cockpit\App\Assets;

use Cockpit\Framework\Database\Constraint;
use Cockpit\Framework\Database\MysqlConstraintQueryBuilder;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;

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

    public function byID(string $folderID): ?Folder
    {
        $result = $this->db->executeQuery('SELECT * FROM '.self::TABLE.' WHERE _id=:id', ['id' => $folderID])->fetch();

        if (!$result) {
            return null;
        }

        return $this->createFolderFromDatabaseResult($result);
    }

    public function createFolderFromDatabaseResult(array $dbResult): Folder
    {
        $parentFolder = null;
        if ($dbResult['_p']) {
            $parentFolder = $this->byID($dbResult['_p']);
        }

        return new Folder($dbResult['_id'], $dbResult['name'], $parentFolder);
    }

    public function children(Constraint $constraint, ?string $parentID = null)
    {
        $sql = 'SELECT * FROM '.self::TABLE . ' ';

        $constraint->addFilter('_p', empty($parentID) ? null : $parentID);

        list($sql, $params) = $this->applyConstraints($constraint, $sql);

        return $this->db->executeQuery($sql, $params)->fetchAll();
    }

    public function save(Folder $folder): void
    {
        $params = [
            '_id' => $folder->id(),
            '_p' => ($folder->parentFolder() !== null) ? $folder->parentFolder()->id() : null,
            'name' => $folder->name(),
            'path' => $folder->path()
        ];

        $types = array_map(function ($key) {
            return ParameterType::STRING;
        }, array_keys($params));

        $placeholders = array_map(function ($key) {
            return ':'.$key;
        }, array_keys($params));

        $sql = 'INSERT INTO `' . self::TABLE . '` (`_id`, `_p`, `name`, `path`) VALUES (' . implode(', ', $placeholders) . ')';
        $this->db->executeUpdate($sql, $params, $types);
    }
}
