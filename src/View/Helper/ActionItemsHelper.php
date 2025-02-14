<?php

declare(strict_types=1);

namespace BootstrapTools\View\Helper;

use BootstrapTools\View\ActionItems\ActionItem;
use BootstrapTools\View\ActionItems\ActionItemInterface;
use BootstrapTools\View\ActionItems\ActionType;
use Cake\Utility\Hash;
use Cake\View\Helper;
use UtilityKit\Trait\RegisterScopeDataTrait;

/**
 * Helper for rendering action groups.
 * 
 * @property \Cake\View\Helper\HtmlHelper $Html
 * @property \Cake\View\Helper\PaginatorHelper $Paginator
 * @property \Cake\View\Helper\FormHelper $Form
 */
class ActionItemsHelper extends Helper
{
    use RegisterScopeDataTrait;
    use FormatOptionsTrait;

    /**
     * Default configuration.
     *
     * @var array<string, mixed>
     */
    protected array $_defaultConfig = [
        'defaultGroup' => false,
        'actionItemClass' => ActionItem::class,
    ];

    protected array $options = [];

    protected array $helpers = ['Html', 'Paginator', 'Form', 'BootstrapTools.ModalAjax'];

    protected string $actionItemClass;

    /**
     * @param array $options
     * @return self
     */
    public function withOptions(array $options): self
    {
        $scope = $this->getScopeName($options['scope'] ?? null);
        $this->options[$scope] = $options;

        return $this;
    }

    public function setItem(ActionItemInterface|string $item, array $options = []): self
    {
        $scope = $this->getScopeName($options['scope'] ?? null);
        $actionItem = match (true) {
            is_string($item) => $this->actionItemClass()::get($item),
            $item instanceof ActionItemInterface => $item,
        };

        $actionItem = $actionItem->withOptions($options);
        $this->setScopeData($actionItem, $scope);

        return $this;
    }

    public function registry(string $name, array $options = []): self
    {
        $this->actionItemClass()::set($name, $options);

        return $this;
    }

    /**
     * @param array $items
     * @param array $options
     * @return self
     */
    public function setItems(array $items, array $options = []): self
    {
        foreach ($items as $key => $itemOptions) {
            if (is_string($key) && is_array($itemOptions)) {
                $this->setItem($key, $itemOptions + $options);
                continue;
            }

            $this->setItem($itemOptions, $options);
        }

        return $this;
    }

    /**
     * Render the actions
     * 
     * Options:
     * - scope: string|null
     * - reset: bool
     * - group: bool
     * 
     * @param array $options
     * @return string
     */
    public function render(array $options = []): string
    {
        $scope = $this->getScopeName($options['scope'] ?? null);
        $options = Hash::merge($this->options[$scope] ?? [], $options);

        $output = '';
        foreach ($this->getScopeData($scope) ?? [] as $item) {
            if (is_string($item)) {
                $output .= $item;
                continue;
            }
            $item = $item->withOptions($options);
            $output .= $this->renderItem($item, $options);
        }

        if ($options['reset'] ?? true) {
            $this->deleteScopeData($scope);
            unset($this->options[$scope]);
        }

        if ($options['group'] ?? $this->getConfig('defaultGroup') ?? false) {
            $output = $this->Html->tag('div', $output, ['class' => 'btn-group', 'role' => 'group']);
        }

        $this->defaultScope();

        return $output;
    }

    public function renderItem(ActionItemInterface $item): string
    {
        $data = $item->toArray();
        $type = $data['type'];
        unset($data['type']);

        switch ($type) {
            case ActionType::Link:
                $options = $this->formatOptions($data);
                return $this->Html->link($options['label'], $options['url'], $options['options']);

            case ActionType::PostLink:
                $options = $this->formatOptions($data);
                return $this->Form->postLink($options['label'], $options['url'], $options['options']);

            case ActionType::Button:
                $options = $this->formatOptions($data);
                return $this->Form->button($options['label'], $options['options']);

            case ActionType::LimitControl:
                $options = $data;
                return $this->Paginator->limitControl($options['limits'], null, $options['options']);

            case ActionType::ModalLink:
                $options = $this->formatOptions($data);
                return $this->ModalAjax->link($options['label'], $options['url'], $options['options']);

            default:
                return '';
        }
    }


    protected function actionItemClass(): string
    {
        if (empty($this->actionItemClass)) {
            $this->actionItemClass = $this->getConfig('actionItemClass') ?? ActionItem::class;

            if (!class_exists($this->actionItemClass) || !is_subclass_of($this->actionItemClass, ActionItemInterface::class)) {
                throw new \RuntimeException('Action item class not found, or does not implement ' . ActionItemInterface::class);
            }
        }

        return $this->actionItemClass;
    }
}
