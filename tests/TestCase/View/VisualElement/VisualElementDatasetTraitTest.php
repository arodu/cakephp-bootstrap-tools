<?php
declare(strict_types=1);

namespace BootstrapTools\Test\TestCase\View\VisualElement;

use BootstrapTools\View\VisualElement\VisualElement;
use Cake\TestSuite\TestCase;

/**
 * VisualElementDatasetTraitTest
 */
class VisualElementDatasetTraitTest extends TestCase
{
    public function testGetVisualElementFromDataset(): void
    {
        $stub = new DatasetStub(9);
        $element = $stub->getVisualElement();

        $this->assertInstanceOf(VisualElement::class, $element);
        $this->assertSame(9, $element->getValue());
        $this->assertSame('Stub label', $element->getLabel());
        $this->assertSame('bi-stub', $element->getIcon());
        $this->assertSame('dark', $element->getColor());
        $this->assertSame('dataset desc', $element->getDescription());
        $this->assertSame('/stub', $element->getUrl());
    }

    public function testGetVisualElementOptionsOverrideDataset(): void
    {
        $stub = new DatasetStub(9);
        $element = $stub->getVisualElement([
            'icon' => 'bi-custom',
            'color' => 'success',
            'description' => 'custom desc',
            'url' => '/custom',
        ]);

        $this->assertSame('bi-custom', $element->getIcon());
        $this->assertSame('success', $element->getColor());
        $this->assertSame('custom desc', $element->getDescription());
        $this->assertSame('/custom', $element->getUrl());
    }

    public function testGetVisualElementNullOptionFallsBackToDataset(): void
    {
        $stub = new DatasetStub(1);
        $element = $stub->getVisualElement(['url' => null]);

        $this->assertSame('/stub', $element->getUrl());
    }
}
