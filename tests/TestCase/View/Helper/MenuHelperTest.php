<?php
declare(strict_types=1);

namespace BootstrapTools\Test\TestCase\View\Helper;

use BootstrapTools\View\Helper\MenuHelper;
use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use Cake\View\View;

/**
 * MenuHelperTest
 */
class MenuHelperTest extends TestCase
{
    private MenuHelper $Helper;

    public function setUp(): void
    {
        parent::setUp();
        $view = new View();
        $this->Helper = new MenuHelper($view);
        $this->Helper->setConfig('name', 'Menu');
    }

    public function tearDown(): void
    {
        Configure::delete('Menu');
        parent::tearDown();
    }

    public function testRenderSimpleItems(): void
    {
        $output = $this->Helper->render([
            'home' => ['label' => 'Home', 'url' => '/home'],
            'about' => ['label' => 'About', 'url' => '/about'],
        ]);

        $this->assertStringContainsString('<ul class="nav">', $output);
        $this->assertStringContainsString('class="nav-item"', $output);
        $this->assertStringContainsString('class="nav-link', $output);
        $this->assertStringContainsString('href="/home"', $output);
        $this->assertStringContainsString('>About</a>', $output);
    }

    public function testRenderMenuClassOptionOverrides(): void
    {
        $output = $this->Helper->render(
            ['a' => ['label' => 'A', 'url' => '/a']],
            ['class' => 'my-nav'],
        );

        $this->assertStringContainsString('<ul class="my-nav">', $output);
    }

    public function testRenderWithMenuClassConfig(): void
    {
        $this->Helper->setConfig('menuClass', 'navbar-nav');
        $output = $this->Helper->render(['a' => ['label' => 'A', 'url' => '/a']]);

        $this->assertStringContainsString('<ul class="navbar-nav">', $output);
    }

    public function testRenderWithChildrenBuildsDropdown(): void
    {
        $output = $this->Helper->render([
            'parent' => [
                'label' => 'Parent',
                'url' => '/parent',
                'children' => [
                    'child' => ['label' => 'Child', 'url' => '/child'],
                ],
            ],
        ]);

        $this->assertStringContainsString('dropdown-toggle', $output);
        $this->assertStringContainsString('dropdown-menu', $output);
        $this->assertStringContainsString('class="dropdown-item', $output);
        $this->assertStringContainsString('href="/child"', $output);
    }

    public function testActiveItemByKey(): void
    {
        $output = $this->Helper->render(
            [
                'parent' => [
                    'label' => 'Parent',
                    'url' => '/parent',
                    'children' => [
                        'child' => ['label' => 'Child', 'url' => '/child'],
                    ],
                ],
                'other' => ['label' => 'Other', 'url' => '/other'],
            ],
            ['activeItem' => 'parent.child'],
        );

        $this->assertStringContainsString('dropdown-toggle active', $output);
        $this->assertStringContainsString('dropdown-item active', $output);
        $this->assertStringNotContainsString('nav-link active>Child', $output);
        $this->assertStringContainsString('>Child</a>', $output);
    }

    public function testActiveItemFromViewData(): void
    {
        $this->Helper->getView()->set('Menu.activeItem', 'other');
        $this->Helper->initialize([]);

        $output = $this->Helper->render([
            'other' => ['label' => 'Other', 'url' => '/other'],
        ]);

        $this->assertStringContainsString('active', $output);
    }

    public function testItemActiveFlag(): void
    {
        $output = $this->Helper->render([
            'x' => ['label' => 'X', 'url' => '/x', 'active' => true],
        ]);

        $this->assertStringContainsString('nav-link active', $output);
    }

    public function testItemActiveCallable(): void
    {
        $output = $this->Helper->render([
            'x' => ['label' => 'X', 'url' => '/x', 'active' => fn() => true],
        ]);

        $this->assertStringContainsString('nav-link active', $output);
    }

    public function testItemShowFalseIsHidden(): void
    {
        $output = $this->Helper->render([
            'hidden' => ['label' => 'Hidden', 'url' => '/hidden', 'show' => false],
            'shown' => ['label' => 'Shown', 'url' => '/shown'],
        ]);

        $this->assertStringNotContainsString('Hidden', $output);
        $this->assertStringContainsString('Shown', $output);
    }

    public function testItemShowCallableFalseIsHidden(): void
    {
        $output = $this->Helper->render([
            'x' => ['label' => 'X', 'url' => '/x', 'show' => fn() => false],
            'y' => ['label' => 'Y', 'url' => '/y'],
        ]);

        $this->assertStringNotContainsString('>X</a>', $output);
        $this->assertStringContainsString('>Y</a>', $output);
    }

