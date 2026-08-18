<?php
declare(strict_types=1);

namespace BootstrapTools\Test\TestCase\View\VisualElement;

use BootstrapTools\View\VisualElement\VisualElement;
use Cake\TestSuite\TestCase;

/**
 * VisualElementTest
 */
class VisualElementTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $element = new VisualElement(42, 'Label', 'bi-eye', 'primary', 'A description', ['action' => 'view', 42]);

        $this->assertSame(42, $element->getValue());
        $this->assertSame('Label', $element->getLabel());
        $this->assertSame('bi-eye', $element->getIcon());
        $this->assertSame('primary', $element->getColor());
        $this->assertSame('A description', $element->getDescription());
        $this->assertSame(['action' => 'view', 42], $element->getUrl());
    }

    public function testConstructorWithDefaultArguments(): void
    {
        $element = new VisualElement('id');

        $this->assertSame('id', $element->getValue());
        $this->assertNull($element->getIcon());
        $this->assertNull($element->getColor());
        $this->assertNull($element->getDescription());
        $this->assertNull($element->getUrl());
    }

    public function testStringValueAccepted(): void
    {
        $element = new VisualElement('abc');

        $this->assertSame('abc', $element->getValue());
    }

    public function testGetLabelFallsBackToValueAsString(): void
    {
        $element = new VisualElement(7);

        $this->assertSame('7', $element->getLabel());
    }

    public function testGetLabelWithExplicitEmptyString(): void
    {
        $element = new VisualElement(7, '');

        $this->assertSame('', $element->getLabel());
    }

    public function testToArray(): void
    {
        $element = new VisualElement(1, 'One', 'bi-1', 'success', 'desc', '/one');

        $this->assertSame([
            'value' => 1,
            'label' => 'One',
            'icon' => 'bi-1',
            'color' => 'success',
            'description' => 'desc',
            'url' => '/one',
        ], $element->toArray());
    }
}
