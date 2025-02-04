<?php

/**
 * @var \App\View\AppView $this
 * @var string $target
 * @var string $eventJs
 * @var string|null $jsCallback
 */
$target ??= 'ajax-modal';
$script ??= 'BootstrapTools./js/bst.modal-ajax-manager';
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
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden"><?= __('Loading...') ?></span>
                    </div>
                </div>
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
            title: "<?= ($modalOptions['title'] ?? __('Modal Form')) ?>",
        });
    });

    <?php if ($jsCallback): ?>
        document.addEventListener('modalAjaxResponse', (e) => {
            if (typeof this.config.callback === 'function') {
                const callback = <?= $jsCallback ?? 'null' ?>;

                callback(e, e.detail);
            }
        });
    <?php endif; ?>

    <?= $this->Html->scriptEnd() ?>
</script>