    public function testDisabledItem(): void
    {
        $output = $this->Helper->render([
            'd' => ['label' => 'Disabled', 'url' => '/d', 'disabled' => true],
        ]);

        $this->assertStringContainsString('class="nav-link disabled"', $output);
        $this->assertStringContainsString('aria-disabled="true"', $output);
    }

    public function testDisabledItemCallable(): void
    {
        $output = $this->Helper->render([
            'd' => ['label' => 'Disabled', 'url' => '/d', 'disabled' => fn() => true],
        ]);

        $this->assertStringContainsString('nav-link disabled', $output);
    }

    public function testDisabledChildUsesDropdownTemplate(): void
    {
        $output = $this->Helper->render([
            'p' => [
                'label' => 'P',
                'url' => '/p',
                'children' => [
                    'd' => ['label' => 'D', 'url' => '/d', 'disabled' => true],
                ],
            ],
        ]);

        $this->assertStringContainsString('dropdown-item disabled', $output);
    }

    public function testTitleAtRootRenderedEmptyByDefault(): void
    {
        $output = $this->Helper->render([
            ['label' => 'Title', 'type' => MenuHelper::ITEM_TYPE_TITLE],
            'a' => ['label' => 'A', 'url' => '/a'],
        ]);

        // menuItemTitle template default es ''.
        $this->assertStringContainsString('>A</a>', $output);
    }

    public function testTitleChildRendersDropdownHeader(): void
    {
        $output = $this->Helper->render([
            'p' => [
                'label' => 'P',
                'url' => '/p',
                'children' => [
                    ['label' => 'Section', 'type' => MenuHelper::ITEM_TYPE_TITLE],
                ],
            ],
        ]);

        $this->assertStringContainsString('class="dropdown-header"', $output);
        $this->assertStringContainsString('>Section</li>', $output);
    }

    public function testDividerAtRootEmptyByDefault(): void
    {
        $output = $this->Helper->render([
            'a' => ['label' => 'A', 'url' => '/a'],
            ['type' => MenuHelper::ITEM_TYPE_DIVIDER],
        ]);

        $this->assertStringNotContainsString('dropdown-divider', $output);
    }

    public function testDividerChildRendersHr(): void
    {
        $output = $this->Helper->render([
            'p' => [
                'label' => 'P',
                'url' => '/p',
                'children' => [
                    ['type' => MenuHelper::ITEM_TYPE_DIVIDER],
                    'c' => ['label' => 'C', 'url' => '/c'],
                ],
            ],
        ]);

        $this->assertStringContainsString('class="dropdown-divider"', $output);
    }

    public function testDefaultIconString(): void
    {
        $output = $this->Helper->render(
            ['a' => ['label' => 'A', 'url' => '/a']],
            ['defaultIcon' => 'bi bi-dot'],
        );

        $this->assertStringContainsString('class="bi bi-dot me-1"', $output);
    }

    public function testDefaultIconByLevel(): void
    {
        $output = $this->Helper->render(
            [
                'p' => [
                    'label' => 'P',
                    'url' => '/p',
                    'children' => [
                        'c' => ['label' => 'C', 'url' => '/c'],
                    ],
                ],
            ],
            ['defaultIcon' => [0 => 'bi bi-circle', 'default' => 'bi bi-dot']],
        );

        $this->assertStringContainsString('bi bi-circle', $output);
        $this->assertStringContainsString('bi bi-dot', $output);
    }

    public function testAppendCallable(): void
    {
        $output = $this->Helper->render([
            'a' => ['label' => 'A', 'url' => '/a', 'append' => fn($item, $request) => '<span class="badge">3</span>'],
        ]);

        $this->assertStringContainsString('class="badge">3</span>', $output);
    }

    public function testCustomTemplatesViaOptions(): void
    {
        $output = $this->Helper->render(
            ['a' => ['label' => 'A', 'url' => '/a']],
            ['templates' => ['menuContainer' => '<nav class="{{menuClass}}">{{items}}</nav>']],
        );

        $this->assertStringContainsString('<nav class="nav">', $output);
    }

    public function testContainerClassOption(): void
    {
        $output = $this->Helper->render(
            ['a' => ['label' => 'A', 'url' => '/a', 'container' => ['class' => 'first-item']]],
            ['activeClass' => 'active'],
        );

        $this->assertStringContainsString('nav-item first-item', $output);
    }

    public function testMenuFromFile(): void
    {
        $items = $this->Helper->menuFromFile();

        $this->assertNotEmpty($items);
        $this->assertSame('Menu', $items[0]['label']);
    }

    public function testRenderFile(): void
    {
        $output = $this->Helper->renderFile('BootstrapTools.menu', [
            'name' => 'Menu',
            'configKey' => 'Menu',
        ]);

        $this->assertStringContainsString('<ul class="nav">', $output);
        $this->assertStringContainsString('Home', $output);
    }
}
