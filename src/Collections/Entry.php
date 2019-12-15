<?php declare(strict_types=1);

namespace Cockpit\Collections;

use Cockpit\App\ContentUnit;

final class Entry implements ContentUnit
{
    /** @var string */
    private $id;
    /** @var array */
    private $data;

    public function __construct(string $id, array $data)
    {
        $this->id = $id;
        $this->data = $data;
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

    public function toFrontendArray(): array
    {
        $data = [
            '_id' => $this->id
        ];

        return array_merge($data, $this->data);
    }

    public function toArray(): array
    {
        return $this->toFrontendArray();
    }
}
