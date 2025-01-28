<?php

/**
 * @var \App\View\AppView $this
 * @property \BootstrapTools\View\Helper\StepperHelper $Stepper
 */


$this->loadHelper('BootstrapTools.Stepper');
$this->Stepper
    ->loadAssets()
    ->setConfig('defaultStatus', 'active')
    ->addItems([
        [
            'label' => __('Step 1'),
            'url' => ['action' => 'stepper', 1],
        ],
        [
            'label' => __('Step 2'),
            'url' => ['action' => 'stepper', 2],
        ],
        [
            'label' => __('Step 3'),
            'url' => ['action' => 'stepper', 3],
        ],
        [
            'label' => __('Step 4'),
            'url' => ['action' => 'stepper', 4],
        ],
    ]);
?>

<div class="my-5">
    <?= $this->Stepper
        ->setCurrentStep($index)
        ->render() ?>
</div>