<?php declare(strict_types=1);

namespace Cockpit\App\Assets;

final class Asset
{
    /** @var string */
    private $id;
    /** @var string */
    private $filename;
    /** @var string */
    private $title;
    /** @var string */
    private $description;
    /** @var string */
    private $mime;
    /** @var string[] */
    private $tags;
    /** @var string */
    private $size;
    /** @var \DateTimeImmutable */
    private $created;
    /** @var \DateTimeImmutable */
    private $modified;
    /** @var string */
    private $userID;
    /** @var Folder */
    private $folder;
    private $author;
    private $type;

    public function __construct(
        string $id, Folder $folder, string $filename, string $title, string $description, array $tags, string $size, string $mime, \DateTimeImmutable $created, \DateTimeImmutable $modified, string $userID, $width, $height, Author $author, $type, array $colors = [])
    {
        $this->folder = $folder;
        $this->filename = $filename;
        $this->title = $title;
        $this->description = $description;
        $this->tags = $tags;
        $this->size = $size;
        $this->created = $created;
        $this->modified = $modified;
        $this->userID = $userID;
        $this->id = $id;
        $this->mime = $mime;
        $this->width = $width;
        $this->height = $height;
        $this->colors = $colors;
        $this->author = $author;
        $this->type = $type;
    }

    public static function fromFrontendArray($data, Folder $folder): Asset
    {
        $filename = explode('/', $data['path']);
        return new self(
            $data['_id'],
            $folder,
            end($filename),
            $data['title'],
            $data['description'],
            $data['tags'],
            $data['size'],
            $data['mime'],
            new \DateTimeImmutable($data['created']),
            $data['modified'],
            $data['_by']['_id'],
            $data['width'],
            $data['height'],
            new Author($data['_by']['_id'], $data['_by']['name'], $data['_by']['email']),
            $data['type'],
            $data['colors'] ?? []
        );
    }

    public function id(): string
    {
        return $this->id;
    }

    public function folder(): Folder
    {
        return $this->folder;
    }

    public function folderId(): string
    {
        return $this->folder->id();
    }

    public function filename(): string
    {
        return $this->filename;
    }

    public function path(): string
    {
        return $this->filename;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function tags(): array
    {
        return $this->tags;
    }

    public function size(): string
    {
        return $this->size;
    }

    public function mime(): string
    {
        return $this->mime;
    }

    public function created(): \DateTimeImmutable
    {
        return $this->created;
    }

    public function modified(): \DateTimeImmutable
    {
        return $this->modified;
    }

    public function userID(): string
    {
        return $this->userID;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function isImage(): bool
    {
        return preg_match('/\.(jpg|jpeg|png|gif|svg)$/i', $this->filename) ? true:false;
    }

    public function isVideo(): bool
    {
        return preg_match('/\.(mp4|mov|ogv|webv|wmv|flv|avi)$/i', $this->filename) ? true:false;
    }

    public function isAudio(): bool
    {
        return preg_match('/\.(mp3|weba|ogg|wav|flac)$/i', $this->filename) ? true:false;
    }

    public function isArchive(): bool
    {
        return preg_match('/\.(zip|rar|7zip|gz|tar)$/i', $this->filename) ? true:false;
    }

    public function isDocument(): bool
    {
        return preg_match('/\.(txt|htm|html|pdf|md)$/i', $this->filename) ? true:false;
    }

    public function isCode(): bool
    {
        return preg_match('/\.(htm|html|php|css|less|js|json|md|markdown|yaml|xml|htaccess)$/i', $this->filename) ? true:false;
    }

    public function width()
    {
        return $this->width;
    }

    public function height()
    {
        return $this->height;
    }

    public function colors()
    {
        return $this->colors;
    }

    public function authorId(): string
    {
        return $this->author->id();
    }

    public function authorName(): string
    {
        return $this->author->name();
    }

    public function authorEmail(): string
    {
        return $this->author->email();
    }

    public function toArray(): array
    {
        return [
            '_id' => $this->id,
            'path' => $this->filename,
            'title' => $this->title,
            'mime' => $this->mime(),
            'description' => '',
            'tags' => [],
            'size' => $this->size,
            'image' => $this->isImage(),
            'video' => $this->isVideo(),
            'audio' => $this->isAudio(),
            'archive' => $this->isArchive(),
            'document' => $this->isDocument(),
            'code' => $this->isCode(),
            'created' => $this->created->getTimestamp(),
            'modified' => $this->modified->getTimestamp(),
            '_by' => $this->userID
        ];
    }
}
