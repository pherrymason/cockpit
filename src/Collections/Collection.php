<?php declare(strict_types=1);

namespace Cockpit\Collections;

use Framework\IDs;

final class Collection
{
    /** @var string */
    private $id;
    /** @var string */
    private $name;
    /** @var string */
    private $label;
    /** @var string */
    private $color;
    /** @var mixed */
    private $fields;
    /** @var array */
    private $acl;
    /** @var bool */
    private $sortable;
    /** @var bool */
    private $inMenu;

    public static function create()
    {
        return new self(IDs::new(), '', '', '', [], [], false, false);
    }

    public function __construct(string $id, string $name, string $label, string $color, $fields, array $acl, bool $sortable, bool $inMenu)
    {
        $this->id = $id;
        $this->name = $name;
        $this->label = $label;
        $this->color = $color;
        $this->fields = $fields;
        $this->acl = $acl;
        $this->sortable = $sortable;
        $this->inMenu = $inMenu;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function hasAccess($role): bool
    {
        return true;
    }

    public function toFrontendArray(): array
    {
        $data = [
            '_id' => $this->id,
            'name' => $this->name,
            'label' => $this->label,
            'color' => $this->color,
            'fields' => $this->fields,
            'sortable' => (bool) $this->sortable,
            'in_menu' => (bool) $this->inMenu,
            '_created' => (new \DateTimeImmutable())->getTimestamp(),
            '_updated' => (new \DateTimeImmutable())->getTimestamp(),
            'acl' => [],
            'rules' => [
                'create' => ['enabled' => false],
                'read' => ['enabled' => false],
                'update' => ['enabled' => false],
                'delete' => ['enabled' => false]
            ],
            'meta' => [
                'allowed' => [
                    'delete' => true,
                    'create' => true,
                    'edit' => true,
                    'entries_create' => true,
                    'entries_delete' => true
                ]
            ]
        ];

        return $data;
    }
}
