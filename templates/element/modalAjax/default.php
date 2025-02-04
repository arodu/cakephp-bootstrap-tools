<?php

/**
 * @var \App\View\AppView $this
 * @var string $target
 * @var string $eventJs
 * @var string|null $jsCallback
 */
$target ??= 'ajax-modal';
$script ??= 'BootstrapTools./js/modal-ajax-manager';
$jsCallback ??= null;

$modalOptions ??= [];
$dialogClasses = array_filter([
    'modal-dialog',
    $modalOptions['size'] ?? null,
    $modalOptions['scrollable'] ? 'modal-dialog-scrollable' : null,
    $modalOptions['centered'] ? 'modal-dialog-centered' : null,
    $modalOptions['dialogClasses'] ?? null
]);
?>

<div class="modal fade <?= $modalOptions['container']['class'] ?? '' ?>"
    id="<?= $target ?>"
    tabindex="-1"
    aria-hidden="true"
    <?php foreach ($modalOptions['attributes'] ?? [] as $attr => $value): ?>
    <?= "$attr='$value' " ?>
    <?php endforeach; ?>
    data-bs-config='<?= json_encode([
                        'backdrop' => $modalOptions['staticBackdrop'] ? 'static' : true,
                        'keyboard' => !$modalOptions['staticBackdrop']
                    ]) ?>'>

    <div class="<?= implode(' ', $dialogClasses) ?>">
        <div class="modal-content <?= $modalOptions['classes'] ?? '' ?>">
            <div class="modal-header">
                <h5 class="modal-title"><?= __('Loading...') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= __('Close') ?>"></button>
            </div>
            <div class="modal-body">
                <p class="card-text placeholder-glow">
                    <span class="placeholder col-4"></span>
                    <span class="placeholder col-4"></span>
                    <span class="placeholder col-6"></span>
                </p>
            </div>
        </div>
    </div>

</div>

<?= $this->Html->script($script, ['block' => true, 'once' => true]) ?>
<script>
    <?= $this->Html->scriptStart(['block' => true]) ?>
    document.addEventListener('DOMContentLoaded', () => {
        new ModalAjaxManager({
            target: "<?= $target ?>",
            csrfToken: "<?= $this->getRequest()->getAttribute('csrfToken') ?>",
            callback: <?= $jsCallback ?? 'null' ?>,
            title: "<?= ($modalOptions['title'] ?? __('Modal Form')) ?>",
        });
    });
    <?= $this->Html->scriptEnd() ?>
</script>