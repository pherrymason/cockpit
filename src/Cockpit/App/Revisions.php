<?php
/**
 * This file is part of the Cockpit project.
 *
 * (c) Artur Heinze - 🅰🅶🅴🅽🆃🅴🅹🅾, http://agentejo.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cockpit\App;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Cockpit\Framework\IDs;

class Revisions
{
    /** @var Connection */
    protected $db;

    const TABLE = 'cockpit_revisions';

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function count($id) {
        $sql = 'SELECT COUNT(_id) FROM '.self::TABLE.' WHERE _oid=:id';
        $stmt = $this->db->executeQuery($sql, ['_oid'=>$id]);

        return $stmt->fetch()['COUNT(_id)'];
    }

    public function getList($id)
    {
        $sql = 'SELECT * FROM '.self::TABLE.' WHERE _oid=:_oid ORDER BY `_created` DESC';
        $stmt = $this->db->executeQuery($sql, ['_oid' => $id], ['_oid' => ParameterType::STRING]);

        return $stmt->fetchAll();
//        return $this->storage->find('cockpit/revisions', [
//            'filter' => ['_oid' => $id],
//            'sort'   => ['_created' => -1]
//        ])->toArray();
    }

    public function add(ContentUnit $content, $creatorID, $meta = null)
    {
        $entry = [
            '_id' => IDs::new(),
            '_oid' => $content->id(),
            'data' => json_encode($content->toArray()),
            'meta' => $meta,
            '_creator' => $creatorID,
            '_created' => microtime(true)
        ];

        $sql = 'INSERT INTO cockpit_revisions SET `_id`=:_id, `_oid`=:_oid, `data`=:data, `meta`=:meta, `_creator`=:_creator, `_created`=:_created';

        $this->db->executeUpdate($sql, $entry, [
            '_id' => ParameterType::STRING,
            '_oid' => ParameterType::STRING,
            'data' => ParameterType::STRING,
            'meta' => ParameterType::STRING,
            '_creator' => ParameterType::STRING,
            '_created' => ParameterType::STRING
        ]);
    }

    public function get($id) {
        return $this->storage->findOne('cockpit/revisions', ['_oid' => $id]);
    }

    public function remove($rid) {
        return $this->storage->remove('cockpit/revisions', ['_id' => $rid]);
    }

    public function removeAll($id) {
        return $this->storage->remove('cockpit/revisions', ['_oid' => $id]);
    }
}
