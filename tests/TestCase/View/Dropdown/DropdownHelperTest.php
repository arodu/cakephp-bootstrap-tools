<?php
declare(strict_types=1);

namespace BootstrapTools\Test\TestCase\View\Dropdown;

use BootstrapTools\View\Dropdown\DropdownBuilder;
use BootstrapTools\View\Dropdown\DropdownBuilderInterface;
use BootstrapTools\View\Helper\DropdownHelper;
use Cake\TestSuite\TestCase;
use Cake\View\View;
use InvalidArgumentException;
use stdClass;

/**
 * DropdownHelperTest
 */
class DropdownHelperTest extends TestCase
{
    private DropdownHelper $Helper;

    public function setUp(): void
    {
        parent::setUp();
        $view = new View();
        $view->loadHelper('Html');
        $this->Helper = new DropdownHelper($view);
    }

    public function testCreateReturnsBuilder(): void
    {
        $builder = $this->Helper->create();

        $this->assertInstanceOf(DropdownBuilderInterface::class, $builder);
        $this->assertInstanceOf(DropdownBuilder::class, $builder);
        $this->assertSame('down', $builder->getOptions()['direction']);
    }

    public function testCreateMergesConfig(): void
    {
        $builder = $this->Helper->create(['split' => true]);

        $this->assertTrue($builder->getOptions()['split']);
        $this->assertSame('Dropdown', $builder->getOptions()['button']['text']);
    }

    public function testCreateWithInvalidBuilderThrows(): void
    {
        $this->Helper->setConfig('builder', stdClass::class);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must implement');
        $this->Helper->create();
    }

    public function testMakeRendersDropdown(): void
    {
        $output = $this->Helper->make(
            ['button' => ['text' => 'Menu'], 'menu' => ['class' => 'dropdown-menu']],
            [
                ['text' => 'Item One', 'url' => '/one'],
                ['text' => 'Item Two', 'url' => '/two'],
            ],
        );

        $this->assertStringContainsString('dropdown', $output);
        $this->assertStringContainsString('Menu', $output);
        $this->assertStringContainsString('>Item One</a>', $output);
    }

    public function testMakeRendersDividerAndHeader(): void
    {
        $output = $this->Helper->make(
            [],
            [
                ['text' => 'A', 'url' => '#'],
                ['divider' => true],
                ['header' => 'Section'],
            ],
        );

        $this->assertStringContainsString('dropdown-divider', $output);
        $this->assertStringContainsString('dropdown-header', $output);
        $this->assertStringContainsString('>Section</h6>', $output);
    }

    public function testRenderWithSplitButton(): void
    {
        $builder = $this->Helper->create(['split' => true, 'button' => ['text' => 'Split']]);
        $builder->addItem('Item', '/item');

        $output = $this->Helper->render($builder);

        $this->assertStringContainsString('btn ', $output);
        $this->assertStringContainsString('visually-hidden', $output);
        $this->assertStringContainsString('>Item</a>', $output);
    }

    public function testRenderFromBuilderBacksUpConfig(): void
    {
        $builder = $this->Helper->create();
        $builder->button('Custom', ['class' => 'btn btn-custom']);
        $builder->addItem('X', '/x');

        $output = $this->Helper->render($builder);

        $this->assertStringContainsString('Custom', $output);
        $this->assertStringContainsString('>X</a>', $output);
    }
}
