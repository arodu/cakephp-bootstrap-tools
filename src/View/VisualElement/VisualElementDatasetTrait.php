<?php

declare(strict_types=1);

namespace BootstrapTools\View\VisualElement;

trait VisualElementDatasetTrait
{
    abstract public function label(): string;

    abstract public function dataset(): array;

    public function getVisualElement(array $options = []): VisualElement
    {
        $data = $this->dataset();

        return new VisualElement(
            value: $this->value,
            label: $this->label(),
            icon: $options['icon'] ?? $data['icon'] ?? null,
            color: $options['color'] ?? $data['color'] ?? null,
            description: $options['description'] ?? $data['description'],
        );
    }
}
