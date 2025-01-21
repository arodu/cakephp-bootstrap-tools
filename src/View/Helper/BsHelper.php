<?php
declare(strict_types=1);

namespace BootstrapTools\View\Helper;

use BootstrapTools\View\VisualElement\VisualElementInterface;
use BootstrapTools\View\VisualElement\VisualElement;
use Cake\View\Helper;

/**
 * App helper
 */
class BsHelper extends Helper
{
    /**
     * Default configuration.
     *
     * @var array<string, mixed>
     */
    protected array $_defaultConfig = [
        'defaultColor' => 'secondary',
        'defaultTooltipPlacement' => 'top',
        'defaultTooltip' => false,
        'defaultIcon' => false,
        'defaultIcon' => 'circle-fill',
        'defaultPill' => false,
    ];

    /**
     * @var array
     */
    protected array $helpers = ['Html'];

    /**
     * @param VisualElement|array $options
     * @return VisualElement
     */
    public function visualElement(VisualElementInterface|array $element, array $options = []): VisualElement
    {
        if ($element instanceof VisualElementInterface) {
            return $element->getVisualElement($options);
        }

        return new VisualElement(...$element);
    }

    /**
     * Generates a Bootstrap-styled badge element.
     *
     * ### $visualElement options:
     * - `label` (string): The text to be displayed inside the badge.
     * - `icon` (string): The icon to be displayed inside the badge.
     * - `color` (string): The background color of the badge.
     * - `description` (string): A description used as a tooltip or additional information.
     *
     * ### $options:
     * - `class` (string): Additional CSS classes for the badge.
     * - `tooltip` (string|bool): Tooltip placement (`top`, `bottom`, `start`, `end`) or `false` to disable it. Default is `top`.
     * - `icon` (string|false): Overrides the default icon, or `false` to disable it.
     * - `pill` (bool): Enables the pill style for the badge.
     *
     * @param \BsUtils\Utility\VisualElementInterface|array $visualElement The visual element object or an array of properties.
     * @param array<string, mixed> $options Additional options for customizing the badge.
     * @return string The generated HTML badge element.
     */
    public function badge(VisualElementInterface|array $visualElement, array $options = []): string
    {
        $visualElement = $this->visualElement($visualElement);
        $options += ['class' => 'badge'];
        $options['class'] .= ' text-bg-' . ($visualElement->getColor() ?? $this->getConfig('defaultColor') ?? 'secondary');
        $options['title'] = $visualElement->getDescription() ?? $visualElement->getLabel() ?? null;
        $options['aria-label'] = $visualElement->getLabel() ?? '';

        if ($options['pill'] ?? $this->getConfig('defaultPill') ?? false) {
            $options['class'] .= ' rounded-pill';
        }

        if ($options['tooltip'] ?? $this->getConfig('defaultTooltip') ?? false) {
            $options = $this->tooltipOptions($visualElement, $options);
            unset($options['tooltip']);
        }

        $icon = $this->renderIcon($visualElement, $options);

        return $this->Html->tag('span', $icon . $visualElement->getLabel(), $options);
    }

    /**
     * @param VisualElementInterface|array $visualElement
     * @param array $options
     * @return string
     */
    public function text(VisualElementInterface|array $visualElement, array $options = []): string
    {
        $visualElement = $this->visualElement($visualElement);
        $options += ['class' => 'text-' . ($visualElement->getColor() ?? $this->getConfig('defaultColor') ?? 'secondary')];
        $options['title'] = $visualElement->getDescription() ?? $visualElement->getLabel() ?? null;

        if ($options['tooltip'] ?? $this->getConfig('defaultTooltip') ?? false) {
            $options = $this->tooltipOptions($visualElement, $options);
            unset($options['tooltip']);
        }

        $icon = $this->renderIcon($visualElement, $options);

        return $this->Html->tag('span', $icon . $visualElement->getLabel(), $options);
    }

