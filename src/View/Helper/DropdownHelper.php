<?php
declare(strict_types=1);

namespace BootstrapTools\View\Helper;

use BootstrapTools\View\Dropdown\DropdownBuilder;
use BootstrapTools\View\Dropdown\DropdownBuilderInterface;
use Cake\Utility\Hash;
use Cake\View\Helper;
use InvalidArgumentException;

class DropdownHelper extends Helper
{
    /**
     * Default configuration.
     *
     * @var array<string, mixed>
     */
    protected array $_defaultConfig = [
        'builder' => DropdownBuilder::class,
        'element' => 'BootstrapTools.dropdown/default',
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
    ];

    /**
     * Creates a dropdown builder instance
     *
     * @param array $options Dropdown configuration options
     * @return \BootstrapTools\View\Dropdown\DropdownBuilderInterface
     */
    public function create(array $options = []): DropdownBuilderInterface
    {
        $config = Hash::merge($this->getConfig(), $options);
        $builderClass = $config['builder'];

        if (!is_subclass_of($builderClass, DropdownBuilderInterface::class)) {
            throw new InvalidArgumentException(sprintf(
                'The builder class "%s" must implement "%s".',
                $builderClass,
                DropdownBuilderInterface::class,
            ));
        }

        return new $builderClass($config);
    }

    /**
     * Renders the dropdown
     *
     * @param \BootstrapTools\View\Dropdown\DropdownBuilderInterface $builder Dropdown builder instance
     * @param array $options Additional rendering options
     * @return string Rendered HTML
     */
    public function render(DropdownBuilderInterface $builder, array $options = []): string
    {
        $config = Hash::merge($this->getConfig(), $builder->getOptions(), $options);
        $element = $config['element'];
        unset($config['element']);

        return $this->getView()->element($element, ['config' => $config]);
    }

    /**
     * Shortcut method to create and render a dropdown in one step
     *
     * @param array $options Dropdown configuration
     * @param array $items Dropdown menu items
     * @param array $renderOptions Additional rendering options
     * @return string Rendered HTML
     */
    public function make(array $options = [], array $items = [], array $renderOptions = []): string
    {
        $dropdown = $this->create($options);

        if (!empty($items)) {
            $dropdown->items($items);
        }

        return $this->render($dropdown, $renderOptions);
    }
}
