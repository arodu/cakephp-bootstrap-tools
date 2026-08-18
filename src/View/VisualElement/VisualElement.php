<?php
declare(strict_types=1);

namespace BootstrapTools\View\VisualElement;

/**
 * VisualElement class
 */
class VisualElement
{
    private int|string $value;
    private ?string $label = null;
    private ?string $icon = null;
    private ?string $color = null;
    private ?string $description = null;
    private array|string|null $url = null;

    /**
     * Constructor
     *
     * @param string|int $value
     * @param string|null $label
     * @param string|null $icon
     * @param string|null $color
     * @param string|null $description
     * @param string|null $url
     */
    public function __construct(
        int|string $value,
        ?string $label = null,
        ?string $icon = null,
        ?string $color = null,
        ?string $description = null,
        array|string|null $url = null,
    ) {
        $this->value = $value;
        $this->label = $label;
        $this->icon = $icon;
        $this->color = $color;
        $this->description = $description;
        $this->url = $url;
    }

    /**
     * Get value
     *
     * @return string|int
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
    public function getLabel(): ?string
    {
        return $this->label ?? (string)$this->value;
    }

    /**
     * Get icon
     *
     * @return string
     */
    public function getIcon(): ?string
    {
        return $this->icon;
    }

    /**
     * Get color
     *
     * @return string
     */
    public function getColor(): ?string
    {
        return $this->color;
    }

    /**
     * Get description
     *
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Get URL
     *
     * @return array|string|null
     */
    public function getUrl(): array|string|null
    {
        return $this->url;
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
            'url' => $this->getUrl(),
        ];
    }
}
