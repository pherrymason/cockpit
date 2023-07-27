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
    /** @var string */
    private $assetsBasePath;

    public function __construct(Connection $db, string $assetsBasePath)
    {
        $this->db = $db;
        $this->assetsBasePath = $assetsBasePath;
    }

    public function byId(string $assetID): ?array
    {
        $sql = 'SELECT * FROM '.self::TABLE.' WHERE _id=:id';
        $stmt = $this->db->executeQuery($sql, ['id' => $assetID]);

        $result = $stmt->fetch();

        return $result !== false ? $result : null;
    }

    public function byConstraint(Constraint $constraints)
    {
        $sql = 'SELECT assets.*, ca._id as userId, ca.name as userName, ca.email as userEmail FROM cockpit_assets assets ';
        $sql.= ' JOIN cockpit_accounts ca ON ca._id=assets._by ';
        list($sql, $params) = $this->applyConstraints($constraints, $sql);

        $stmt = $this->db->executeQuery($sql, $params);
        if ($constraints->limit() || $constraints->skip()) {
            $total = $this->countAllByConstraint($constraints);
        } else {
            $total = $stmt->rowCount();
        }

        $assets = $stmt->fetchAll();

        return [
            'assets' => $assets,
            'total' => $total
        ];
    }

    public function countAll(): int
    {
        $sql = 'SELECT COUNT(_id) as id FROM '.self::TABLE;

        $data = $this->db->query($sql)->fetch();

        return (int)$data['id'];
    }

    public function countAllByConstraint(Constraint $constraints): int
    {
        $sql = 'SELECT COUNT(_id) as id FROM '.self::TABLE;
        $newConstraint = new Constraint($constraints->filter());
        list($sql, $params) = $this->applyConstraints($newConstraint, $sql);

        $stmt = $this->db->executeQuery($sql, $params);
        $data = $stmt->fetch();

        return (int)$data['id'];
    }

    public function save(Asset $asset, string $folderID = null)
    {
        $params = [
            '_id' => $asset->id(),
            'folder' => $asset->folder()->id(),
            'path' => $this->assetsBasePath . $asset->folder()->path().'/'.$asset->filename(),
            'title' => $asset->title(),
            'mime' => $asset->mime(),
            'description' => $asset->description(),
            'tags' => json_encode($asset->tags()),
            'size' => $asset->size(),
            'image' => (int)$asset->isImage(),
            'video' => (int)$asset->isVideo(),
            'audio' => (int)$asset->isAudio(),
            'archive' => (int)$asset->isArchive(),
            'document' => (int)$asset->isDocument(),
            'code' => (int)$asset->isCode(),
            'created' => $asset->created()->format('Y-m-d H:i:s'),
            'modified' => $asset->modified()->format('Y-m-d H:i:s'),
            'width' => $asset->width(),
            'height' => $asset->height(),
            'colors' => json_encode($asset->colors()),
            '_by' => $asset->userID()
        ];

        $types = array_map(function ($key) {
            return ParameterType::STRING;
        }, array_keys($params));

        $sql = <<<SQL
            INSERT INTO cockpit_assets SET 
                _id=:_id,
                _by=:_by,
                folder=:folder,
                path=:path,
                title=:title,
                mime=:mime,
                description=:description,
                tags=:tags,
                size=:size,
                image=:image,
                video=:video,
                audio=:audio,
                archive=:archive,
                document=:document,
                code=:code,
                width=:width,
                height=:height,
                colors=:colors,
                created=:created,
                modified=:modified
            ON DUPLICATE KEY UPDATE
                _by=:_by,
                folder=:folder,
                path=:path,
                title=:title,
                mime=:mime,
                description=:description,
                tags=:tags,
                size=:size,
                image=:image,
                video=:video,
                audio=:audio,
                archive=:archive,
                document=:document,
                code=:code,
                width=:width,
                height=:height,
                colors=:colors,
                created=:created,
                modified=:modified
        SQL;

        $this->db->executeUpdate($sql, $params, $types);
    }

    public function delete(string $assetID)
    {
        $this->db->executeUpdate('DELETE FROM '.self::TABLE.' WHERE _id=:id LIMIT 1', ['id' => $assetID]);
    }
}
