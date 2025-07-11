<?php

declare(strict_types=1);

namespace BootstrapTools\View\Helper;

use BootstrapTools\View\VisualElement\VisualElementInterface;
use BootstrapTools\View\VisualElement\VisualElement;
use Cake\Utility\Text;
use Cake\View\Helper;
use Cake\View\StringTemplateTrait;

/**
 * Bootstrap helper
 */
class BootstrapHelper extends Helper
{
    use StringTemplateTrait;

    /**
     * Default configuration.
     *
     * @var array<string, mixed>
     */
    protected array $_defaultConfig = [
        'defaults' => [
            'color' => 'secondary',
            'tooltip' => [
                'enabled' => false,
                'placement' => 'top',
            ],
            'icon' => [
                'name' => 'circle-fill',
            ],
        ],
        'badge' => [
            'pill' => false,
        ],
        'button' => [],
        'alert' => [],
        'listGroup' => [
            'itemTag' => 'li',
        ],
        'dropdown' => [
            'buttonDefaultClass' => 'btn-secondary',
        ],
        'templates' => [
            'badge' => '<span class="{{class}}" {{attrs}}>{{icon}}{{label}}</span>',
            'button' => '<a href="{{url}}" class="{{class}}"{{attrs}}>{{icon}}{{label}}</a>',

            // --- Para botones <button>
            'buttonElement' => '<button type="{{type}}" class="{{class}}"{{attrs}}>{{icon}}{{label}}</button>',
            'text' => '<span class="{{class}}"{{attrs}}>{{icon}}{{label}}</span>',
            'alert' => '<div class="{{class}}" role="alert"{{attrs}}>{{closeButton}}<h4 class="alert-heading">{{icon}}{{label}}</h4><p class="mb-0">{{content}}</p></div>',
            'icon' => '<i class="{{class}}"{{attrs}}></i>',

            // --- List Group
            'listGroup' => '<ul class="list-group {{class}}"{{attrs}}>{{items}}</ul>',
            'listGroupDiv' => '<div class="list-group {{class}}"{{attrs}}>{{items}}</div>', // Para items que son <a> o <button>
            'listGroupItem' => '<{{tag}} class="list-group-item {{class}}" href="{{url}}" {{attrs}}>{{icon_html}}{{label_html}}{{content_html}}</{{tag}}>',

            // --- Dropdown
            'dropdown' => '<div class="dropdown d-inline-block {{wrapperClass}}"{{wrapperAttrs}}>{{button_html}}{{menu_html}}</div>',
            'dropdownMenu' => '<ul class="dropdown-menu {{class}}" aria-labelledby="{{buttonId}}"{{attrs}}>{{items}}</ul>',
            'dropdownItem' => '<li><a class="dropdown-item {{class}}" href="{{url}}"{{attrs}}>{{icon_html}}{{label}}</a></li>',
            'dropdownItemButton' => '<li><button type="button" class="dropdown-item {{class}}"{{attrs}}>{{icon_html}}{{label}}</button></li>',
            'dropdownHeader' => '<li><h6 class="dropdown-header {{class}}"{{attrs}}>{{label}}</h6></li>',
            'dropdownDivider' => '<li><hr class="dropdown-divider {{class}}"{{attrs}}></li>',

            // --- Progress Bar
            'progress' => '<div class="progress {{wrapper_class}}" role="progressbar" aria-valuenow="{{value}}" aria-valuemin="{{min}}" aria-valuemax="{{max}}" aria-label="{{ariaLabel}}" {{wrapper_attrs}}>{{bar_html}}</div>',
            'progressBar' => '<div class="progress-bar {{class}}" style="width: {{percentage}}%;"{{attrs}}>{{label_html}}</div>',

            // --- Spinner
            'spinner' => '<div class="{{typeClass}} {{colorClass}} {{sizeClass}}" role="status"{{attrs}}><span class="visually-hidden">{{label}}</span></div>',

            // --- Toast
            'toast' => '<div class="toast {{class}}" role="alert" aria-live="assertive" aria-atomic="true" id="{{id}}"{{attrs}}><div class="toast-header {{headerClass}}">{{icon_html}}<strong class="me-auto">{{label}}</strong><small class="text-muted">{{timestamp}}</small>{{closeButton}}</div><div class="toast-body {{bodyClass}}">{{content}}</div></div>',
        ],
    ];

