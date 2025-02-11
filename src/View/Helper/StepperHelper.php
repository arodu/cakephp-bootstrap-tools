<?php

declare(strict_types=1);

namespace BootstrapTools\View\Helper;

use Cake\Utility\Hash;
use Cake\View\Helper;
use Cake\View\StringTemplateTrait;
use Cake\View\View;

/**
 * Stepper helper
 */
class StepperHelper extends Helper
{
    use StringTemplateTrait;

    /**
     * Default configuration.
     *
     * @var array<string, mixed>
     */
    protected array $_defaultConfig = [
        'cssFile' => 'BootstrapTools.bst-stepper',
        'templates' => [
            'container' => '<div class="stepper-container">{{content}}</div>',
            'list' => '<ul class="stepper">{{content}}</ul>',
            'step' => '<li class="stepper-item {{status}}">{{link}}</li>',
            'link' => '<a href="{{url}}" class="stepper-link">{{content}}</a>',
            'content' => '<div class="stepper-link-content">{{indicator}}{{label}}</div>',
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

        $this->steps[] = [
            'label' => $options['label'] ?? '',
            'url' => $options['url'] ?? '#',
            'status' => $options['status'] ?? '',
        ];

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

        $stepper = $this->templater()->format('list', ['content' => $content]);

        $output = $this->templater()->format('container', ['content' => $stepper]);

        $this->reset();

        return $output;
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
        $link = $this->templater()->format('link', [
            'url' => $item['url'],
            'content' => $this->templater()->format('content', ['indicator' => $indicator, 'label' => $label]),
        ]);

        if ($index === $this->currentStep) {
            $item['status'] = 'current';
        }

        return $this->templater()->format('step', ['status' => $item['status'], 'link' => $link]);
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
        $options = Hash::merge(['block' => true], $options);

        return $this->getView()->Html->css($this->getConfig('cssFile'), $options);
    }
}
