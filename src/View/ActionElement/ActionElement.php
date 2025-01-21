<?php
declare(strict_types=1);

namespace BootstrapTools\View\ActionElement;

use BootstrapTools\Utility\Color;
use Cake\Utility\Hash;

enum ActionElement: string implements ActionElementInterface
{
    case Default = 'default';
    case Index = 'index';
    case Add = 'add';
    case Edit = 'edit';
    case Delete = 'delete';
    case View = 'view';
    case LimitControl = 'limit_control';
    case Submit = 'submit';
    case Cancel = 'cancel';
    case CloseModal = 'close_modal';
    case AjaxSubmit = 'ajax_submit';

    /**
     * @var array<string, mixed>
     */
    protected array $options = [];

    /**
     * @param array $options
     * @return self
     */
    public function withOptions(array $options): self
    {
        $this->options = Hash::merge($this->options, $options);

        return $this;
    }

    /**
     * @param array $options
     * @return array
     */
    public function getActionElementOptions(array $options = []): ActionElementOptions
    {
        $options = array_merge($this->options, $options);
        $id = $options['id'] ?? null;
        unset($options['id']);

        $dataset = $this->getActionDataset($id);
        $dataset['options'] = Hash::merge($dataset['options'], $options);

        return new ActionElementOptions($dataset['type'], $dataset['options']);
    }

    protected function getActionDataset(int|string $id): array
    {
        return match ($this) {
            self::Index => [
                'type' => ActionElementOptions::TYPE_LINK,
                'options' => [
                    'label' => __('List'),
                    'url' => ['action' => 'index', 'redirect' => true],
                    'icon' => 'list',
                    'color' => Color::LIGHT,
                ],
            ],

            self::Add => [
                'type' => ActionElementOptions::TYPE_LINK,
                'options' => [
                    'label' => __('Add'),
                    'url' => ['action' => 'add'],
                    'icon' => 'plus',
                    'color' => Color::SUCCESS,
                ],
            ],

            self::Edit => [
                'type' => ActionElementOptions::TYPE_LINK,
                'options' => [
                    'label' => __('Edit'),
                    'url' => array_filter(['action' => 'edit', $id ?? null, 'redirect' => true]),
                    'icon' => 'pencil',
                    'color' => Color::WARNING,
                ],
            ],

            self::View => [
                'type' => ActionElementOptions::TYPE_LINK,
                'options' => [
                    'label' => __('View'),
                    'url' => array_filter(['action' => 'view', $id ?? null, 'redirect' => true]),
                    'icon' => 'eye',
                    'color' => Color::INFO,
                ],
            ],

            self::Delete => [
                'type' => ActionElementOptions::TYPE_POSTLINK,
                'options' => [
                    'label' => __('Delete'),
                    'url' => array_filter(['action' => 'delete', $id ?? null, 'redirect' => true]),
                    'icon' => 'trash',
                    'color' => Color::DANGER,
                    'confirm' => __('Are you sure you want to delete this record?'),
                ],
            ],

            self::LimitControl => [
                'type' => ActionElementOptions::TYPE_LIMIT_CONTROL,
                'options' => [
                    'limits' => [],
                    'default' => null,
                    'options' => [
                        'label' => false,
                        'spacing' => 'mb-0',
                    ],
                ],
            ],

            self::Submit => [
                'type' => ActionElementOptions::TYPE_SUBMIT,
                'options' => [
                    'label' => __('Submit'),
                    'color' => Color::PRIMARY,
                ],
            ],

            self::Cancel => [
                'type' => ActionElementOptions::TYPE_BUTTON_LINK,
                'options' => [
                    'label' => __('Cancel'),
                    'url' => ['action' => 'index'],
                    'color' => Color::SECONDARY,
                ],
            ],

            self::CloseModal => [
                'type' => ActionElementOptions::TYPE_BUTTON,
                'options' => [
                    'label' => __('Close'),
                    'color' => Color::SECONDARY,
                    'data-bs-dismiss' => 'modal',
                ],
            ],

            self::AjaxSubmit => [
                'type' => ActionElementOptions::TYPE_AJAX_SUBMIT,
                'options' => [
                    'label' => __('Submit'),
                    'color' => Color::PRIMARY,
                ],
            ],

            self::Default => [
                'type' => ActionElementOptions::TYPE_BUTTON,
                'options' => [
                    'label' => __('Action'),
                    'color' => Color::SECONDARY,
                ],
            ],
        };
    }
}
