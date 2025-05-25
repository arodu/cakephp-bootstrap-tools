<?php

/**
 * @var \App\View\AppView $this
 * @var array $tabs
 * @var array $config
 */

$tabs ??= [];
$target = $config['target'] ?? 'ajax-tabs';
$navOptions = $config['nav'] ?? [];
//$script = $config['script'] ?? 'BootstrapTools./js/bst-ajax-manager';
?>
<ul class="<?= $navOptions['class'] ?? 'nav nav-tabs' ?>" id="<?= $target ?>" role="tablist">
    <?php foreach ($tabs as $key => $options): ?>
        <li class="nav-item" role="presentation">
            <?php
            $keyId = '#' . $key;
            $options = array_merge(
                [
                    'class' => 'nav-link',
                    'id' => $key . '-tab',
                    'data-bs-toggle' => 'tab',
                    'data-bs-target' => $keyId,
                    'role' => 'tab',
                    'aria-controls' => $key,
                    'aria-selected' => 'true',
                    'tabindex' => '-1',
                ],
                $options
            );

            $label = $options['label'] ?? $key;
            if (is_array($label)) {
                $label = $this->Html->tag('span', $label['text'], $label['options'] ?? []);
            }

            if ($options['active'] ?? false) {
                $options['class'] = $options['class'] . ' active';
                $options['aria-selected'] = 'true';
            }

            if ($options['disabled'] ?? false) {
                $options['class'] = $options['class'] . ' disabled';
                $options['aria-disabled'] = 'true';
                $options['tabindex'] = '-1';
            }
            unset($options['label'], $options['active'], $options['disabled'], $options['url']);
            
            echo $this->Html->tag(
                'li',
                $this->Html->link($label, $keyId, $options),
                ['class' => 'nav-item', 'role' => 'presentation']
            ) ?>
        </li>
    <?php endforeach; ?>
</ul>