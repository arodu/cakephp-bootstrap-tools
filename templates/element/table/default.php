<?php

/**
 * @var \App\View\AppView $this
 */

$columns ??= [];
$emptyMessage = $emptyMessage ?? __('No records found');
$tableActions ??= [];
$tableActions['formatter'] ??= null;
$pagination ??= false;
$data ??= [];
$class ??= 'table';
$header ??= true;

$totalColumns = count($columns) + (!empty($rowActions) ? 1 : 0);

if (is_object($data) && method_exists($data, 'toArray')) {
    $data = $data->toArray();
}
?>
<?php if ($header ?? true): ?>
    <div class="row">
        <div class="col d-flex align-items-center justify-content-between mb-3">
            <h4 class="mb-3"><?= h($title ?? '') ?></h4>
            <?php if (!empty($tableActions) && is_callable($tableActions['formatter'])): ?>
                <div class="ms-auto d-flex gap-1">
                    <?= $tableActions['formatter']() ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<div class="row">
    <div class="table-responsive col-12">
        <table class="<?= $class ?? 'table' ?>">
            <thead>
                <tr>
                    <?php foreach ($columns as $key => $column): ?>
                        <?php if ($column['sortable'] ?? false): ?>
                            <th><?= $this->Paginator->sort($key, $column['label']) ?></th>
                        <?php else: ?>
                            <th><?= $column['label'] ?></th>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <?php if (!empty($rowActions)): ?>
                        <th class="actions"><?= h($rowActions['label'] ?? __('Actions')) ?></th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($data)): ?>
                    <tr>
                        <td colspan="<?= $totalColumns ?>" class="text-muted text-center">
                            <?= h($emptyMessage) ?>
                        </td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($data as $item): ?>
                        <tr>
                            <?php foreach ($columns as $key => $column): ?>
                                <td class="<?= $column['class'] ?? '' ?>">
                                    <?php
                                    if (isset($column['formatter']) && is_callable($column['formatter'])) {
                                        echo $column['formatter']($item->{$key}, $item);
                                    } else {
                                        echo h($item->{$key});
                                    }
                                    ?>
                                </td>
                            <?php endforeach; ?>
                            <?php if (!empty($rowActions) && is_callable($rowActions['formatter'])): ?>
                                <td class="actions">
                                    <div class="d-flex gap-1">
                                        <?= $rowActions['formatter']($item) ?>
                                    </div>
                                </td>
                            <?php endif ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($pagination ?? false) : ?>
    <div class="row">
        <div class="col-md-6 text-muted"><?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?></div>
        <div class="col-md-6 mt-1 mt-md-0 d-flex">
            <ul class="pagination pagination-sm pagination-primary ms-md-auto mb-0">
                <?= $this->Paginator->first('<< ' . __('First')) ?>
                <?= $this->Paginator->prev('< ' . __('Previous')) ?>
                <?= $this->Paginator->numbers() ?>
                <?= $this->Paginator->next(__('Next') . ' >') ?>
                <?= $this->Paginator->last(__('Last') . ' >>') ?>
            </ul>
        </div>
    </div>
<?php endif; ?>