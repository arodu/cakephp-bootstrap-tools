<?php
declare(strict_types=1);

namespace BootstrapTools\View\ActionElement;

/**
 * @method static ActionElementOptions link(array $options = [])
 * @method static ActionElementOptions buttonLink(array $options = [])
 * @method static ActionElementOptions postlink(array $options = [])
 * @method static ActionElementOptions limitControl(array $options = [])
 * @method static ActionElementOptions submit(array $options = [])
 * @method static ActionElementOptions button(array $options = [])
 * @method static ActionElementOptions reset(array $options = [])
 * @method static ActionElementOptions ajaxSubmit(array $options = [])
 */
class ActionElementOptions
{
    public const TYPE_LINK = 'link';
    public const TYPE_BUTTON_LINK = 'button_link';
    public const TYPE_POSTLINK = 'postlink';
    public const TYPE_LIMIT_CONTROL = 'limit_control';
    public const TYPE_SUBMIT = 'submit';
    public const TYPE_BUTTON = 'button';
    public const TYPE_RESET = 'reset';
    public const TYPE_AJAX_SUBMIT = 'ajax_submit';

    private string $type;
    private array $options;

    public function __construct(string $type, array $options = [])
    {
        $this->type = $type;
        $this->options = $options;
    }


    public function getType(): string
    {
        return $this->type;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function toArray(): array
    {
        return [
            'type' => $this->getType(),
            'options' => $this->getOptions(),
        ];
    }

}