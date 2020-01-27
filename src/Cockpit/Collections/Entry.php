<?php declare(strict_types=1);

namespace Cockpit\Collections;

use Cockpit\App\ContentUnit;
use Framework\IDs;

final class Entry implements ContentUnit
{
    /** @var string */
    private $id;
    /** @var array */
    private $data;
    /** @var array */
    private $localizedData;


    public function __construct(string $id, array $data)
    {
        $this->id = $id;
        $this->localizedData = $data['localized'];
        unset($data['localized']);
        $this->data = $data;
    }

    public static function create(): self
    {
        return new Entry(IDs::new(), []);
    }

    public function id(): string
    {
        return $this->id;
    }

/*    public function freezeRevision(Entry $newEntry)
    {
        $this->revisionID = IDs::new();
        $newEntry->setPreviousRevision($this);
    }

    public function setPreviousRevision(Entry $previousRevision)
    {
        $this->previousRevisionID = $previousRevision->revisionID();
    }
*/
    public function hasAccess(): bool
    {
        return true;
    }

    public function toArray(): array
    {
        $data = [
            '_id' => $this->id
        ];

        $data = array_merge($data, $this->data);

        if (!is_numeric($data['_created'])) {
            $created = new \DateTimeImmutable($data['_created']);
            $data['_created'] = $created->getTimestamp();
        }

        if (!is_numeric($data['_modified'])) {
            $modified = new \DateTimeImmutable($data['_modified']);
            $data['_modified'] = $modified->getTimestamp();
        }

        foreach ($this->localizedData as $langCode => $fields) {
            foreach ($fields as $fieldName => $value ) {
                $data[$fieldName.'_'.$langCode] = $value;
            }
        }

        return $data;
    }
}
