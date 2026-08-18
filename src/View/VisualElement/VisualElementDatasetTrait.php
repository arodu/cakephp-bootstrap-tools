<?php
declare(strict_types=1);

namespace BootstrapTools\View\VisualElement;

trait VisualElementDatasetTrait
{
    /**
     * Returns the label of the element.
     *
     * @return string
     */
    abstract public function label(): string;

    /**
     * Returns the dataset of the element.
     *
     * @return array
     */
    abstract public function dataset(): array;

    /**
     * Builds a VisualElement from the dataset and given options.
     *
     * @param array $options Options overriding dataset values.
     * @return \BootstrapTools\View\VisualElement\VisualElement
     */
    public function getVisualElement(array $options = []): VisualElement
    {
        $data = $this->dataset();

        return new VisualElement(
            value: $this->value,
            label: $this->label(),
            icon: $options['icon'] ?? $data['icon'] ?? null,
            color: $options['color'] ?? $data['color'] ?? null,
            description: $options['description'] ?? $data['description'] ?? null,
            url: $options['url'] ?? $data['url'] ?? null,
        );
    }
}
