<?php declare(strict_types=1);

namespace Cockpit\Collections;

final class Entry
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
}
