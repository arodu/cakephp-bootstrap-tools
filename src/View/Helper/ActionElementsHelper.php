<?php
declare(strict_types=1);

namespace BootstrapTools\View\Helper;

use BootstrapTools\Utility\RegisterScopeDataTrait;
use BootstrapTools\View\ActionElement\ActionItem;
use BootstrapTools\View\ActionElement\ActionElementInterface;
use BootstrapTools\View\ActionElement\ActionElement;
use Cake\Utility\Hash;
use Cake\View\Helper;

/**
 * ActionElementsHelper
 * 
 * Helper for rendering action groups.
 * 
 * Usage:
 * - Set the items with setItem() or setItems()
 * - Render the items with render()
 * - Optionally set the scope with withScope()
 * 
 */
class ActionElementsHelper extends Helper
{
    use RegisterScopeDataTrait;

    /**
     * Default configuration.
     *
     * @var array<string, mixed>
     */
    protected array $_defaultConfig = [
        'defaultGroup' => false,
    ];
    protected array $options = [];

    protected array $helpers = ['Html', 'Paginator', 'Form'];

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

    /**
     * @param ActionElementInterface|array|string $item
     * @param array $options
     * @return self
     */
    public function setItem(ActionElementInterface|array|string $item, array $options = []): self
    {
        $scope = $this->getScopeName($options['scope'] ?? null);
        $options = Hash::merge($this->options[$scope] ?? [], $options);

        if ($item instanceof ActionElementInterface) {
            $actionElement = $item->getActionElement($options);
        } elseif (is_string($item)) {
            $actionElement = ActionItem::tryFrom($item)->getActionElement($options);
        } elseif (is_array($item) && isset($item['type'])) {
            $actionElement = ActionItem::tryFrom($item['type'])->getActionElement($item);
        } elseif (is_array($item)) {
            $actionElement = new ActionElement($item);
        }

        if (!empty($actionElement)) {
            $this->setScopeData($actionElement, $scope);
        }

        return $this;
    }

    /**
     * @param array $items
     * @param array $options
     * @return self
     */
    public function setItems(array $items, array $options = []): self
    {
        $scope = $this->getScopeName($options['scope'] ?? null);
        foreach ($items as $item => $itemOptions) {
            if ($itemOptions instanceof ActionElementInterface) {
                $this->setItem($itemOptions, $options, $scope);
                continue;
            }

            $this->setItem($item, array_merge($itemOptions, $options), $scope);
        }

        return $this;
    }

    protected function actionType(string $type, array $options = []): ActionItem
    {
        return ActionItem::tryFrom($type);
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

            $output .= $this->renderItem($item);
        }

        if ($options['reset'] ?? false) {
            $this->deleteScopeData($scope);
        }

        if ($options['group'] ?? $this->getConfig('defaultGroup') ?? false) {
            $output = $this->Html->tag('div', $output, ['class' => 'btn-group', 'role' => 'group']);
        }

        $this->defaultScope();

        return $output;
    }

    public function renderItem(ActionElement|ActionElementInterface|array $item, array $options = []): string
    {
        if (is_array($item)) {
            $item = new ActionElement($item, $options);
        } elseif ($item instanceof ActionElementInterface) {
            $item = $item->getActionElement($options);
        }

        switch ($item->getType()) {
            case ActionElement::TYPE_LINK:
                $options = $this->formatOptions($item->getOptions());

                return $this->Html->link($options['label'], $options['url'], $options['options']);

            case ActionElement::TYPE_POSTLINK:
                $options = $this->formatOptions($item->getOptions());

                return $this->Form->postLink($options['label'], $options['url'], $options['options']);

            case ActionElement::TYPE_LIMIT_CONTROL:
                $options = $item->getOptions();
                return $this->Paginator->limitControl($options['limits'], null, $options['options']);

            case ActionElement::TYPE_SUBMIT:
                $options = $this->formatOptions($item->getOptions());
                $options['options']['escapeTitle'] = false;
                $options['options']['type'] = 'submit';

                return $this->Form->button($options['label'], $options['options']);

            case ActionElement::TYPE_BUTTON:
                $options = $this->formatOptions($item->getOptions());
                $options['options']['escapeTitle'] = false;
                $options['options']['type'] = 'button';

                return $this->Form->button($options['label'], $options['options']);

            case ActionElement::TYPE_RESET:
                $options = $this->formatOptions($item->getOptions());
                $options['options']['escapeTitle'] = false;
                $options['options']['type'] = 'reset';

                return $this->Form->button($options['label'], $options['options']);

            default:
                return '';
        }
    }

    /**
     * @param array $item
     * @return array
     */
    protected function formatOptions(array $item): array
    {
        $label = $item['label'] ?? null;
        $url = $item['url'] ?? null;

        $keysToRemove = ['label', 'url', 'color', 'outline', 'size', 'block', 'active', 'disabled', 'icon', 'type'];
        $options = array_diff_key($item, array_flip($keysToRemove));

        $classes = ['btn'];
        if (!empty($item['color']) && !($item['outline'] ?? false)) {
            $classes[] = 'btn-' . $item['color'];
        }
        if (!empty($item['outline']) && !empty($item['color'])) {
            $classes[] = 'btn-outline-' . $item['color'];
        }
        if (!empty($item['size'])) {
            $classes[] = 'btn-' . $item['size'];
        }
        if (!empty($item['block'])) {
            $classes[] = 'btn-block';
        }
        if (!empty($item['active'])) {
            $classes[] = 'active';
        }
        if (!empty($item['disabled'])) {
            $classes[] = 'disabled';
            $options['disabled'] = true;
            $options['aria-disabled'] = 'true';
        }
        $options['class'] = implode(' ', $classes);

        if (!empty($item['icon'])) {
            $label = $this->Html->icon($item['icon']) . ' ' . $label;
            $options['escape'] = false;
        }

        return compact('label', 'url', 'options');
    }
}
