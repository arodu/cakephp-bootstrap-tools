<?php
declare(strict_types=1);
/**
 * BootstrapTools CakePHP Plugin
 * 
 * @copyright 2025 Alberto Rodriguez
 * @author Alberto Rodriguez <arodu.dev@gmail.com>
 * @link https://github.com/arodu
 */

namespace BootstrapTools\View\Trait;

use Cake\Utility\Hash;

/**
 * MenuLoaderTrait
 */
trait MenuLoaderTrait
{
    protected array $menuTemplates = [
        'Menu' => [
            'name' => 'Menu',
            'parentTemplate' => null,
        ],
        'Nav' => [
            'name' => 'Nav',
            'parentTemplate' => null,
            'menuClass' => 'nav',
        ],
        'Pills' => [
            'name' => 'Pills',
            'parentTemplate' => 'Nav',
            'menuClass' => 'nav nav-pills',
        ],
        'Tabs' => [
            'name' => 'Tabs',
            'parentTemplate' => 'Nav',
            'menuClass' => 'nav nav-tabs',
        ],
        'Underline' => [
            'name' => 'Underline',
            'parentTemplate' => 'Nav',
            'menuClass' => 'nav nav-underline',
        ],
        'Navbar' => [
            'name' => 'Navbar',
            'parentTemplate' => 'Nav',
            'menuClass' => 'navbar-nav',
        ],
    ];

    /**
     * @param string $key
     * @param array $options
     * @return void
     */
    public function loadMenuHelper(string $key = 'Menu', array $options = []): void
    {
        $options['className'] ??= 'BootstrapTools.Menu';
        $options['name'] ??= $key;
        $menuOptions = $this->buildMenuOptions($key, $options ?? []);
        $this->loadHelper($key, $menuOptions);
    }

    /**
     * @param string $key
     * @param array $options
     * @return array
     */
    protected function buildMenuOptions(string $key, array $options): array
    {
        if (isset($this->menuTemplates[$key])) {
            $options = Hash::merge($this->menuTemplates[$key] ?? [], $options);
        }

        if (!empty($options['parentTemplate']) && isset($this->menuTemplates[$options['parentTemplate']])) {
            $parentTemplate = $options['parentTemplate'];
            unset($options['parentTemplate']);
            $options = $this->buildMenuOptions($parentTemplate, $options);
        }

        return $options;
    }
}