    /**
     * @param VisualElementInterface|array $visualElement
     * @param array $options
     * @return string
     */
    public function alert(VisualElementInterface|array $visualElement, array $options = []): string
    {
        $visualElement = $this->visualElement($visualElement);
        $options += ['class' => 'alert'];
        $options['class'] .= ' alert-' . ($visualElement->getColor() ?? $this->getConfig('defaultColor') ?? 'secondary');
        $options['title'] = $visualElement->getDescription() ?? $visualElement->getLabel() ?? null;

        $icon = $this->renderIcon($visualElement, $options);

        $closeButton = '';
        if (isset($options['dismissible']) && $options['dismissible']) {
            $closeButton = $this->Html->tag('button', null, [
                'type' => 'button',
                'class' => 'btn-close',
                'data-bs-dismiss' => 'alert',
                'aria-label' => __('Close'),
            ]);
        }

        $header = $this->Html->tag('h4', $icon . $visualElement->getLabel(), ['class' => 'alert-heading']);
        $content = $this->Html->tag('p', $visualElement->getDescription(), ['class' => 'mb-0']);

        return $this->Html->tag('div', $closeButton . $header . $content, $options);
    }

    /**
     * @param VisualElementInterface|array $visualElement
     * @param array $options
     * @return string
     */
    public function icon(VisualElementInterface|array $visualElement, array $options = []): string
    {
        $visualElement = $this->visualElement($visualElement);
        if (empty($visualElement->getIcon())) {
            return '';
        }

        $options += ['class' => 'bi'];
        $options['class'] .= ' bi-' . $visualElement->getIcon();
        $options['class'] .= ' text-' . ($visualElement->getColor() ?? $this->getConfig('defaultColor') ?? 'secondary');
        $options['title'] = $visualElement->getDescription() ?? $visualElement->getLabel() ?? '';

        if ($options['tooltip'] ?? $this->getConfig('defaultTooltip') ?? false) {
            $options = $this->tooltipOptions($visualElement, $options);
            unset($options['tooltip']);
        }

        return $this->Html->tag('i', '', $options);
    }

    public function button(VisualElementInterface|array $visualElement, array $options = []): string
    {
        $visualElement = $this->visualElement($visualElement);
        $options += ['class' => 'btn'];
        $options['class'] .= ' btn-' . ($visualElement->getColor() ?? $this->getConfig('defaultColor') ?? 'secondary');
        $options['title'] = $visualElement->getDescription() ?? $visualElement->getLabel() ?? '';

        if ($options['tooltip'] ?? $this->getConfig('defaultTooltip') ?? false) {
            $options = $this->tooltipOptions($visualElement, $options);
            unset($options['tooltip']);
        }

        $icon = $this->renderIcon($visualElement, $options);
        $label = $visualElement->getLabel();

        return $this->Html->link($icon . $label, $options['url'] ?? '#', $options);
    }

    /**
     * @param array<string> $tags
     * @param array $options
     * @return string
     */
    public function tags(array $tags, array $options = []): string
    {
        $output = [];
        $options += ['class' => 'badge bg-' . ($options['color'] ?? $this->getConfig('defaultColor') ?? 'secondary')];
        foreach ($tags as $tag) {
            $output[] = $this->Html->tag('span', $tag, $options);
        }

        return implode(' ', $output);
    }

    /**
     * @param VisualElement $visualElement
     * @param array $options
     * @return array
     */
    protected function tooltipOptions(VisualElement $visualElement, array $options = []): array
    {
        $options += [
            'title' => $visualElement->getDescription() ?? $visualElement->getLabel(),
            'aria-label' => $visualElement->getLabel(),
            'data-bs-toggle' => 'tooltip',
            'data-bs-placement' => $options['tooltip'] ?? $this->getConfig('defaultTooltipPlacement') ?? 'top',
        ];

        return $options;
    }

    protected function renderIcon(VisualElement $visualElement, array $options): string
    {
        // @todo check wath is flex-shrink-0 and me-2

        if ($options['icon'] ?? $this->getConfig('defaultIcon') ?? false) {
            return $this->Html->tag('i', '', [
                'class' => 'flex-shrink-0 me-2 bi bi-' . ($visualElement->getIcon() ?? $options['icon'] ?? $this->getConfig('defaultIcon') ?? 'circle-fill')
            ]);
        }
        return '';
    }
}
