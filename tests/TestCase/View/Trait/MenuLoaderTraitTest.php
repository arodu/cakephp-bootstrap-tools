<?php
declare(strict_types=1);

namespace BootstrapTools\Test\TestCase\View\Trait;

use Cake\TestSuite\TestCase;

/**
 * MenuLoaderTraitTest
 */
class MenuLoaderTraitTest extends TestCase
{
    private MenuLoaderTraitStub $stub;

    public function setUp(): void
    {
        parent::setUp();
        $this->stub = new MenuLoaderTraitStub();
    }

    public function testBuildMenuOptionsWithUnknownKey(): void
    {
        $this->assertSame(
            ['name' => 'MyMenu'],
            $this->stub->buildOptions('MyMenu', ['name' => 'MyMenu']),
        );
    }

    public function testBuildMenuOptionsForMenu(): void
    {
        $options = $this->stub->buildOptions('Menu', ['name' => 'Menu']);

        $this->assertSame('Menu', $options['name']);
        $this->assertNull($options['parentTemplate']);
        $this->assertArrayNotHasKey('menuClass', $options);
    }

    public function testBuildMenuOptionsForNav(): void
    {
        $options = $this->stub->buildOptions('Nav', []);

        $this->assertSame('nav', $options['menuClass']);
        $this->assertNull($options['parentTemplate']);
    }

    public function testBuildMenuOptionsForPillsInheritsNav(): void
    {
        $options = $this->stub->buildOptions('Pills', []);

        $this->assertSame('nav nav-pills', $options['menuClass']);
        $this->assertNull($options['parentTemplate']);
    }

    public function testBuildMenuOptionsForTabsInheritsNav(): void
    {
        $options = $this->stub->buildOptions('Tabs', []);

        $this->assertSame('nav nav-tabs', $options['menuClass']);
    }

    public function testBuildMenuOptionsForUnderline(): void
    {
        $options = $this->stub->buildOptions('Underline', []);

        $this->assertSame('nav nav-underline', $options['menuClass']);
    }

    public function testBuildMenuOptionsForNavbar(): void
    {
        $options = $this->stub->buildOptions('Navbar', []);

        $this->assertSame('navbar-nav', $options['menuClass']);
    }

    public function testBuildMenuOptionsMergeCustomOptionsWins(): void
    {
        $options = $this->stub->buildOptions('Nav', ['menuClass' => 'my-custom-nav']);

        $this->assertSame('my-custom-nav', $options['menuClass']);
    }

    public function testLoadMenuHelperRegistersHelper(): void
    {
        $this->stub->loadMenuHelper('Pills');

        $this->assertArrayHasKey('Pills', $this->stub->loadedHelpers);
        $config = $this->stub->loadedHelpers['Pills'];
        $this->assertSame('Pills', $config['name']);
        $this->assertSame('BootstrapTools.Menu', $config['className']);
        $this->assertSame('nav nav-pills', $config['menuClass']);
    }

    public function testLoadMenuHelperWithCustomKey(): void
    {
        $this->stub->loadMenuHelper('Sidebar', ['menuClass' => 'sidebar-nav']);

        $config = $this->stub->loadedHelpers['Sidebar'];
        $this->assertSame('Sidebar', $config['name']);
        $this->assertSame('sidebar-nav', $config['menuClass']);
    }
}
