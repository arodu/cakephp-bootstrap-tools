<?php

declare(strict_types=1);

namespace BootstrapTools\View\ActionElement;

use BootstrapTools\Utility\Color;
use BootstrapTools\View\ActionElement\ActionElementInterface;
use Cake\Utility\Hash;

enum ActionItem: string implements ActionElementInterface
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

    public function getActionElement(array $options = []): ActionElement
    {
        $id = $options['id'] ?? null;
        unset($options['id']);

        $dataset = $this->getActionDataset($id);
        $dataset = Hash::merge($dataset, $options);

        return new ActionElement($dataset);
    }

    protected function getActionDataset(int|string|null $id): array
    {
        return match ($this) {
            self::Index => [
                'type' => ActionElement::TYPE_LINK,
                'label' => __('List'),
                'url' => ['action' => 'index', 'redirect' => true],
                'icon' => 'list',
                'color' => Color::LIGHT,
            ],

            self::Add => [
                'type' => ActionElement::TYPE_LINK,
                'label' => __('Add'),
                'url' => ['action' => 'add'],
                'icon' => 'plus',
                'color' => Color::SUCCESS,
            ],

            self::Edit => [
                'type' => ActionElement::TYPE_LINK,
                'label' => __('Edit'),
                'url' => array_filter(['action' => 'edit', $id ?? null, 'redirect' => true]),
                'icon' => 'pencil',
                'color' => Color::WARNING,
            ],

            self::View => [
                'type' => ActionElement::TYPE_LINK,
                'label' => __('View'),
                'url' => array_filter(['action' => 'view', $id ?? null, 'redirect' => true]),
                'icon' => 'eye',
                'color' => Color::INFO,
            ],

            self::Delete => [
                'type' => ActionElement::TYPE_POSTLINK,
                'label' => __('Delete'),
                'url' => array_filter(['action' => 'delete', $id ?? null, 'redirect' => true]),
                'icon' => 'trash',
                'color' => Color::DANGER,
                'confirm' => __('Are you sure you want to delete this record?'),
            ],

            self::LimitControl => [
                'type' => ActionElement::TYPE_LIMIT_CONTROL,
                'limits' => [],
                'default' => null,
                'options' => [
                    'label' => false,
                    'spacing' => 'mb-0',
                ],
            ],

            self::Submit => [
                'type' => ActionElement::TYPE_SUBMIT,
                'label' => __('Submit'),
                'color' => Color::PRIMARY,
            ],

            self::Cancel => [
                'type' => ActionElement::TYPE_LINK,
                'label' => __('Cancel'),
                'url' => ['action' => 'index'],
                'color' => Color::SECONDARY,
            ],

            self::CloseModal => [
                'type' => ActionElement::TYPE_BUTTON,
                'label' => __('Close'),
                'color' => Color::SECONDARY,
                'data-bs-dismiss' => 'modal',
            ],

            self::AjaxSubmit => [
                'type' => ActionElement::TYPE_AJAX_SUBMIT,
                'label' => __('Submit'),
                'color' => Color::PRIMARY,
            ],

            self::Default => [
                'type' => ActionElement::TYPE_BUTTON,
                'label' => __('Action'),
                'color' => Color::SECONDARY,
            ],
        };
    }
}
