<?php declare(strict_types=1);

namespace Cockpit\App\Assets;

use Cockpit\Framework\IDs;

final class Folder
{
    /** @var string */
    private $id;
    /** @var string */
    private $name;
    /** @var Folder|null */
    private $parentFolder;
    /** @var Folder[] */
    private $childrenFolders;

    public static function create(string $name, ?Folder $parentFolder)
    {
        return new self(IDs::new(), $name, $parentFolder);
    }

    public function __construct(string $id, string $name, ?Folder $parentFolder = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->childrenFolders = [];

        if ($parentFolder !== null) {
            $parentFolder->addChildren($this);
            $this->parentFolder = $parentFolder;
        }
    }

    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function parentFolder(): ?Folder
    {
        return $this->parentFolder;
    }

    public function addChildren(Folder $childrenFolder)
    {
        $childrenFolder->parentFolder = $this;
        $this->childrenFolders[] = $childrenFolder;
    }

    public function children(): array
    {
        return $this->childrenFolders;
    }

    public function path(): string
    {
        if ($this->parentFolder !== null) {
            $path = explode('/', $this->parentFolder->path());
        } else {
            $path = [];
        }

        $path[] = $this->name();

        return '/' . ltrim(implode('/', $path), '/');
    }

    public function rename(string $name): void
    {
        $this->name = $name;
    }

    public function toArray()
    {
        return [
            '_id' => $this->id,
            'name' => $this->name,
            'path' => $this->path()
        ];
    }
}
