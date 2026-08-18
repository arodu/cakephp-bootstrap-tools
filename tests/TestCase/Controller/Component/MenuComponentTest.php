<?php
declare(strict_types=1);

namespace BootstrapTools\Test\TestCase\Controller\Component;

use BootstrapTools\Controller\Component\MenuComponent;
use Cake\Controller\ComponentRegistry;
use Cake\Controller\Controller;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;

/**
 * MenuComponentTest
 */
class MenuComponentTest extends TestCase
{
    public function testActiveItemSetsControllerViewVar(): void
    {
        $controller = new Controller(new ServerRequest());
        $registry = new ComponentRegistry($controller);
        $component = new MenuComponent($registry);

        $component->activeItem('dashboard.stats');

        $this->assertSame('dashboard.stats', $controller->viewBuilder()->getVar('Menu.activeItem'));
    }

    public function testActiveItemWithCustomMenuKey(): void
    {
        $controller = new Controller(new ServerRequest());
        $registry = new ComponentRegistry($controller);
        $component = new MenuComponent($registry);
        $component->setConfig('menuKey', 'Sidebar');

        $component->activeItem('profile');

        $this->assertSame('profile', $controller->viewBuilder()->getVar('Sidebar.activeItem'));
    }

    public function testDefaultConfig(): void
    {
        $controller = new Controller(new ServerRequest());
        $registry = new ComponentRegistry($controller);
        $component = new MenuComponent($registry);

        $this->assertSame('Menu', $component->getConfig('menuKey'));
        $this->assertSame('activeItem', $component->getConfig('activeItemKey'));
    }
}
