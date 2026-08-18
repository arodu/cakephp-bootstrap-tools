<?php
declare(strict_types=1);

namespace BootstrapTools\Test\TestCase\View\Helper;

use BootstrapTools\View\VisualElement\VisualElement;
use BootstrapTools\View\VisualElement\VisualElementInterface;

/**
 * Visual element stub implementing the interface.
 */
class VisualElementStub implements VisualElementInterface
{
    public function getVisualElement(array $options = []): VisualElement
    {
        return new VisualElement('v', 'Stub', 'bi-stub', 'success', 'Description', '/stub');
    }
}
