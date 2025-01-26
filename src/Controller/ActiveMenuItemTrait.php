<?php

declare(strict_types=1);

namespace BootstrapTools\Controller;

use Cake\Core\Configure;

/**
 * ActiveMenuItemTrait
 */
trait ActiveMenuItemTrait
{
    /**
     * @param string $activeItem
     * @param string $menuKey
     * @return void
     */
    public function activeMenuItem(string $activeItem, string $menuKey = 'Menu')
    {
        $config = Configure::read('BootstrapTools.menu');
        $menuKey ??= $config['default'] ?? 'Menu';
        $activeItemKey = $config['activeItem'] ?? 'activeItem';

        $this->set($menuKey . '.' . $activeItemKey, $activeItem);
    }
}
