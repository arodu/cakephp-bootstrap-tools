<?php
declare(strict_types=1);
/**
 * BootstrapTools CakePHP Plugin
 * 
 * @copyright 2025 Alberto Rodriguez
 * @author Alberto Rodriguez <arodu.dev@gmail.com>
 * @link https://github.com/arodu
 */

namespace BootstrapTools\View\Helper;

use Cake\Core\Configure;
use Cake\Core\InstanceConfigTrait;
use Cake\Routing\Router;
use Cake\Utility\Hash;
use Cake\View\Helper;
use Cake\View\StringTemplateTrait;

/**
 * Menu helper
 */
class MenuHelper extends Helper
{
    use StringTemplateTrait;
    use InstanceConfigTrait;

    const ITEM_TYPE_LINK = 'link';
    const ITEM_TYPE_DIVIDER = 'divider';
    const ITEM_TYPE_TITLE = 'title';

    /**
     * Default configuration.
     *
     * @var array<string, mixed>
     */
    protected $_defaultConfig = [
        'name' => 'Menu',
        'menuClass' => 'nav',
        'dropdownClass' => 'dropdown',
        'activeClass' => 'active',
        'dropdownOpenClass' => 'dropdown-open',

        /**
         * Default icon for menu items.
         * 'defaultIcon' => [
         *      0 => 'bi bi-circle-fill',
         *      1 => 'bi bi-circle',
         *      2 => 'bi bi-record-circle-fill',
         *      'default' => 'bi bi-circle',
         * ],
         */
        'defaultIcon' => null,

        'maxLevel' => 2,

        /**
         * Class for nested items.
         */
        'templates' => [
            /**
             * Default templates for menu items.
             */
            'menuContainer' => '<ul class="{{menuClass}}">{{items}}</ul>',
            'menuItem' => '<li class="nav-item{{class}}{{dropdownClass}}"{{attrs}}>{{text}}{{children}}</li>',
            'menuItemLink' => '<a class="nav-link{{linkClass}}{{activeClass}}" aria-current="page" href="{{url}}"{{attrs}}>{{icon}}{{text}}{{append}}{{dropdownIcon}}</a>',
            'menuItemDisabled' => '<li class="nav-item{{class}}"><a class="nav-link disabled" aria-disabled="true"{{attrs}}>{{icon}}{{text}}</a></li>',
            'menuItemLinkDropdown' => '<a class="nav-link dropdown-toggle{{linkClass}}{{activeClass}}" href="{{url}}" role="button" data-bs-toggle="dropdown" aria-expanded="false"{{attrs}}>{{icon}}{{text}}</a>',
            'menuItemDivider' => '', // '<li><hr class="dropdown-divider"></li>',
            'menuItemTitle' => '', // '<li class="nav-header">{{icon}}{{text}}</li>',
            'dropdownIcon' => '', // '<i class="bi bi-chevron-right"></i>',

            /**
             * Default templates for dropdown items.
             */
            'dropdownContainer' => '<ul class="dropdown-menu">{{items}}</ul>',
            'dropdownItem' => '<li{{attrs}}>{{text}}{{children}}</li>',
            'dropdownItemLink' => '<a class="dropdown-item{{linkClass}}{{activeClass}}" href="{{url}}"{{attrs}}>{{icon}}{{text}}</a>',
            'dropdownItemDisabled' => '<li{{attrs}}><a class="dropdown-item disabled">{{icon}}{{text}}</a></li>',
            'dropdownItemLinkDropdown' => '<a class="dropdown-item{{linkClass}}{{activeClass}}" href="{{url}}"{{attrs}}>{{icon}}{{text}}</a>',
            'dropdownItemDivider' => '<li><hr class="dropdown-divider"></li>',
            'dropdownItemTitle' => '<li class="dropdown-header">{{text}}</li>',

            /**
             * Default templates for other items.
             */
            'icon' => '<i class="{{icon}} me-1"></i>',
        ],
    ];

    /**
     * @var array Keys representing the active menu item hierarchy.
     */
    protected $activeKeys = [];

    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $bstConfig = Configure::read('BootstrapTools.menu');
        $menu = $config['name'] ?? 'Menu';
        $actives = $this->getView()->get($bstConfig['key'] ?? 'activeMenuItem', []);

