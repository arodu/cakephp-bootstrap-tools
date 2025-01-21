<?php
declare(strict_types=1);

namespace BootstrapTools\View\VisualElement;

interface VisualElementInterface
{
    public function getVisualElementOptions(array $options = []): VisualElementOptions;
}