<?php
declare(strict_types=1);

namespace BootstrapTools\Test\TestCase\View\Helper;

use Cake\TestSuite\TestCase;
use Cake\View\View;

/**
 * FormatOptionsTraitTest
 */
class FormatOptionsTraitTest extends TestCase
{
    private FormatOptionsStub $stub;

    public function setUp(): void
    {
        parent::setUp();
        $view = new View();
        $view->loadHelper('Html');
        $this->stub = new FormatOptionsStub($view);
    }

    public function testPlainLabelAndUrl(): void
    {
        $result = $this->stub->format(['label' => 'Go', 'url' => '/go']);

        $this->assertSame('Go', $result['label']);
        $this->assertSame('/go', $result['url']);
        $this->assertSame(['class' => 'btn'], $result['options']);
    }

    public function testLabelAndUrlOptional(): void
    {
        $result = $this->stub->format([]);

        $this->assertNull($result['label']);
        $this->assertNull($result['url']);
    }

    /**
     * @dataProvider classProvider
     */
    public function testClassConstruction(array $item, string $expectedClass): void
    {
        $result = $this->stub->format($item);

        $this->assertSame($expectedClass, $result['options']['class']);
    }

    /**
     * @return array<string, array{mixed[], string}>
     */
    public static function classProvider(): array
    {
        return [
            'color' => [['color' => 'primary'], 'btn btn-primary'],
            'outline' => [['color' => 'danger', 'outline' => true], 'btn btn-outline-danger'],
            'size' => [['size' => 'sm'], 'btn btn-sm'],
            'block' => [['block' => true], 'btn btn-block'],
            'active' => [['active' => true], 'btn active'],
            'disabled' => [['disabled' => true], 'btn disabled'],
            'class' => [['class' => 'extra-class'], 'btn extra-class'],
            'combined' => [
                ['color' => 'success', 'size' => 'lg', 'class' => 'w-100'],
                'btn btn-success btn-lg w-100',
            ],
        ];
    }

    public function testDisabledAddsAttributes(): void
    {
        $result = $this->stub->format(['disabled' => true, 'color' => 'primary']);

        $this->assertTrue($result['options']['disabled']);
        $this->assertSame('true', $result['options']['aria-disabled']);
    }

    public function testTrustedKeysRemovedFromOptions(): void
    {
        $result = $this->stub->format([
            'label' => 'X',
            'url' => '/x',
            'color' => 'dark',
            'outline' => false,
            'size' => 'sm',
            'block' => false,
            'active' => false,
            'disabled' => false,
            'icon' => 'bi-x',
            'type' => 'submit',
            'iconPosition' => 'left',
            'data-custom' => 'keep',
        ]);

        $options = $result['options'];
        $this->assertArrayNotHasKey('label', $options);
        $this->assertArrayNotHasKey('url', $options);
        $this->assertArrayNotHasKey('color', $options);
        $this->assertArrayNotHasKey('outline', $options);
        $this->assertArrayNotHasKey('size', $options);
        $this->assertArrayNotHasKey('block', $options);
        $this->assertArrayNotHasKey('active', $options);
        $this->assertArrayNotHasKey('disabled', $options);
        $this->assertArrayNotHasKey('icon', $options);
        $this->assertArrayNotHasKey('type', $options);
        $this->assertArrayNotHasKey('iconPosition', $options);
        $this->assertSame('keep', $options['data-custom']);
    }

    public function testIconLeftByDefault(): void
    {
        $result = $this->stub->format(['label' => 'Save', 'icon' => 'bi-check']);

        $this->assertStringContainsString('<i class="bi-check"></i>Save', $result['label']);
        $this->assertFalse($result['options']['escape']);
    }

    public function testIconRight(): void
    {
        $result = $this->stub->format(['label' => 'Save', 'icon' => 'bi-check', 'iconPosition' => 'right']);

        $this->assertStringContainsString('Save<i class="bi-check"></i>', $result['label']);
    }

    public function testNoIconLeavesLabelUntouched(): void
    {
        $result = $this->stub->format(['label' => 'Plain']);

        $this->assertSame('Plain', $result['label']);
        $this->assertArrayNotHasKey('escape', $result['options']);
    }
}