    /**
     * @param VisualElement|VisualElementInterface|array $options
     * @return VisualElement
     */
    protected function visualElement(
        VisualElement|VisualElementInterface|array $element,
        array $options = []
    ): VisualElement {
        if ($element instanceof VisualElement) {
            return $element;
        }

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
     * @param VisualElement|VisualElementInterface|array $visualElement The visual element object or an array of properties.
     * @param array<string, mixed> $options Additional options for customizing the badge.
     * @return string The generated HTML badge element.
     */
    public function badge(
        VisualElement|VisualElementInterface|array $visualElement,
        array $options = []
    ): string {
        $visualElement = $this->visualElement($visualElement);

        $color = $visualElement->getColor() ?: $this->getConfig('badge.color') ?: $this->getConfig('defaults.color');
        $class = 'badge text-bg-' . $color;
        $class .= ($options['pill'] ?? $this->getConfig('badge.pill') ?? false) ? ' rounded-pill' : '';
        $class .= ' ' . ($options['class'] ?? '');

        $options = array_merge($options, [
            'class' => $class,
            'title' => $visualElement->getDescription() ?? $visualElement->getLabel() ?? null,
            'aria-label' => $visualElement->getLabel() ?? '',
        ]);

        if ($options['tooltip'] ?? $this->getConfig('defaults.tooltip.enabled') ?? false) {
            $options = $this->tooltipOptions($visualElement, $options);
            unset($options['tooltip']);
        }

        $icon = $this->renderIcon($visualElement, $options);

        return $this->formatTemplate('badge', [
            'class' => $options['class'],
            'icon' => $icon,
            'label' => $visualElement->getLabel(),
            'attrs' => $this->templater()->formatAttributes($options, ['class', 'icon', 'label']),
        ]);
    }

    /**
     * @param VisualElement|VisualElementInterface|array $visualElement
     * @param array $options
     * @return string
     */
    public function text(
        VisualElement|VisualElementInterface|array $visualElement,
        array $options = []
    ): string {
        $visualElement = $this->visualElement($visualElement);
        $color = $visualElement->getColor() ?: $this->getConfig('defaults.color'); // CHANGED

        $options = array_merge($options, [
            'class' => 'text-' . $color . ' ' . ($options['class'] ?? ''), // CHANGED: añadido $options['class']
            'title' => $visualElement->getDescription() ?? $visualElement->getLabel() ?? null,
        ]);

        if ($options['tooltip'] ?? $this->getConfig('defaults.tooltip.enabled') ?? false) {
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
     * @param VisualElement|VisualElementInterface|array $visualElement
     * @param array $options
     * @return string
     */
    public function alert(
        VisualElement|VisualElementInterface|array $visualElement,
        array $options = []
    ): string {
        $visualElement = $this->visualElement($visualElement);
        $color = $visualElement->getColor() ?: $this->getConfig('defaults.color');

        $options += ['class' => 'alert'];
        $options['class'] .= ' alert-' . $color;
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
            'attrs' => $this->templater()->formatAttributes($options, [
                'class',
                'aria-label',
                'icon',
                'label',
                'content',
                'dismissible',
                'title'
            ]),

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
     * @param VisualElement|VisualElementInterface|array $visualElement
     * @param array $options
     * @return string
     */
    public function icon(
        VisualElement|VisualElementInterface|array $visualElement,
        array $options = []
    ): string {
        $visualElement = $this->visualElement($visualElement);

        $iconName = $visualElement->getIcon() ?: ($options['icon'] ?? $this->getConfig('defaults.icon.name')); // CHANGED: Lógica de obtención de ícono
        if (empty($iconName)) {
            return '';
        }

        $color = $visualElement->getColor() ?: $this->getConfig('defaults.color'); // CHANGED

        $options += ['class' => ''];
        $options['class'] .= ' ' . $iconName;
        $options['class'] .= ' text-' . $color;
        $options['class'] = trim($options['class']);
        $options['title'] = $visualElement->getDescription() ?? $visualElement->getLabel() ?? '';

        if ($options['tooltip'] ?? $this->getConfig('defaults.tooltip.enabled') ?? false) {
            $options = $this->tooltipOptions($visualElement, $options);
            unset($options['tooltip']);
        }

        return $this->formatTemplate('icon', [
            'class' => $options['class'],
            'aria-label' => $options['title'],
            'attrs' => $this->templater()->formatAttributes($options, ['class', 'aria-label', 'icon', 'title']),
        ]);
    }

    public function link(
        VisualElement|VisualElementInterface|array $visualElement,
        array $options = []
    ): string {
        $visualElement = $this->visualElement($visualElement);
        $options += ['class' => 'text-decoration-none'];
        $options['class'] .= ' text-' . ($visualElement->getColor() ?? $this->getConfig('defaultColor') ?? 'secondary');
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

    public function button(
        VisualElement|VisualElementInterface|array $visualElement,
        array $options = []
    ): string {
        $visualElement = $this->visualElement($visualElement);
        $color = $visualElement->getColor() ?: $this->getConfig('defaults.color'); // CHANGED

        $options += ['class' => 'btn'];
        $options['class'] .= ' btn-' . $color;
        $options['title'] = $visualElement->getDescription() ?? $visualElement->getLabel() ?? '';

        if ($options['tooltip'] ?? $this->getConfig('defaults.tooltip.enabled') ?? false) {
            $options = $this->tooltipOptions($visualElement, $options);
            unset($options['tooltip']);
        }

        $icon = $this->renderIcon($visualElement, $options);
        $label = $visualElement->getLabel();
        $url = $visualElement->getUrl() ?? $options['url'] ?? '#';

        return $this->formatTemplate('button', [
            'url' => $url, // CHANGED
            'class' => $options['class'],
            'aria-label' => $options['title'],
            'icon' => $icon,
            'label' => $label,
            'attrs' => $this->templater()->formatAttributes($options, ['class', 'aria-label', 'icon', 'label', 'url', 'title']), // CHANGED: url, title excluidos
        ]);
    }

    /**
     * @param array<string> $tags Un array de strings, o podría ser array de VisualElements/arrays si se quisiera más personalización por tag.
     * @param array $options
     * @return string
     */
    public function tags(array $tags, array $options = []): string
    {
        $output = [];
        $tagColor = $options['color'] ?? $this->getConfig('defaults.color');
        $baseClass = 'badge text-bg-' . $tagColor;

        foreach ($tags as $tag) {
            $currentTagLabel = is_string($tag) ? $tag : (is_object($tag) && method_exists($tag, 'getLabel') ? $tag->getLabel() : 'tag');
            $currentTagClass = $baseClass . ' ' . ($options['class'] ?? '');

            $output[] = $this->formatTemplate('badge', [
                'class' => $currentTagClass,
                'aria-label' => $currentTagLabel,
                'icon' => '',
                'label' => $currentTagLabel,
                'attrs' => $this->templater()->formatAttributes($options, [
                    'class',
                    'aria-label',
                    'icon',
                    'label',
                    'color'
                ]),
            ]);
        }

        return implode(' ', $output);
    }

    /**
     * @param VisualElement $visualElement
     * @param array $options
     * @return array
     */
    protected function tooltipOptions(
        VisualElement $visualElement,
        array $options = []
    ): array {
        $tooltipPlacement = is_string($options['tooltip'] ?? null)
            ? $options['tooltip']
            : $this->getConfig('defaults.tooltip.placement');

        $title = $options['data-bs-title'] ?? $visualElement->getDescription() ?? $visualElement->getLabel();

        $tooltipSpecificOptions = [
            'title' => $title,
            'aria-label' => $options['aria-label'] ?? $visualElement->getLabel(),
            'data-bs-toggle' => 'tooltip',
            'data-bs-placement' => $tooltipPlacement,
            'data-bs-title' => $title,
        ];

        return array_merge($options, $tooltipSpecificOptions);
    }

    protected function renderIcon(VisualElement $visualElement, array $options): string
    {
        $iconName = $visualElement->getIcon();
        if (isset($options['icon'])) {
            $iconName = $options['icon'];
        }

        if ($iconName === false) return '';

        if (empty($iconName)) {
            $iconName = $this->getConfig('defaults.icon.name');
        }

        if (empty($iconName)) {
            return '';
        }

        return $this->getView()->Html->tag('i', '', ['class' => $iconName . ' me-1']);
    }

    /**
     * @param integer $startYear
     * @return string
     */
    public function copyrightYears(int $startYear): string
    {
        $currentYear = (int) date('Y');

        return $startYear < $currentYear
            ? $startYear . '-' . $currentYear
            : (string) $startYear;
    }



    // --- NEW METHOD --- List Group ---
    /**
     * Generates a list group item.
     *
     * @param VisualElement|VisualElementInterface|array $elementData
     * @param array $options Additional options for the list group item.
     * - `tag`: (string) HTML tag for the item ('li', 'a', 'button'). Default 'li'.
     * - `active`: (bool) Is the item active?
     * - `disabled`: (bool) Is the item disabled?
     * - `badge`: (array|VisualElement) Data for a badge within the item.
     * - `content`: (string) Additional HTML content for the item.
     * @return string
     */
    public function listGroupItem(VisualElement|VisualElementInterface|array $elementData, array $options = []): string
    {
        $element = $this->visualElement($elementData);
        $tag = $options['tag'] ?? $this->getConfig('listGroup.itemTag');
        $itemOptions = ['class' => 'list-group-item'];

        if ($element->getColor()) {
            $itemOptions['class'] .= ' list-group-item-' . $element->getColor();
        }
        if (!empty($options['active'])) {
            $itemOptions['class'] .= ' active';
            $itemOptions['aria-current'] = 'true';
        }
        if (!empty($options['disabled'])) {
            $itemOptions['class'] .= ' disabled';
            if ($tag !== 'li') $itemOptions['tabindex'] = -1;
            $itemOptions['aria-disabled'] = 'true';
        }
        if ($tag === 'a' || $tag === 'button') {
            $itemOptions['class'] .= ' list-group-item-action';
        }
        if (!empty($options['class'])) {
            $itemOptions['class'] .= ' ' . $options['class'];
        }

        $url = ($tag === 'a') ? ($element->getUrl() ?? $options['url'] ?? '#') : null;

        $badgeHtml = '';
        if (!empty($options['badge'])) {
            $badgeData = $this->visualElement($options['badge']);
            // Simple badge, could be more configurable
            $badgeHtml = $this->badge($badgeData, ['class' => 'ms-auto']); // ms-auto to push to the right
        }

        $contentHtml = $badgeHtml . ($options['content'] ?? '');
        if ($element->getDescription()) { // If VisualElement has description, append it
            $contentHtml .= $this->getView()->Html->tag('small', $element->getDescription(), ['class' => 'd-block text-muted']);
        }


        return $this->formatTemplate('listGroupItem', [
            'tag' => $tag,
            'class' => trim($itemOptions['class']),
            'url' => $url,
            'icon_html' => $this->renderIcon($element, $options),
            'label_html' => $element->getLabel(),
            'content_html' => $contentHtml,
            'attrs' => $this->templater()->formatAttributes($options, ['class', 'active', 'disabled', 'tag', 'badge', 'url', 'content']),
        ]);
    }

    /**
     * Generates a list group.
     *
     * @param array $items Array of VisualElement data or strings for list items.
     * @param array $options Additional options for the list group container.
     * - `flush`: (bool) Use flush style.
     * - `numbered`: (bool) Use numbered style.
     * - `horizontal`: (string|bool) Horizontal layout (e.g., 'sm', true for all breakpoints).
     * - `itemOptions`: (array) Default options to apply to all items.
     * - `tag`: (string) If items are links/buttons, the container might be 'div'. Default 'ul'.
     * @return string
     */
    public function listGroup(array $items, array $options = []): string
    {
        $containerTag = 'ul'; // Default for li items
        $renderedItems = [];
        $itemOptions = $options['itemOptions'] ?? [];

        // Determine if items are likely to be links/buttons to suggest div wrapper
        $hasActionableItem = false;
        if (!empty($items)) {
            $firstItemData = is_array($items[0]) ? $items[0] : (is_object($items[0]) ? $items[0]->toArray() : []);
            $firstItemTag = $itemOptions['tag'] ?? $this->getConfig('listGroup.itemTag');
            if (isset($firstItemData['url']) || $firstItemTag === 'a' || $firstItemTag === 'button') {
                $hasActionableItem = true;
            }
        }
        if ($hasActionableItem && !isset($options['tag'])) {
            $containerTag = 'div';
        }
        $containerTag = $options['tag'] ?? $containerTag;


        foreach ($items as $itemData) {
            $currentVisualElement = is_string($itemData) ? new VisualElement(0, $itemData) : $itemData;
            $renderedItems[] = $this->listGroupItem($currentVisualElement, $itemOptions);
        }

        $ulOptions = ['class' => ''];
        if (!empty($options['flush'])) {
            $ulOptions['class'] .= ' list-group-flush';
        }
        if (!empty($options['numbered'])) {
            $ulOptions['class'] .= ' list-group-numbered';
        }
        if (!empty($options['horizontal'])) {
            $ulOptions['class'] .= ' list-group-horizontal' . (is_string($options['horizontal']) ? '-' . $options['horizontal'] : '');
        }
        if (!empty($options['class'])) {
            $ulOptions['class'] .= ' ' . $options['class'];
        }
        $templateName = ($containerTag === 'div') ? 'listGroupDiv' : 'listGroup';

        return $this->formatTemplate($templateName, [
            'class' => trim($ulOptions['class']),
            'items' => implode('', $renderedItems),
            'attrs' => $this->templater()->formatAttributes($options, ['class', 'flush', 'numbered', 'horizontal', 'itemOptions', 'tag']),
        ]);
    }

    // --- NEW METHOD --- Dropdown ---
    /**
     * Generates a dropdown item.
     *
     * @param VisualElement|VisualElementInterface|array $elementData
     * @param array $options
     * - `type`: (string) 'link', 'button', 'header', 'divider'. Default 'link'.
     * - `active`: (bool)
     * - `disabled`: (bool)
     * @return string
     */
    public function dropdownItem(VisualElement|VisualElementInterface|array $elementData, array $options = []): string
    {
        $type = $options['type'] ?? 'link'; // link, button, header, divider
        $element = ($type !== 'divider') ? $this->visualElement($elementData) : null;

        $itemClass = $options['class'] ?? '';
        if (!empty($options['active'])) $itemClass .= ' active';
        if (!empty($options['disabled'])) $itemClass .= ' disabled';

        $templateName = 'dropdownItem'; // Default for link
        $vars = [
            'class' => trim($itemClass),
            'attrs' => $this->templater()->formatAttributes($options, ['class', 'active', 'disabled', 'type', 'url']),
        ];

        if ($type === 'link' && $element) {
            $vars['url'] = $element->getUrl() ?? $options['url'] ?? '#';
            $vars['icon_html'] = $this->renderIcon($element, $options);
            $vars['label'] = $element->getLabel();
        } elseif ($type === 'button' && $element) {
            $templateName = 'dropdownItemButton';
            $vars['icon_html'] = $this->renderIcon($element, $options);
            $vars['label'] = $element->getLabel();
        } elseif ($type === 'header' && $element) {
            $templateName = 'dropdownHeader';
            $vars['label'] = $element->getLabel();
        } elseif ($type === 'divider') {
            $templateName = 'dropdownDivider';
        } else {
            return ''; // Invalid type or missing element for type
        }
        return $this->formatTemplate($templateName, $vars);
    }

    /**
     * Generates a Bootstrap dropdown.
     *
     * @param VisualElement|VisualElementInterface|array $buttonData Data for the dropdown toggle button.
     * @param array $items Array of item data (VisualElement, string for label, or array for options) for dropdownItem.
     * @param array $options
     * - `id`: (string) ID for the dropdown button (auto-generated if not provided).
     * - `direction`: (string) 'up', 'start', 'end'.
     * - `align`: (string|array) Alignment of menu 'start', 'end', or responsive e.g. ['sm-start', 'lg-end'].
     * - `menuClass`: (string) Additional classes for the dropdown menu.
     * - `wrapperClass`: (string) Additional classes for the dropdown wrapper div.
     * @return string
     */
    public function dropdown(VisualElement|VisualElementInterface|array $buttonData, array $items, array $options = []): string
    {
        $buttonElement = $this->visualElement($buttonData);
        $buttonId = $options['id'] ?? 'dropdown-' . Text::uuid();

        $buttonOptions = $options['buttonOptions'] ?? [];
        $buttonOptions['class'] = ($buttonOptions['class'] ?? '') . ' dropdown-toggle';
        $buttonOptions['data-bs-toggle'] = 'dropdown';
        $buttonOptions['aria-expanded'] = 'false';
        $buttonOptions['id'] = $buttonId;
        // Ensure button color if not provided by VisualElement
        if (!$buttonElement->getColor() && empty($buttonOptions['color'])) {
            // Use a default color for the button if none is set
            $buttonOptions['class'] .= ' ' . ($this->getConfig('dropdown.buttonDefaultClass') ?? 'btn-secondary');
        }
        $buttonHtml = $this->button($buttonElement, $buttonOptions); // Uses the main button method

        $renderedItems = [];
        foreach ($items as $item) {
            if (is_string($item)) { // Simple string assumes it's a label for a link
                $renderedItems[] = $this->dropdownItem(new VisualElement(0, $item), []);
            } elseif (is_array($item) && isset($item['type']) && ($item['type'] === 'divider' || $item['type'] === 'header')) {
                // For dividers or headers passed as arrays
                $renderedItems[] = $this->dropdownItem($item['element'] ?? ($item['label'] ?? ''), ['type' => $item['type']] + ($item['options'] ?? []));
            } else { // Assumes VisualElement or array that can be cast to VisualElement
                $itemData = $item;
                $itemOpts = [];
                if (is_array($item) && isset($item['element'])) { // Support structure like ['element' => ..., 'options' => ...]
                    $itemData = $item['element'];
                    $itemOpts = $item['options'] ?? [];
                }
                $renderedItems[] = $this->dropdownItem($itemData, $itemOpts);
            }
        }

        $menuClass = 'dropdown-menu ' . ($options['menuClass'] ?? '');
        if (!empty($options['align'])) {
            if (is_array($options['align'])) {
                foreach ($options['align'] as $alignClass) $menuClass .= ' dropdown-menu-' . $alignClass;
            } else {
                $menuClass .= ' dropdown-menu-' . $options['align'];
            }
        }

        $menuHtml = $this->formatTemplate('dropdownMenu', [
            'class' => trim($menuClass),
            'buttonId' => $buttonId,
            'items' => implode('', $renderedItems),
            'attrs' => $this->templater()->formatAttributes($options, ['menuClass', 'align', 'id', 'buttonOptions', 'wrapperClass', 'direction']),
        ]);

        $wrapperClass = $options['wrapperClass'] ?? '';
        if (!empty($options['direction'])) {
            $wrapperClass .= ' drop' . $options['direction'];
        }

        return $this->formatTemplate('dropdown', [
            'wrapperClass' => trim($wrapperClass),
            'button_html' => $buttonHtml,
            'menu_html' => $menuHtml,
            'wrapperAttrs' => $this->templater()->formatAttributes($options, ['id', 'buttonOptions', 'menuClass', 'align', 'wrapperClass', 'direction']),
        ]);
    }

    // --- NEW METHOD --- Progress Bar ---
    /**
     * @param VisualElement|VisualElementInterface|array $elementData
     * - `value` in VisualElement is used for current progress.
     * - `label` in VisualElement is for text inside the bar.
     * @param array $options
     * - `min` (int, default 0)
     * - `max` (int, default 100)
     * - `striped` (bool)
     * - `animated` (bool)
     * - `showLabel` (bool)
     * - `wrapperClass` (string) for the outer div.progress
     * @return string
     */
    public function progressBar(VisualElement|VisualElementInterface|array $elementData, array $options = []): string
    {
        $element = $this->visualElement($elementData);
        $min = $options['min'] ?? 0;
        $max = $options['max'] ?? 100;
        $value = (int)$element->getValue();
        $percentage = ($max > $min) ? (($value - $min) / ($max - $min)) * 100 : 0;

        $barClass = '';
        if ($element->getColor()) {
            $barClass .= ' bg-' . $element->getColor();
        }
        if (!empty($options['striped'])) {
            $barClass .= ' progress-bar-striped';
        }
        if (!empty($options['animated'])) {
            $barClass .= ' progress-bar-animated';
        }
        if (!empty($options['class'])) {
            $barClass .= ' ' . $options['class'];
        }

        $labelHtml = ($options['showLabel'] ?? false) ? $element->getLabel() : '';

        $barHtml = $this->formatTemplate('progressBar', [
            'class' => trim($barClass),
            'percentage' => $percentage,
            'label_html' => $labelHtml,
            'attrs' => $this->templater()->formatAttributes($options, ['class', 'min', 'max', 'striped', 'animated', 'showLabel', 'wrapperClass', 'wrapper_attrs']),
        ]);

        $ariaLabel = $element->getDescription() ?: ($element->getLabel() ?: 'Progress bar');

        return $this->formatTemplate('progress', [
            'value' => $value,
            'min' => $min,
            'max' => $max,
            'ariaLabel' => $ariaLabel,
            'bar_html' => $barHtml,
            'wrapper_class' => $options['wrapperClass'] ?? '',
            'wrapper_attrs' => $this->templater()->formatAttributes($options, ['min', 'max', 'striped', 'animated', 'showLabel', 'wrapperClass', 'class']),
        ]);
    }

    // --- NEW METHOD --- Spinner ---
    /**
     * @param VisualElement|VisualElementInterface|array $elementData
     * - `label` for sr-only text.
     * - `color` for text color class.
     * @param array $options
     * - `type`: (string) 'border' or 'grow'. Default 'border'.
     * - `size`: (string) 'sm' for smaller spinner.
     * @return string
     */
    public function spinner(VisualElement|VisualElementInterface|array $elementData, array $options = []): string
    {
        $element = $this->visualElement($elementData);
        $type = $options['type'] ?? 'border';
        $typeClass = 'spinner-' . $type;

        $colorClass = $element->getColor() ? 'text-' . $element->getColor() : '';
        $sizeClass = ($options['size'] ?? null) === 'sm' ? $typeClass . '-sm' : '';

        return $this->formatTemplate('spinner', [
            'typeClass' => $typeClass,
            'colorClass' => $colorClass,
            'sizeClass' => $sizeClass,
            'label' => $element->getLabel() ?: 'Loading...',
            'attrs' => $this->templater()->formatAttributes($options, ['type', 'size', 'class']),
        ]);
    }

    // --- NEW METHOD --- Toast ---
    /**
     * @param VisualElement|VisualElementInterface|array $elementData
     * - `label`: Header title.
     * - `icon`: Header icon.
     * - `description`: Body content.
     * - `color`: Can be used for bg-color in header.
     * @param array $options
     * - `id`: (string) Toast ID (auto-generated if null).
     * - `dismissible`: (bool) Show close button. Default true.
     * - `timestamp`: (string) e.g., "11 mins ago".
     * - `headerClass`: (string)
     * - `bodyClass`: (string)
     * - `autohide`: (bool) data-bs-autohide. Default true.
     * - `delay`: (int) data-bs-delay if autohide is true. Default 5000.
     * @return string
     */
    public function toast(VisualElement|VisualElementInterface|array $elementData, array $options = []): string
    {
        $element = $this->visualElement($elementData);
        $toastId = $options['id'] ?? 'toast-' . Text::uuid();

        $toastClass = $options['class'] ?? '';
        if ($element->getColor() && empty($options['headerClass'])) { // Simple way to color header
            $options['headerClass'] = ($options['headerClass'] ?? '') . ' bg-' . $element->getColor() . ($this->isDarkColor($element->getColor()) ? ' text-white' : '');
        }


        $closeButtonHtml = '';
        if ($options['dismissible'] ?? true) {
            $closeButtonHtml = $this->getView()->Html->tag('button', '', [
                'type' => 'button',
                'class' => 'btn-close' . ($this->isDarkColor($element->getColor()) && str_contains($options['headerClass'] ?? '', 'bg-') ? ' btn-close-white' : ''),
                'data-bs-dismiss' => 'toast',
                'aria-label' => 'Close' // Consider internationalization
            ]);
        }

        $toastAttrs = [];
        $toastAttrs['data-bs-autohide'] = ($options['autohide'] ?? true) ? 'true' : 'false';
        if ($toastAttrs['data-bs-autohide'] === 'true') {
            $toastAttrs['data-bs-delay'] = (string)($options['delay'] ?? 5000);
        }


        return $this->formatTemplate('toast', [
            'id' => $toastId,
            'class' => trim($toastClass),
            'headerClass' => trim($options['headerClass'] ?? ''),
            'bodyClass' => trim($options['bodyClass'] ?? ''),
            'icon_html' => $this->renderIcon($element, ['icon' => $element->getIcon()]), // Pass explicit icon option
            'label' => $element->getLabel(),
            'timestamp' => $options['timestamp'] ?? '',
            'closeButton' => $closeButtonHtml,
            'content' => $element->getDescription(),
            'attrs' => $this->templater()->formatAttributes(array_merge($options, $toastAttrs), ['class', 'id', 'headerClass', 'bodyClass', 'timestamp', 'dismissible', 'content', 'autohide', 'delay']),
        ]);
    }

    // Helper para determinar si un color de Bootstrap es oscuro para contraste de texto
    protected function isDarkColor(string $colorName): bool
    {
        $darkColors = ['primary', 'secondary', 'success', 'danger', 'dark', 'info']; // info puede ser ambiguo
        return in_array(strtolower($colorName), $darkColors);
    }
}
