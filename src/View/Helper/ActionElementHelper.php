<?php
declare(strict_types=1);

namespace BootstrapTools\View\Helper;

use BootstrapTools\Utility\RegisterScopeDataTrait;
use BootstrapTools\View\ActionElement\ActionElement;
use BootstrapTools\View\ActionElement\ActionElementInterface;
use BootstrapTools\View\ActionElement\ActionElementOptions;
use Cake\Utility\Hash;
use Cake\View\Helper;

/**
 * ActionElementHelper
 * 
 * Helper for rendering action groups.
 * 
 * Usage:
 * - Set the items with setItem() or setItems()
 * - Render the items with render()
 * - Optionally set the scope with withScope()
 * 
 */
class ActionElementHelper extends Helper
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
    public function options(array $options): self
    {
        $scope = $this->getScopeName($options['scope'] ?? null);
        $this->options[$scope] = $options;

        return $this;
    }

    /**
     * @param \BootstrapTools\View\ActionElement|array|string $item
     * @param array $options
     * @return self
     */
    public function setItem(string|array|ActionElement $item, array $options = []): self
    {
        $scope = $this->getScopeName($options['scope'] ?? null);
        $options = Hash::merge($this->options[$scope] ?? [], $options);

        $ActionType = $item;
        if (is_string($item)) {
            $ActionType = ActionElement::tryFrom($item);
        } elseif (is_array($item) && isset($item['type'])) {
            $ActionType = ActionElement::tryFrom($item['type']);
        } elseif (is_array($item)) {
            $item = array_merge($item, $options);
        }

        if ($ActionType instanceof ActionElementInterface) {
            $this->setScopeData($ActionType->getActionElementOptions($options), $scope);
        } else {
            $this->setScopeData($item, $scope);
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
            if ($itemOptions instanceof ActionElement) {
                $this->setItem($itemOptions, $options, $scope);
                continue;
            }

            $this->setItem($item, array_merge($itemOptions, $options), $scope);
        }

        return $this;
    }

    protected function actionType(string $type, array $options = []): ActionElement
    {
        return ActionElement::tryFrom($type);
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
        foreach ($this->items[$scope] ?? [] as $item) {
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

    /**
     * @param \App\View\Enum\ActionElement|array $item
     * @param array $options
     * @return string
     */
    public function renderItem(array|\BootstrapTools\View\Helper\ActionElementInterface $item, array $options = []): string
    {
        if ($item instanceof ActionElementInterface) {
            $item = $item->getActionElementOptions($options)->toArray();
        }

        switch ($item['type']) {
            case ActionElementOptions::TYPE_LINK:
                $item = $this->formatOptions($item);

                return $this->Html->link($item['label'], $item['url'], $item['options']);

            case ActionElementOptions::TYPE_POSTLINK:
                $item = $this->formatOptions($item);

                return $this->Form->postLink($item['label'], $item['url'], $item['options']);

            case ActionElementOptions::TYPE_LIMIT_CONTROL:
                return $this->Paginator->limitControl($item['limits'], $item[self::DEFAULT_SCOPE], $item['options']);

            case ActionElementOptions::TYPE_SUBMIT:
                $item = $this->formatOptions($item);
                $item['options']['escapeTitle'] = false;
                $item['options']['type'] = 'submit';

                return $this->Form->button($item['label'], $item['options']);

            case ActionElementOptions::TYPE_BUTTON:
                $item = $this->formatOptions($item);
                $item['options']['escapeTitle'] = false;
                $item['options']['type'] = 'button';

                return $this->Form->button($item['label'], $item['options']);

            case ActionElementOptions::TYPE_RESET:
                $item = $this->formatOptions($item);
                $item['options']['escapeTitle'] = false;
                $item['options']['type'] = 'reset';

                return $this->Form->button($item['label'], $item['options']);

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
