<?php declare(strict_types=1);

namespace Cockpit\Singleton;

use Cockpit\Collections\Field;
use DateTimeImmutable;
use Framework\IDs;

final class Singleton
{
    /** @var string */
    private $id;
    /** @var string */
    private $name;
    /** @var string|null */
    private $label;
    /** @var string|null */
    private $description;
    /** @var Field[] */
    private $fields;
    /** @var string|null */
    private $template;
    /** @var DateTimeImmutable */
    private $created;
    /** @var DateTimeImmutable|null */
    private $modified;

    public static function create(string $id, string $name, ?string $label, ?string $description, array $fields, ?string $template)
    {
        $fields = array_map([Field::class, 'fromArray'], $fields);

        return new self($id, $name, $label, $description, $fields, $template, new \DateTimeImmutable(), null);
    }

    public function __construct(string $id, string $name, ?string $label, ?string $description, array $fields, ?string $template, DateTimeImmutable $created, ?DateTimeImmutable $modified)
    {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->fields = $fields;
        $this->template = $template;
        $this->label = $label;
        $this->created = $created;
        $this->modified = $modified;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function label(): ?string
    {
        return $this->label;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function fields(): array
    {
        return $this->fields;
    }

    public function template(): ?string
    {
        return $this->template;
    }

    public function created(): DateTimeImmutable
    {
        return $this->created;
    }

    public function modified(): ?DateTimeImmutable
    {
        return $this->modified;
    }

    public function toArray(): array
    {
        return [
            '_id' => $this->id,
            'name' => $this->name,
            'label' => $this->label,
            'description' => $this->description,
            'fields' => array_map(function (Field $field) {
                return $field->toArray();
            }, $this->fields),
            'template' => $this->template,
            'data' => null
        ];
    }
}
