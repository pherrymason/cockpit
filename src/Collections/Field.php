<?php declare(strict_types=1);

namespace Cockpit\Collections;

final class Field
{
    const TYPE_ACCESS_LIST = 'access-list';
    const TYPE_ACCOUNT_LINK = 'account-link';
    const TYPE_ASSET = 'asset';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_CODE = 'code';
    const TYPE_COLLECTIONLINK = 'collectionlink';
    const TYPE_COLOR = 'color';
    const TYPE_COLORTAG = 'colortag';
    const TYPE_DATE = 'date';
    const TYPE_FILE = 'file';
    const TYPE_GALLERY = 'gallery';
    const TYPE_HTML = 'html';
    const TYPE_IMAGE = 'image';
    const TYPE_LAYOUT = 'layout';
    const TYPE_LAYOUT_GRID = 'layout-grid';
    const TYPE_LOCATION = 'location';
    const TYPE_MARKDOWN = 'markdown';
    const TYPE_MULTIPLESELECT= 'multipleselect';
    const TYPE_OBJECT = 'object';
    const TYPE_PASSWORD = 'password';
    const TYPE_RATING = 'rating';
    const TYPE_REPEATER = 'repeater';
    const TYPE_SELECT = 'select';
    const TYPE_SET = 'set';
    const TYPE_TAGS = 'tags';
    const TYPE_TEXT = 'text';
    const TYPE_TEXTAREA = 'textarea';
    const TYPE_TIME = 'time';
    const TYPE_WYSIWYG = 'wysiwyg';


    /** @var string */
    private $name;
    /** @var string */
    private $label;
    /** @var string */
    private $type;
    /** @var string */
    private $default;
    /** @var string */
    private $info;
    /** @var string */
    private $group;
    /** @var bool */
    private $localize;
    /** @var array */
    private $options;
    /** @var string */
    private $width;
    /** @var bool ¿Show on list? */
    private $lst;
    /** @var array */
    private $acl;

    public static function fromArray(array $field)
    {
        return new Field(
            $field['name'],
            $field['label'],
            $field['type'],
            $field['default'] ?? null,
            $field['info'],
            $field['group'],
            $field['localize'],
            $field['options'],
            $field['width'],
            $field['lst'],
            $field['acl']
        );
    }

    public function __construct(string $name, string $label, string $type, ?string $default, string $info, string $group, bool $localize, array $options, string $width, bool $lst, array $acl)
    {
        $this->name = $name;
        $this->label = $label;
        $this->type = $type;
        $this->default = $default;
        $this->info = $info;
        $this->group = $group;
        $this->localize = $localize;
        $this->options = $options;
        $this->width = $width;
        $this->lst = $lst;
        $this->acl = $acl;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function default(): ?string
    {
        return $this->default;
    }

    public function info(): string
    {
        return $this->info;
    }

    public function group(): string
    {
        return $this->group;
    }

    public function localize(): bool
    {
        return $this->localize;
    }

    public function options(): array
    {
        return $this->options;
    }

    public function width(): string
    {
        return $this->width;
    }

    public function lst(): bool
    {
        return $this->lst;
    }

    public function acl(): array
    {
        return $this->acl;
    }

    public function toArray()
    {
        return [
            'name' => $this->name,
            'label' => $this->label,
            'type' => $this->type,
            'info' => $this->info,
            'group' => $this->group,
            'localize' => $this->localize,
            'options' => $this->options,
            'width' => $this->width,
            'lst' => $this->lst,
            'acl' => $this->acl
        ];
    }
}
