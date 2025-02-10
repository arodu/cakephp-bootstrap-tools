<?php

declare(strict_types=1);

namespace BootstrapTools\View\Helper;

use BootstrapTools\View\VisualElement\VisualElementInterface;
use BootstrapTools\View\VisualElement\VisualElement;
use Cake\View\Helper;
use Cake\View\StringTemplateTrait;

/**
 * App helper
 */
class BsHelper extends Helper
{
    use StringTemplateTrait;

    /**
     * Default configuration.
     *
     * @var array<string, mixed>
     */
    protected array $_defaultConfig = [
        'templates' => [
            'badge' => '<span class="{{class}}" aria-label="{{aria-label}}"{{attrs}}>{{icon}}{{label}}</span>',
            'button' => '<a href="{{url}}" class="{{class}}"{{attrs}}>{{icon}}{{label}}</a>',
            'text' => '<span class="{{class}}"{{attrs}}>{{icon}}{{label}}</span>',
            'alert' => '<div class="{{class}}" role="alert"{{attrs}}>{{closeButton}}<h4 class="alert-heading">{{icon}}{{label}}</h4><p class="mb-0">{{content}}</p></div>',
            'icon' => '<i class="{{class}}"{{attrs}}></i>',
        ],
    ];

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

        $class = 'badge text-bg-' . ($visualElement->getColor() ?? $this->getConfig('bagde.color') ?? 'secondary');
        $class .= ($options['pill'] ?? $this->getConfig('bagde.pill') ?? false) ? ' rounded-pill' : '';
        $class .= ' ' . ($options['class'] ?? '');

        $options += [
            'class' => $class,
            'title' => $visualElement->getDescription() ?? $visualElement->getLabel() ?? null,
            'aria-label' => $visualElement->getLabel() ?? '',
        ];

        if ($options['tooltip'] ?? $this->getConfig('bagde.tooltip') ?? false) {
            $options = $this->tooltipOptions($visualElement, $options);
            unset($options['tooltip']);
        }

        $icon = $this->renderIcon($visualElement, $options);

        return $this->formatTemplate('badge', [
            'class' => $options['class'],
            'aria-label' => $options['aria-label'],
            'icon' => $icon,
            'label' => $visualElement->getLabel(),
            'attrs' => $this->templater()->formatAttributes($options, ['class', 'aria-label', 'icon', 'label']),
        ]);
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

        return $this->formatTemplate('text', [
            'class' => $options['class'],
            'aria-label' => $options['title'],
            'icon' => $icon,
            'label' => $visualElement->getLabel(),
            'attrs' => $this->templater()->formatAttributes($options, ['class', 'aria-label', 'icon', 'label']),
        ]);
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
            $closeButton = $this->getView()->Html->tag('button', null, [
                'type' => 'button',
                'class' => 'btn-close',
                'data-bs-dismiss' => 'alert',
                'aria-label' => __('Close'),
            ]);
        }

        return $this->formatTemplate('alert', [
            'class' => $options['class'],
            'aria-label' => $options['title'],
            'closeButton' => $closeButton,
            'icon' => $icon,
            'label' => $visualElement->getLabel(),
            'content' => $visualElement->getDescription(),
            'attrs' => $this->templater()->formatAttributes($options, ['class', 'aria-label', 'icon', 'label', 'content']),
        ]);
    }

    /**
     * Generates a Bootstrap-styled icon element.
     * 
     * visualElement options:
     * - `label` (string): The text to be displayed inside the icon.
     * - `icon` (string): The icon to be displayed.
     * - `color` (string): The color of the icon.
     * - `description` (string): A description used as a tooltip or additional information.
     * 
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

        $options += ['class' => ''];
        $options['class'] .= ' bi bi-' . $visualElement->getIcon();
        $options['class'] .= ' text-' . ($visualElement->getColor() ?? $this->getConfig('defaultColor') ?? 'secondary');
        $options['title'] = $visualElement->getDescription() ?? $visualElement->getLabel() ?? '';

        if ($options['tooltip'] ?? $this->getConfig('defaultTooltip') ?? false) {
            $options = $this->tooltipOptions($visualElement, $options);
            unset($options['tooltip']);
        }

        return $this->formatTemplate('icon', [
            'class' => $options['class'],
            'aria-label' => $options['title'],
            'attrs' => $this->templater()->formatAttributes($options, ['class', 'aria-label']),
        ]);
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

        return $this->formatTemplate('button', [
            'url' => $options['url'] ?? '#',
            'class' => $options['class'],
            'aria-label' => $options['title'],
            'icon' => $icon,
            'label' => $label,
            'attrs' => $this->templater()->formatAttributes($options, ['class', 'aria-label', 'icon', 'label']),
        ]);
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
            $output[] = $this->formatTemplate('badge', [
                'class' => $options['class'],
                'aria-label' => $tag,
                'icon' => '',
                'label' => $tag,
                'attrs' => $this->templater()->formatAttributes($options, ['class', 'aria-label', 'icon', 'label']),
            ]);
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
            return $this->getView()->Html->tag('i', '', [
                'class' => 'me-1 ' . ($visualElement->getIcon() ?? $options['icon'] ?? $this->getConfig('defaultIcon') ?? 'circle-fill')
            ]);
        }
        return '';
    }

    /**
     * @param integer $startYear
     * @return string
     */
    public function copyrightYears(int $startYear): string
    {
        $currentYear = (int) date('Y');

        return $startYear < $currentYear ? "$startYear-$currentYear" : "$startYear";
    }
}
