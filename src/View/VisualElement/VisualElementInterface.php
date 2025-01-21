<?php
declare(strict_types=1);

namespace BootstrapTools\View\VisualElement;

interface VisualElementInterface
{
    public function getVisualElement(array $options = []): VisualElement;
}