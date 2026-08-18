<?php
declare(strict_types=1);

namespace BootstrapTools\View\VisualElement;

interface VisualElementInterface
{
    /**
     * Returns a VisualElement built from the implementing element.
     *
     * @param array $options
     * @return \BootstrapTools\View\VisualElement\VisualElement
     */
    public function getVisualElement(array $options = []): VisualElement;
}
