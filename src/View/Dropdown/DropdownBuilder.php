<?php
declare(strict_types=1);

namespace BootstrapTools\View\Dropdown;

use Cake\Core\InstanceConfigTrait;

class DropdownBuilder implements DropdownBuilderInterface
{
    use InstanceConfigTrait;

    /**
     * Default configuration options
     *
     * @var array
     */
    protected array $_defaultConfig = [
        'button' => [
            'text' => 'Dropdown',
            'options' => [
                'class' => 'btn btn-primary dropdown-toggle',
                'type' => 'button',
                'data-bs-toggle' => 'dropdown',
                'aria-expanded' => 'false',
            ],
        ],
        'split' => false,
        'direction' => 'down',
        'menu' => [
            'class' => 'dropdown-menu',
            'items' => [],
        ],
        'element' => 'BootstrapTools.dropdown/default',
    ];

    /**
     * Constructor
     *
     * @param array $config Configuration options
     */
    public function __construct(array $config = [])
    {
        $this->setConfig($config);
    }

    /**
     * Set multiple options at once
     *
     * @param array $options Dropdown configuration
     * @return $this
     */
    public function setOptions(array $options): self
    {
        $this->setConfig($options);

        return $this;
    }

    /**
     * Get current configuration
     *
     * @return array
     */
    public function getOptions(): array
    {
        return $this->getConfig();
    }

    /**
     * Configure dropdown button
     *
     * @param string $text Button text
     * @param array $options Button attributes
     * @return $this
     */
    public function button(string $text, array $options = [])
    {
        $this->setConfig('button.text', $text);

        if (!empty($options)) {
            $currentOptions = $this->getConfig('button.options') ?? [];
            $this->setConfig('button.options', array_merge($currentOptions, $options));
        }

        return $this;
    }

    /**
     * Set dropdown items
     *
     * @param array $items Menu items configuration
     * @return $this
     */
    public function items(array $items): self
    {
        $this->setConfig('menu.items', $items);

        return $this;
    }

    /**
     * Add a single item to dropdown
     *
     * @param string $text Item text
     * @param array|string|null $url Item URL
     * @param array $options Item attributes
     * @return $this
     */
    public function addItem(string $text, array|string|null $url = null, array $options = [])
    {
        $items = $this->getConfig('menu.items') ?? [];
        $items[] = compact('text', 'url', 'options');
        $this->setConfig('menu.items', $items);

        return $this;
    }

    /**
     * Set dropdown direction
     *
     * @param string $direction Direction (down|up|start|end)
     * @return $this
     */
    public function direction(string $direction)
    {
        $this->setConfig('direction', $direction);

        return $this;
    }

    /**
     * Enable split button dropdown
     *
     * @param bool $enable Enable/disable split mode
     * @return $this
     */
    public function split(bool $enable = true)
    {
        $this->setConfig('split', $enable);

        return $this;
    }

    /**
     * Configure menu options
     *
     * @param array $options Menu attributes
     * @return $this
     */
    public function menuOptions(array $options)
    {
        $currentOptions = $this->getConfig('menu') ?? [];
        $this->setConfig('menu', array_merge($currentOptions, $options));

        return $this;
    }

    /**
     * Add divider to dropdown menu
     *
     * @return $this
     */
    public function addDivider()
    {
        $items = $this->getConfig('menu.items') ?? [];
        $items[] = ['divider' => true];
        $this->setConfig('menu.items', $items);

        return $this;
    }

    /**
     * Add header to dropdown menu
     *
     * @param string $text Header text
     * @return $this
     */
    public function addHeader(string $text)
    {
        $items = $this->getConfig('menu.items') ?? [];
        $items[] = ['header' => $text];
        $this->setConfig('menu.items', $items);

        return $this;
    }
}
