<?php

declare(strict_types=1);

namespace BootstrapTools\View\VisualElement;

/**
 * VisualElement class
 */
class VisualElement
{
    private int|string $value;
    private string $label;
    private string $icon;
    private string $color;
    private string $description;

    /**
     * Constructor
     *
     * @param string $label
     * @param string $icon
     * @param string $color
     * @param string $description
     */
    public function __construct(
        int|string $value,
        string $label = '',
        string $icon = '',
        string $color = '',
        string $description = ''
    ) {
        $this->value = $value;
        $this->label = $label;
        $this->icon = $icon;
        $this->color = $color;
        $this->description = $description;
    }

    /**
     * Get value
     *
     * @return int|string
     */
    public function getValue(): int|string
    {
        return $this->value;
    }

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Get icon
     *
     * @return string
     */
    public function getIcon(): string
    {
        return $this->icon;
    }

    /**
     * Get color
     *
     * @return string
     */
    public function getColor(): string
    {
        return $this->color;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Get array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'value' => $this->getValue(),
            'label' => $this->getLabel(),
            'icon' => $this->getIcon(),
            'color' => $this->getColor(),
            'description' => $this->getDescription(),
        ];
    }
}
