<?php

declare(strict_types=1);

namespace BootstrapTools\View\VisualElement;

/**
 * VisualElement class
 */
class VisualElement
{
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
        string $label = '',
        string $icon = '',
        string $color = '',
        string $description = ''
    ) {
        $this->label = $label;
        $this->icon = $icon;
        $this->color = $color;
        $this->description = $description;
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
            'label' => $this->getLabel(),
            'icon' => $this->getIcon(),
            'color' => $this->getColor(),
            'description' => $this->getDescription(),
        ];
    }
}
