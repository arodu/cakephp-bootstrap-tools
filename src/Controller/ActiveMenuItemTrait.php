<?php
declare(strict_types=1);
/**
 * BootstrapTools CakePHP Plugin
 * 
 * @copyright 2025 Alberto Rodriguez
 * @author Alberto Rodriguez <arodu.dev@gmail.com>
 * @link https://github.com/arodu
 */

namespace BootstrapTools\Controller;

use Cake\Core\Configure;

/**
 * ActiveMenuItemTrait
 */
trait ActiveMenuItemTrait
{
    protected string $defaultMenuKey = null;

    /**
     * @param string $activeItem
     * @param string $menuKey
     * @return void
     */
    public function activeMenuItem(string $activeItem, string $menuKey = null): void
    {
        $config = Configure::read('BootstrapTools.menu');
        $menuKey ??= $this->defaultMenuKey ?? $config['default'] ?? 'Menu';
        $activeItemKey = $config['activeItem'] ?? 'activeItem';

        $this->set($menuKey . '.' . $activeItemKey, $activeItem);
    }
}
