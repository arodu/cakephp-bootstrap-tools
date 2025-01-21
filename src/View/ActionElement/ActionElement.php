<?php
declare(strict_types=1);

namespace BootstrapTools\View\ActionElement;

/**
 */
class ActionElement
{
    public const TYPE_LINK = 'link';
    public const TYPE_BUTTON_LINK = 'button_link';
    public const TYPE_POSTLINK = 'postlink';
    public const TYPE_LIMIT_CONTROL = 'limit_control';
    public const TYPE_SUBMIT = 'submit';
    public const TYPE_BUTTON = 'button';
    public const TYPE_RESET = 'reset';
    public const TYPE_AJAX_SUBMIT = 'ajax_submit';

    private array $options;
    private string $type;

    public function __construct(array $options = [])
    {
        $this->options = $options;
        $this->type = $options['type'] ?? self::TYPE_LINK;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getType(): string
    {
        return $this->type;
    }
}