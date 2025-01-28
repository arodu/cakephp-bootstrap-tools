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
            'status' => 'disabled',
        ],
    ]);
?>

<div class="my-5">
    <?= $this->Stepper
        ->setCurrentStep($index)
        ->render() ?>
</div>


<?php
$total = 4;
?>

<div class="row">
    <div class="col">
        <div class="progress bg-light" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="height: 3em;">
            <div class="progress-bar" style="width: <?= ($index / $total) * 100 ?>%;">
                <?= __('Step {0} of {1}', $index, 4) ?>
            </div>
        </div>
    </div>
</div>