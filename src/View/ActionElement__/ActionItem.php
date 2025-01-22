<?php

declare(strict_types=1);

namespace BootstrapTools\View\ActionElement;

use BootstrapTools\Utility\Color;
use BootstrapTools\View\ActionElement\ActionElementInterface;
use Cake\Utility\Hash;

class ActionItem
{







    
    const Default = 'default';
    const Index = 'index';
    const Add = 'add';
    const Edit = 'edit';
    const Delete = 'delete';
    const View = 'view';
    const LimitControl = 'limit_control';
    const Submit = 'submit';
    const Cancel = 'cancel';
    const CloseModal = 'close_modal';
    const AjaxSubmit = 'ajax_submit';

    protected static array $registry = [];

    private array $options;
    private array|callable $item;
    private string $template;

    private function __construct(callable $item = [], string $template = null) {
        $this->$item = $item;
        $this->template = $template;
    }

    public function withOptions(array $options = []): self
    {
        $this->options = Hash::merge($this->options, $options);

        return $this;
    }

    public static function get(string $name, array $options = [], string $template = null): self
    {
        $template = self::defaults($name) ?? self::defaults($template);

        if (!isset(self::$registry[$name])) {
             return new self($options, $template);
        }
        
    }

    public static function registry(string $name, array|callable $options = [], string $template = null): self
    {
        if (!isset(self::$registry[$name])) {
            self::$registry[$name] = new self($options, $template);
        }

        return self::$registry[$name];
    }

    protected static function defaults(string $name): callable
    {
        return match ($name) {
            self::Index => function (array $options = []): array {
                return [
                    'type' => ActionElement::TYPE_LINK,
                    'label' => __('List'),
                    'url' => ['action' => 'index'],
                    'icon' => 'list',
                    'color' => Color::LIGHT,
                ];
            },

            self::Add => function (array $options = []) {
                return [
                    'type' => ActionElement::TYPE_LINK,
                    'label' => __('Add'),
                    'url' => ['action' => 'add'],
                    'icon' => 'plus',
                    'color' => Color::SUCCESS,
                ];
            },

            self::Edit => function (array $options = []) {
                return [
                    'type' => ActionElement::TYPE_LINK,
                    'label' => __('Edit'),
                    'url' => array_filter(['action' => 'edit', $options['id'] ?? null]),
                    'icon' => 'pencil',
                    'color' => Color::WARNING,
                ];
            },

            self::View => function (array $options = []) {
                return [
                    'type' => ActionElement::TYPE_LINK,
                    'label' => __('View'),
                    'url' => array_filter(['action' => 'view', $options['id'] ?? null]),
                    'icon' => 'eye',
                    'color' => Color::INFO,
                ];
            },

            self::Delete => function (array $options = []) {
                return [
                    'type' => ActionElement::TYPE_POSTLINK,
                    'label' => __('Delete'),
                    'url' => array_filter(['action' => 'delete', $options['id'] ?? null]),
                    'icon' => 'trash',
                    'color' => Color::DANGER,
                    'confirm' => __('Are you sure you want to delete this record?'),
                ];
            },

            self::LimitControl => function (array $options = []) {
                return [
                    'type' => ActionElement::TYPE_LIMIT_CONTROL,
                    'limits' => [],
                    'default' => null,
                    'options' => [
                        'label' => false,
                        'spacing' => 'mb-0',
                    ],
                ];
            },

            self::Submit => function (array $options = []) {
                return [
                    'type' => ActionElement::TYPE_SUBMIT,
                    'label' => __('Submit'),
                    'color' => Color::PRIMARY,
                ];
            },

            self::Cancel => function (array $options = []) {
                return [
                    'type' => ActionElement::TYPE_LINK,
                    'label' => __('Cancel'),
                    'url' => ['action' => 'index'],
                    'color' => Color::SECONDARY,
                ];
            },

            self::CloseModal => function (array $options = []) {
                return [
                    'type' => ActionElement::TYPE_BUTTON,
                    'label' => __('Close'),
                    'color' => Color::SECONDARY,
                    'data-bs-dismiss' => 'modal',
                ];
            },

            self::AjaxSubmit => function (array $options = []) {
                return [
                    'type' => ActionElement::TYPE_AJAX_SUBMIT,
                    'label' => __('Submit'),
                    'color' => Color::PRIMARY,
                ];
            },

            self::Default => function (array $options = []) {
                return [
                    'type' => ActionElement::TYPE_BUTTON,
                    'label' => __('Action'),
                    'color' => Color::SECONDARY,
                ];
            },
        };
    }
}
