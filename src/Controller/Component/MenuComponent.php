<?php

declare(strict_types=1);

namespace BootstrapTools\Controller\Component;

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
        'activeItemKey' => 'activeItem',
    ];


    public function activeItem(string $activeItem)
    {
        $this->getController()->set(
            $this->getConfig('menuKey') . '.' . $this->getConfig('activeItemKey'),
            $activeItem
        );
    }
}
