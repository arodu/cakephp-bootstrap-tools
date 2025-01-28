<?php
declare(strict_types=1);

namespace BootstrapTools\View\Helper;

use Cake\Event\EventInterface;
use Cake\Routing\Router;
use Cake\View\Helper;
use Cake\View\StringTemplateTrait;

/**
 * @method self addStep(string $label, array $options = [])
 * @method self setTheme(string $theme)
 * @method self enableResponsive(bool $enable)
 */
class StepperHelper extends Helper
{
    use StringTemplateTrait;

    protected array $steps = [];
    protected array $callbacks = [];
    protected int $currentStep = 0;

    protected array $_defaultConfig = [
        'cssFile' => 'BootstrapTools.stepper',
        'style' => 'circle',
        'size' => 'md',
        'responsive' => true,
        'max_visible_steps' => 5,
        'escape' => true,
        'icons' => [
            'completed' => '✓',
            'active' => '',
            'disabled' => '✗'
        ],
        'templates' => [
            'container' => '<div class="stepper {{class}}" data-stepper>{{content}}</div>',
            'step' => '<div class="step {{status}}" data-step="{{index}}" {{attrs}}>{{link}}</div>',
            'link' => '<a href="{{url}}" {{attrs}}>{{indicator}}{{label}}</a>',
            'indicator' => '<div class="indicator" data-number="{{number}}">{{content}}</div>',
            'label' => '<div class="label">{{text}}</div>',
        ],
        'theme' => [
            'circle' => 'circle-stepper',
            'modern' => 'modern-stepper'
        ],
    ];

    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setSize($this->getConfig('size'));
    }

    public function addItem(string $label, array $options = []): self
    {
        $this->steps[] = $this->parseStep($label, $options);
        return $this;
    }

    public function addItems(array $items): self
    {
        foreach ($items as $item) {
            $this->addItem($item['label'], $item);
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

    public function addStepCallback(int $step, callable $callback): self
    {
        $this->callbacks[$step] = $callback;
        return $this;
    }

    public function render(): string
    {
        $stepsContent = [];
        $totalSteps = count($this->steps);
        $visibleSteps = array_slice($this->steps, 0, $this->getConfig('max_visible_steps'));

        foreach ($visibleSteps as $index => $step) {
            $stepsContent[] = $this->renderStep($index, $step, $totalSteps);
        }

        return $this->templater()->format('container', [
            'class' => $this->buildStepperClasses(),
            'content' => implode('', $stepsContent)
        ]);
    }

    protected function renderStep(int $index, array $step, int $totalSteps): string
    {
        $stepNumber = $index + 1;
        $isActive = $stepNumber === $this->currentStep;
        $status = $this->validateStatus($step['status']);

        $indicatorContent = $this->getIndicatorContent($status, $stepNumber);

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
                'aria-current' => $isActive ? 'step' : 'false',
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

    protected function parseStep(string $label, array $options): array
    {
        return [
            'label' => $label,
            'url' => $options['url'] ?? '#',
            'status' => $this->validateStatus($options['status'] ?? 'disabled'),
            'data' => $options['data'] ?? []
        ];
    }

    protected function validateStatus(string $status): string
    {
        $statusMap = [
            'active' => ['current', 'active', 'activo'],
            'completed' => ['done', 'complete', 'completado'],
            'disabled' => ['disabled', 'inactive', 'inactivo']
        ];

        foreach ($statusMap as $validStatus => $aliases) {
            if (in_array(strtolower($status), array_map('strtolower', $aliases))) {
                return $validStatus;
            }
        }

        return 'disabled';
    }

    protected function buildStepperClasses(): string
    {
        $classes = [
            $this->getConfig("theme.{$this->getConfig('style')}"),
            "size-{$this->getConfig('size')}",
            $this->getConfig('responsive') ? 'responsive' : ''
        ];

        return implode(' ', array_filter($classes));
    }

    protected function getIndicatorContent(string $status, int $stepNumber): string
    {
        $icon = $this->getConfig("icons.{$status}");

        return match (true) {
            !empty($icon) => $icon,
            $status === 'completed' => $this->getConfig('icons.completed'),
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
        $this->callbacks = [];
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