        if (!empty($actives[$menu])) {
            $this->activeItem($actives[$menu]);
        }
    }

    /**
     * Renders a menu based on the provided items and options.
     *
     * @param array $items The menu items to render.
     * @param array $options Configuration options to customize rendering.
     * @return string The rendered menu HTML.
     */
    public function render(array $items, array $options = []): string
    {
        $options = Hash::merge($this->getConfig(), $options);

        if (isset($options['activeItem'])) {
            $this->activeItem($options['activeItem']);
        }

        if (isset($options['templates'])) {
            $this->setTemplates($options['templates']);
        }

        return $this->formatTemplate('menuContainer', [
            'menuClass' => $options['class'] ?? $options['menuClass'] ?? null,
            'items' => $this->buildMenuItems($items, $options),
        ]);
    }

    /**
     * Renders a menu based on the provided configuration file.
     * 
     * options:
     * - configKey: The key in the configuration file to use for menu items.
     *
     * @param string $file
     * @param array $options
     * @return string
     */
    public function renderFile(string $file, array $options = []): string
    {
        Configure::load($file, 'default', false);
        $items = Configure::read($options['configKey'] ?? 'menu');

        return $this->render($items, $options);
    }

    /**
     * Sets the active menu item by specifying its hierarchical keys.
     *
     * @param string $keys Dot-separated keys representing the active item path.
     * @return void
     */
    public function activeItem(string $keys): void
    {
        $this->activeKeys = explode('.', $keys);
    }

    /**
     * Recursively builds menu items based on the given configuration.
     *
     * @param array $items The menu items to process.
     * @param array $options Configuration options for rendering.
     * @param int $level The current menu depth level (default is 0).
     * @return string The rendered menu items as HTML.
     */
    protected function buildMenuItems(array $items, array $options, int $level = 0): string
    {
        $result = '';
        foreach ($items as $key => $item) {
            if (!$this->itemShow($item)) {
                continue;
            }
            $item['key'] = (string) $key;
            $result .= $this->buildMenuItem($item, $options, $level);
        }

        return $result;
    }

    /**
     * Builds an individual menu item, including handling nested children and active states.
     *
     * @param array $item The menu item configuration.
     * @param array $options Configuration options for rendering.
     * @param int $level The current menu depth level.
     * @return string The rendered menu item as HTML.
     */
    protected function buildMenuItem(array $item, array $options, int $level): string
    {
        $item['type'] = $item['type'] ?? self::ITEM_TYPE_LINK;

        if ($this->isType($item, self::ITEM_TYPE_TITLE)) {
            return $this->renderTitle($item, $level);
        }

        if ($this->isType($item, self::ITEM_TYPE_DIVIDER)) {
            return $this->renderDivider($level);
        }

        if ($this->itemDisabled($item)) {
            return $this->renderDisabledItem($item, $options, $level);
        }

        return $this->renderMenuItem($item, $options, $level);
    }

    /**
     * Checks if a menu item is of a specific type.
     *
     * @param array $item The menu item configuration.
     * @param string $type The type to check against.
     * @return bool True if the item is of the specified type, false otherwise.
     */
    protected function isType(array $item, string $type): bool
    {
        return ($item['type'] ?? null) === $type;
    }

    /**
     * Renders a menu item title.
     *
     * @param array $item The menu item configuration.
     * @param int $level The current menu depth level.
     * @return string The rendered title HTML.
     */
    protected function renderTitle(array $item, int $level): string
    {
        $isChild = $level > 0;
        $template = $isChild ? 'dropdownItemTitle' : 'menuItemTitle';
        return $this->formatTemplate($template, [
            'text' => $item['label'],
        ]);
    }

    /**
     * Renders a menu item divider.
     *
     * @param int $level The current menu depth level.
     * @return string The rendered divider HTML.
     */
    protected function renderDivider(int $level): string
    {
        $isChild = $level > 0;
        $template = $isChild ? 'dropdownItemDivider' : 'menuItemDivider';
        return $this->formatTemplate($template, []);
    }

    /**
     * Renders a disabled menu item.
     *
     * @param array $item The menu item configuration.
     * @param array $options Configuration options for rendering.
     * @param int $level The current menu depth level.
     * @return string The rendered disabled item HTML.
     */
    protected function renderDisabledItem(array $item, array $options, int $level): string
    {
        $isChild = $level > 0;
        $template = $isChild ? 'dropdownItemDisabled' : 'menuItemDisabled';
        $item['icon'] = $this->resolveIcon($item, $options, $level);
        return $this->formatTemplate($template, [
            'text' => $item['label'] ?? null,
            'class' => $this->cssClass($item['container']['class'] ?? null),
            'icon' => !empty($item['icon']) ? $this->formatTemplate('icon', ['icon' => $item['icon']]) : null,
            'attrs' => $this->templater()->formatAttributes($item['container'] ?? [], ['url', 'label', 'icon']),
        ]);
    }

    /**
     * Renders a menu item.
     *
     * @param array $item The menu item configuration.
     * @param array $options Configuration options for rendering.
     * @param int $level The current menu depth level.
     * @return string The rendered item HTML.
     */
    protected function renderMenuItem(array $item, array $options, int $level): string
    {
        $hasChildren = !empty($item['children']);
        $isChild = $level > 0;
        $isActiveItem = $this->isActiveItem($item, $level);

        $append = $item['append'] ?? null;
        if (!empty($append) && is_callable($append)) {
            $append = $append($item, $this->getView()->getRequest());
        }

        $isActiveItem = $this->isActiveItem($item, $level);
        $item['icon'] = $this->resolveIcon($item, $options, $level);

        $itemLink = $isChild ? 'dropdownItemLink' : 'menuItemLink';
        $itemLinkNest = $isChild ? 'dropdownItemLinkDropdown' : 'menuItemLinkDropdown';
        $itemLinkTemplate = $hasChildren ? $itemLinkNest : $itemLink;
        $link = $this->formatTemplate($itemLinkTemplate, [
            'url' => Router::url($item['url'] ?? '#'),
            'icon' => !empty($item['icon']) ? $this->formatTemplate('icon', ['icon' => $item['icon']]) : null,
            'text' => $item['label'] ?? null,
            'activeClass' => $this->cssClass($isActiveItem ? $options['activeClass'] : null),
            'linkClass' => $this->cssClass($item['link'] ?? null),
            'append' => $append,
            'attrs' => $this->templater()->formatAttributes($item ?? [], ['url', 'label', 'icon', 'append', 'container', 'children', 'key', 'type', 'show', 'active', 'disabled']),
            'dropdownIcon' => $hasChildren ? $this->formatTemplate('dropdownIcon', []) : null,
        ]);

        $children = $hasChildren
            ? $this->formatTemplate('dropdownContainer', [
                'items' => $this->buildMenuItems($item['children'], $options, $level + 1),
                'dropdownOpenClass' => $this->cssClass($isActiveItem ? $options['dropdownOpenClass'] : null),
            ])
            : null;

        $containerTemplate = $isChild ? 'dropdownItem' : 'menuItem';

        return $this->formatTemplate($containerTemplate, [
            'class' => $this->cssClass($item['container']['class'] ?? null),
            'activeClass' => $this->cssClass($isActiveItem ? $options['activeClass'] : null),
            'dropdownClass' => $this->cssClass(!empty($item['children']) ? $options['dropdownClass'] : null),
            'dropdownOpenClass' => $this->cssClass($isActiveItem ? $options['dropdownOpenClass'] : null),
            'text' => $link,
            'children' => $children ?? null,
            'attrs' => $this->templater()->formatAttributes($item['container'] ?? [], ['url', 'label', 'icon', 'append', 'children']),
        ]);
    }

    /**
     * Resolves the icon for a menu item based on its configuration.
     *
     * @param array $item The menu item configuration.
     * @param array $options Configuration options for rendering.
     * @param int $level The current menu depth level.
     * @return string|null The resolved icon class, or null if none is found.
     */
    protected function resolveIcon(array $item, array $options, int $level): ?string
    {
        return $item['icon']
            ?? (is_string($options['defaultIcon']) ? $options['defaultIcon'] : null)
            ?? $options['defaultIcon'][$level]
            ?? $options['defaultIcon']['default']
            ?? null;
    }

    /**
     * Determines if a menu item should be shown based on its configuration.
     *
     * @param array $item The menu item configuration.
     * @return bool True if the item should be displayed, false otherwise.
     */
    protected function itemShow(array $item): bool
    {
        if (isset($item['show']) && $item['show'] === false) {
            return false;
        }

        if (isset($item['show']) && is_callable($item['show']) && !$item['show']($item, $this->getView()->getRequest())) {
            return false;
        }

        return true;
    }

    /**
     * Determines if a menu item should be displayed as disabled based on its configuration.
     *
     * @param array $item The menu item configuration.
     * @return bool True if the item should be disabled, false otherwise.
     */
    protected function itemDisabled(array $item): bool
    {
        if (isset($item['disabled']) && $item['disabled'] === true) {
            return true;
        }

        if (isset($item['disabled']) && is_callable($item['disabled']) && $item['disabled']($item, $this->getView()->getRequest())) {
            return true;
        }

        return false;
    }

    /**
     * Checks if a menu item is the currently active item.
     *
     * @param array $item The menu item configuration.
     * @param int $level The current menu depth level.
     * @return bool True if the item is active, false otherwise.
     */
    protected function isActiveItem(array $item, int $level): bool
    {
        if (isset($item['active']) && $item['active'] === true) {
            return true;
        }

        if (isset($item['active']) && is_callable($item['active']) && $item['active']($item, $this->getView()->getRequest())) {
            return true;
        }

        $currentActiveKey = (string) ($this->activeKeys[$level] ?? null);
        if ($currentActiveKey === ($item['key'] ?? null)) {
            return true;
        }

        return false;
    }

    /**
     * Converts a CSS class or array of classes into a properly formatted string.
     *
     * @param string|array|null $class The CSS class or array of classes.
     * @return string The formatted class string, prefixed with a space if not empty.
     */
    protected function cssClass(string|array|null $class): string
    {
        if (is_array($class)) {
            $class = implode(' ', array_filter($class));
        }

        if (empty($class)) {
            return '';
        }

        return ' ' . trim($class);
    }
}
