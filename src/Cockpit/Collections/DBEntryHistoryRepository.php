<?php declare(strict_types=1);

namespace Cockpit\Collections;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;

final class DBEntryHistoryRepository
{
    /** @var Connection */
    private $db;
    /** @var MySQLCollectionTableManager */
    private $tableManager;
    /** @var EntriesRepository */
    private $entries;

    public function __construct(Connection $db, MySQLCollectionTableManager $tableManager, EntriesRepository $entries)
    {
        $this->db = $db;
        $this->tableManager = $tableManager;
        $this->entries = $entries;
    }

    public function addRevision(Collection $collection, Entry $entry)
    {
        $entries = $this->entries->revisionsById($collection, $entry->id());

        $history = new EntryHistory($entries);
        $history->addRevision($entry);
        $this->save($collection, $history);
    }

    public function save(Collection $collection, EntryHistory $history)
    {
        foreach ($history->entries() as $entry) {
            $sql = 'UPDATE ' . $this->tableManager->tableName($collection->name()) . ' SET rev_id=:revID, prev_rev_id=:prevRevID WHERE id=:id';
            $params = [
                'id' => $entry->id(),
                'revID' => $entry->revisionID(),
                'prevRevID' => $entry->previousRevisionID()
            ];

            $this->db->executeUpdate($sql, $params, ['type' => ParameterType::STRING, 'revID' => ParameterType::STRING, 'prevRevID' => ParameterType::STRING]);
        }
    }
}
