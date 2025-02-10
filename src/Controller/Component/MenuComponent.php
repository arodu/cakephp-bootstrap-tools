<?php

declare(strict_types=1);

namespace BootstrapTools\Controller\Component;

use BootstrapTools\View\Helper\MenuHelper;
use Cake\Controller\Component;

/**
 * Menu component
 */
class MenuComponent extends Component
{
    /**
     * Default configuration.
     *
     * @var array<string, mixed>
     */
    protected array $_defaultConfig = [
        'menuKey' => 'Menu',
        'activeItemKey' => MenuHelper::ACTIVE_ITEM_KEY,
    ];


    public function activeItem(string $activeItem)
    {
        $this->getController()->set(
            $this->getConfig('menuKey') . '.' . $this->getConfig('activeItemKey'),
            $activeItem
        );
    }
}
