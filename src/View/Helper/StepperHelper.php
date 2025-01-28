<?php

declare(strict_types=1);

namespace BootstrapTools\View\Helper;

use Cake\Routing\Router;
use Cake\View\Helper;
use Cake\View\StringTemplateTrait;

/**
 * @method self addStep(string $label, array $options = [])
 * @method self enableResponsive(bool $enable)
 */
class StepperHelper extends Helper
{
    use StringTemplateTrait;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_DISABLED = 'disabled';
    public const STATUS_CURRENT = 'current';

    protected array $steps = [];
    protected int $currentStep = 0;

    protected array $_defaultConfig = [
        'cssFile' => 'BootstrapTools.stepper',
        'containerClass' => 'stepper-circle',
        'size' => 'md',
        'responsive' => true,
        'max_visible_steps' => 5,
        'escape' => true,
        'templates' => [
            'container' => '<div class="stepper {{class}}" data-stepper>{{content}}</div>',
            'step' => '<div class="step {{status}}" data-step="{{index}}" {{attrs}}>{{link}}</div>',
            'link' => '<a href="{{url}}" {{attrs}}>{{indicator}}{{label}}</a>',
            'indicator' => '<div class="indicator" data-number="{{number}}">{{content}}</div>',
            'label' => '<div class="label">{{text}}</div>',
            'icon' => '<i class="{{icon}}"></i>'
        ],

        'defaultStatus' => self::STATUS_DISABLED,
    ];

    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setSize($this->getConfig('size'));
    }

    public function addItem(array $options = []): self
    {
        if (empty($options['label'])) {
            throw new \InvalidArgumentException('You must provide a label and a URL for each step');
        }

        $this->steps[] = [
            'label' => $options['label'] ?? '',
            'url' => $options['url'] ?? '#',
            'status' => $options['status'] ?? $this->getConfig('defaultStatus') ?? self::STATUS_ACTIVE,
            'icon' => $options['icon'] ?? null,
            'data' => $options['data'] ?? [],
        ];

        return $this;
    }

    public function addItems(array $items): self
    {
        foreach ($items as $item) {
            $this->addItem($item);
        }

        return $this;
    }

    public function setCurrentStep(int $step): self
    {
        $this->currentStep = max(0, min($step, count($this->steps) - 1));
        return $this;
    }

    public function setSize(string $size): self
    {
        $validSizes = ['sm', 'md', 'lg'];
        $this->setConfig('size', in_array($size, $validSizes) ? $size : 'md');
        return $this;
    }

    public function setIcon(string $state, string $icon): self
    {
        $this->setConfig("icons.{$state}", $icon);
        return $this;
    }

    public function useCustomTemplate(string $element, string $template): self
    {
        $this->templater()->add([$element => $template]);
        return $this;
    }

    public function render(): string
    {
        $stepsContent = [];
        $visibleSteps = array_slice($this->steps, 0, $this->getConfig('max_visible_steps'));

        foreach ($visibleSteps as $index => $step) {
            $stepsContent[] = $this->renderStep($index, $step);
        }

        return $this->templater()->format('container', [
            'class' => $this->buildStepperClasses(),
            'content' => implode('', $stepsContent)
        ]);
    }

    protected function renderStep(int $index, array $step): string
    {
        $stepNumber = $index + 1;
        $isCurrent = $stepNumber === $this->currentStep;
        $status = $step['status'] ?? $this->getConfig('defaultStatus') ?? self::STATUS_DISABLED;
        $status .= ($isCurrent ? ' ' . self::STATUS_CURRENT : '');

        $indicatorContent = $this->getIndicatorContent($step, $status, $stepNumber);

        $indicator = $this->templater()->format('indicator', [
            'number' => $stepNumber,
            'content' => $indicatorContent,
        ]);

        $label = $this->templater()->format('label', [
            'text' => $step['label'],
        ]);

        $link = $this->templater()->format('link', [
            'url' => Router::url($step['url']),
            'indicator' => $indicator,
            'label' => $label,
            'attrs' => $this->templater()->formatAttributes([
                'aria-current' => $isCurrent ? 'step' : 'false',
                'role' => 'navigation'
            ])
        ]);

        return $this->templater()->format('step', [
            'status' => $status,
            'index' => $stepNumber,
            'link' => $link,
            'attrs' => $this->renderDataAttributes($step)
        ]);
    }

    protected function buildStepperClasses(): string
    {
        $classes = [
            $this->getConfig('containerClass'),
            "size-{$this->getConfig('size')}",
            $this->getConfig('responsive') ? 'responsive' : ''
        ];

        return implode(' ', array_filter($classes));
    }

    protected function getIndicatorContent(array $step, string $status, int $stepNumber): string
    {
        //$icon = $this->getConfig("icons.{$status}");

        return match (true) {
                //!empty($icon) => $icon,
                //$status === 'completed' => $this->getConfig('icons.completed'),
            default => (string)$stepNumber
        };
    }

    protected function escape(string $value): string
    {
        return $this->getConfig('escape') ? h($value) : $value;
    }

    protected function renderDataAttributes(array $step): string
    {
        $attributes = [];
        foreach ($step['data'] as $key => $value) {
            $attributes["data-{$key}"] = $value;
        }

        return $this->templater()->formatAttributes($attributes);
    }

    public function reset(): self
    {
        $this->steps = [];
        $this->currentStep = 0;
        return $this;
    }

    public function getStepCount(): int
    {
        return count($this->steps);
    }

    public function debugSteps(): array
    {
        return $this->steps;
    }

    public function loadAssets()
    {
        $this->getView()->Html->css($this->getConfig('cssFile'), ['block' => true]);

        return $this;
    }
}
