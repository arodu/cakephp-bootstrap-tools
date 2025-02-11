<?php

declare(strict_types=1);

namespace BootstrapTools\View\ActionItems;

use Cake\Utility\Hash;

class ActionItem implements ActionItemInterface
{
    const DEFAULT = 'default';
    const INDEX = 'index';
    const ADD = 'add';
    const EDIT = 'edit';
    const DELETE = 'delete';
    const VIEW = 'view';
    const LIMIT_CONTROL = 'limit_control';
    const SUBMIT = 'submit';
    const CANCEL = 'cancel';
    const CLOSE_MODAL = 'close_modal';
    const AJAX_SUBMIT = 'ajax_submit';
    const BUTTON = 'button';
    const RESET = 'reset';

    protected static array $registry = [];

    private array $options;

    private function __construct(array $options = [])
    {
        $this->options = $options;
    }

    public function withOptions(array $options = []): ActionItemInterface
    {
        $this->options = Hash::merge($this->options, $options);

        return $this;
    }

    public function toArray(): array
    {
        return $this->options;
    }

    /**
     * Set an action item by name
     * 
     * usage:
     * ```php
     * ActionItem::set('index', ['label' => __('List Projects')]);
     * ```
     * 
     * @param string $name
     * @param array $options
     * @return void
     */
    public static function set(string $name, array $options): void
    {
        $options = Hash::merge(static::$registry[$name] ?? static::defaultOptions($name), $options);

        if (empty($options['type']) || !($options['type'] instanceof ActionType)) {
            throw new \InvalidArgumentException(sprintf('Argument "type" must be an instance of `%s`', ActionType::class));
        }

        static::$registry[$name] = $options;
    }

    /**
     * Get an action item by name
     * 
     * usage:
     * ```php
     * $item1 = ActionItem::get('index');
     * $item2 = ActionItem::get('index')->withOptions(['label' => __('List Projects')]);
     * ```
     * 
     * @param string $name
     * @return static
     */
    public static function get(string $name): static
    {
        $options = static::$registry[$name] ?? static::defaultOptions($name);
        if (!empty($options)) {
            return new static($options);
        }

        throw new \InvalidArgumentException(
            sprintf(
                'Action item "%s" in not registred, use `%s::set(string $name, array $options)` to registred',
                $name,
                static::class
            )
        );
    }

    /**
     * @param string $key
     * @return array
     */
    protected static function defaultOptions(string $key): array
    {
        return match ($key) {
            static::INDEX => [
                'type' => ActionType::Link,
                'url' => ['action' => 'index'],
                'label' => __('List'),
                'icon' => 'bi bi-list me-1',
                'color' => 'light',
            ],
            static::VIEW => [
                'type' => ActionType::Link,
                'url' => ['action' => 'view'],
                'label' => __('View'),
                'icon' => 'bi bi-eye me-1',
                'color' => 'info',
            ],
            static::ADD => [
                'type' => ActionType::Link,
                'url' => ['action' => 'add'],
                'label' => __('Add'),
                'icon' => 'bi bi-plus me-1',
                'color' => 'success',
            ],
            static::EDIT => [
                'type' => ActionType::Link,
                'url' => ['action' => 'edit'],
                'label' => __('Edit'),
                'icon' => 'bi bi-pencil me-1',
                'color' => 'warning',
            ],
            static::DELETE => [
                'type' => ActionType::PostLink,
                'url' => ['action' => 'delete'],
                'label' => __('Delete'),
                'icon' => 'bi bi-trash me-1',
                'color' => 'danger',
                'confirm' => __('Are you sure you want to delete this item?'),
            ],
            static::CANCEL => [
                'type' => ActionType::Link,
                'url' => ['action' => 'index'],
                'label' => __('Cancel'),
                'color' => 'secondary',
            ],
            static::CLOSE_MODAL => [
                'type' => ActionType::Link,
                'url' => '#',
                'data-bs-dismiss' => 'modal',
                'aria-label' => __('Close Modal'),
                'label' => __('Close'),
                'color' => 'secondary',
            ],
            static::LIMIT_CONTROL => [
                'type' => ActionType::LimitControl,
                'limits' => [],
                'default' => null,
                'options' => [
                    'label' => false,
                    'spacing' => 'mb-0',
                ],
            ],
            static::SUBMIT => [
                'type' => ActionType::Button,
                'label' => __('Submit'),
                'icon' => 'bi bi-check me-1',
                'color' => 'primary',
                'data-loader-onclick' => true,
                'options' => [
                    'type' => 'submit',
                    'escapeTitle' => false,
                ],
            ],
            static::BUTTON => [
                'type' => ActionType::Button,
                'label' => __('Button'),
                'icon' => 'bi bi-check me-1',
                'color' => 'primary',
                'options' => [
                    'type' => 'button',
                    'escapeTitle' => false,
                ],
            ],
            static::RESET => [
                'type' => ActionType::Button,
                'label' => __('Reset'),
                'icon' => 'bi bi-check me-1',
                'color' => 'secondary',
                'options' => [
                    'type' => 'reset',
                    'escapeTitle' => false,
                ],
            ],
            static::AJAX_SUBMIT => [
                'type' => ActionType::Button,
                'label' => __('Submit'),
                'icon' => 'bi bi-check me-1',
                'color' => 'primary',
                'data-ajax-submit' => true,
                'data-loader-onclick' => true,
                'options' => [
                    'type' => 'submit',
                    'escapeTitle' => false,
                ],
            ],
            default => [],
        };
    }
}
