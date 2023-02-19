<?php


namespace Codegenhub\App\Model;


class MappingOptions
{
    const TYPE_NEW_FILE = 'new_file';
    const TYPE_TEXT = 'text';
    const TYPE_JSON = 'json';
    const TYPE_SECONDARY = 'secondary';

    private const SUPPORTED_TYPES = [
        self::TYPE_NEW_FILE,
        self::TYPE_TEXT,
        self::TYPE_JSON,
        self::TYPE_SECONDARY,
    ];

    /** @var array */
    private $options;

    public function __construct($options)
    {
        $this->options = $options;
        $this->validate();
    }

    public function getFileNameTemplate(): ?string
    {
        return $this->options['file_name_template'] ?? null;
    }

    public function getTemplate(): ?string
    {
        return $this->options['template'] ?? null;
    }

    public function getJsonPath(): ?string
    {
        return $this->options['json_path'] ?? null;
    }

    public function getRelativePath(): ?string
    {
        return $this->options['relative_path'] ?? null;
    }

    public function getSkipCondition(): ?string
    {
        return $this->options['skip_condition'] ?? null;
    }

    public function getTrimNewLines(): bool
    {
        return (bool)($this->options['trim_new_lines'] ?? false);
    }

    public function getTrim(): bool
    {
        return (bool)($this->options['trim'] ?? false);
    }

    public function getRender(): bool
    {
        return (bool)($this->options['render'] ?? true);
    }

    public function getAssoc(): bool
    {
        return (bool)($this->options['assoc'] ?? false);
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getType(): string
    {
        $type = $this->options['type'] ?? self::TYPE_TEXT;
        if (!in_array($type, static::SUPPORTED_TYPES)) {
            throw new \Exception(sprintf('Mapping type %s is not supported', $type));
        }

        return $type;
    }

    private function validate()
    {
    }
}
