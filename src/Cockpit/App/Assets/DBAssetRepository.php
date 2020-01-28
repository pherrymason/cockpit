<?php declare(strict_types=1);

namespace Cockpit\App\Assets;

use Cockpit\Framework\Database\Constraint;
use Cockpit\Framework\Database\MysqlConstraintQueryBuilder;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use function Framework\Database\MongoLite\MongoLite\array_key_intersect;

final class DBAssetRepository implements AssetRepository
{
    use MysqlConstraintQueryBuilder;

    /** @var Connection */
    private $db;

    const TABLE = 'cockpit_assets';

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function byId(string $assetID)
    {
        $sql = 'SELECT * FROM '.self::TABLE.' WHERE _id=:id';
        $stmt = $this->db->executeQuery($sql, ['id' => $assetID]);

        return $stmt->fetch();
    }

    public function byConstraint(Constraint $constraints)
    {
        $sql = 'SELECT * FROM '.self::TABLE.' ';
        $sql = $this->applyConstraints($constraints, $sql);

        $stmt = $this->db->query($sql);
        $total = (!$constraints->skip() && !$constraints->limit())
            ? $stmt->rowCount()
            : $this->countAll();

        $assets = $stmt->fetchAll();

        return [
            'assets' => $assets,
            'total' => $total
        ];
    }

    public function countAll(): int
    {
        $sql = 'SELECT COUNT(_id) as id FROM '.self::TABLE;

        return $this->db->query($sql)->rowCount();
    }

    public function save(Asset $asset)
    {
        $params = [
            '_id' => $asset->id(),
            'path' => $asset->path(),
            'title' => $asset->title(),
            'mime' => mime_content_type($asset->path()),
            'description' => $asset->description(),
            'tags' => json_encode($asset->tags()),
            'size' => $asset->size(),
            'image' => $asset->isImage(),
            'video' => $asset->isVideo(),
            'audio' => $asset->isAudio(),
            'archive' => $asset->isArchive(),
            'document' => $asset->isDocument(),
            'code' => $asset->isCode(),
            'created' => $asset->created()->format('Y-m-d H:i:s'),
            'modified' => $asset->modified()->format('Y-m-d H:i:s'),
            '_by' => $asset->userID()
        ];

        $types = array_map(function ($key) {
            return ParameterType::STRING;
        }, array_keys($params));

        $fieldNames = array_map(function ($key) {
            return '`' . $key . '`';
        }, array_keys($params));

        $placeholders = array_map(function ($key) {
            return ':'.$key;
        }, array_keys($params));

        $sql = 'INSERT INTO '.self::TABLE.' ('. implode(', ', $fieldNames) .') ' .
                'VALUES (' . implode(', ', $placeholders) . ')';

        $this->db->executeUpdate($sql, $params, $types);
    }
}
