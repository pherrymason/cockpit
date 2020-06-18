<?php declare(strict_types=1);

namespace Cockpit\Collections;

use Cockpit\App\ContentUnit;
use Cockpit\Framework\IDs;

final class Entry implements ContentUnit
{
    /** @var string */
    private $id;
    /** @var array */
    private $data;
    /** @var array */
    private $localizedData;
    /** @var Field[] */
    private $fields;

    public function __construct(string $id, array $data, array $fields)
    {
        $this->id = $id;
        $this->localizedData = $data['localized'];
        unset($data['localized']);
        $this->data = $data;
        $this->fields = [];
        /** @var Field[] $fields */
        foreach ($fields as $field) {
            $this->fields[$field->name()] = $field;
        }
    }

    public static function create(): self
    {
        return new Entry(IDs::new(), [], []);
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

        foreach ($this->data as $fieldName => $value) {
            if (!isset($this->fields[$fieldName])) {
                $data[$fieldName] = $value;
                continue;
            }

            switch ($this->fields[$fieldName]->type()) {
                case Field::TYPE_ASSET:
                    $data[$fieldName] = !empty($value) ? json_decode($value, true) : null;
                    break;

                default:
                    $data[$fieldName] = $value;
                    break;
            }
        }

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
                if (!isset($this->fields[$fieldName])) {
                    $data[$fieldName.'_'.$langCode] = $value;
                    continue;
                }

                switch ($this->fields[$fieldName]->type()) {
                    case Field::TYPE_ASSET:
                        $data[$fieldName.'_'.$langCode] = json_decode($value, true);
                        break;

                    case FIeld::TYPE_BOOLEAN:
                        $data[$fieldName.'_'.$langCode] = (bool)$value;
                        break;

                    default:
                        $data[$fieldName.'_'.$langCode] = $value;
                        break;
                }
            }
        }

        return $data;
    }
}
