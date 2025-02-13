<?php

declare(strict_types=1);

namespace BootstrapTools\View\Helper;

use Cake\Routing\Router;
use Cake\Utility\Hash;
use Cake\View\Helper;
use Cake\View\StringTemplateTrait;

/**
 * Stepper helper
 */
class StepperHelper extends Helper
{
    use StringTemplateTrait;

    const STATUS_CURRENT = 'current';
    const STATUS_COMPLETED = 'completed';
    const STATUS_DISABLED = 'disabled';

    /**
     * Default configuration.
     *
     * @var array<string, mixed>
     */
    protected array $_defaultConfig = [
        'cssFile' => 'BootstrapTools.bst-style.min',
        'templates' => [
            'container' => '<div class="stepper-container">{{content}}</div>',
            'step' => '<div class="stepper-item {{status}}">{{link}}</div>',
            'link' => '<a href="{{url}}" class="stepper-link">{{indicator}}{{label}}</a>',
            'indicator' => '<div class="stepper-indicator">{{content}}</div>',
            'label' => '<div class="stepper-label">{{content}}</div>',
            'icon' => '<i class="{{icon}}"></i>',
        ],
    ];

    /**
     * Steps
     *
     * @var array
     */
    protected array $steps = [];

    /**
     * Current step
     *
     * @var int
     */
    protected ?int $currentStep = null;

    /**
     * @param array $options
     * @return self
     */
    public function addItem(array $options = []): self
    {
        if (empty($options['label'])) {
            throw new \InvalidArgumentException('You must provide a label and a URL for each step');
        }

        $this->steps[] = Hash::merge([
            'label' => '',
            'url' => '#',
            'icon' => '',
            'disabled' => false,
        ], $options);

        return $this;
    }

    /**
     * @param array $items
     * @return self
     */
    public function addItems(array $items): self
    {
        foreach ($items as $key => $item) {
            $this->addItem($item, $key);
        }

        return $this;
    }

    /**
     * @param integer|null $index
     * @return self
     */
    public function currentStep(?int $index = null): self
    {
        $this->currentStep = $index;

        return $this;
    }

    /**
     * @return string
     */
    public function render(): string
    {
        $content = '';
        foreach ($this->steps as $index => $item) {
            $content .= $this->renderItem($item, $index + 1);
        }

        $output = $this->templater()->format('container', ['content' => $content]);

        $this->reset();

        //$css = $this->css();
        $css = '';

        return $output . $css;
    }

    /**
     * @param array $item
     * @param integer $index
     * @param boolean $current
     * @return string
     */
    public function renderItem(array $item, int $index): string
    {
        $label = $this->templater()->format('label', ['content' => $item['label']]);
        $icon = $item['icon'] ? $this->templater()->format('icon', ['icon' => $item['icon']]) : $index;
        $indicator = $this->templater()->format('indicator', ['content' => $icon]);

        $status = match (true) {
            isset($item['completed']) && $item['completed'] === true => self::STATUS_COMPLETED,
            isset($item['disabled']) && $item['disabled'] === true => self::STATUS_DISABLED,
            $index === $this->currentStep => self::STATUS_CURRENT,
            default => '',
        };

        $url = $item['disabled'] ? '#' : Router::url($item['url']);
        $link = $this->templater()->format('link', [
            'url' => $url,
            'indicator' => $indicator,
            'label' => $label,
        ]);

        return $this->templater()->format('step', ['status' => $status, 'link' => $link]);
    }

    /**
     * @return self
     */
    public function reset(): self
    {
        $this->steps = [];
        $this->currentStep = null;

        return $this;
    }

    /**
     * @param array $options
     * @return string|null
     */
    public function css(array $options = []): ?string
    {
        $options = Hash::merge([
            'block' => true,
            'once' => true,
            'rel' => 'stylesheet',
        ], $options);

        return $this->getView()->Html->css($this->getConfig('cssFile'), $options);
    }
}
