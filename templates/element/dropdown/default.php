<div class="dropdown<?= $config['direction'] !== 'down' ? ' drop' . $config['direction'] : '' ?>">
    <?php if ($config['split']): ?>
        <button class="btn <?= explode(' ', $config['button']['options']['class'])[0] ?>" type="button">
            <?= $config['button']['text'] ?>
        </button>
    <?php endif; ?>

    <button <?= $this->Html->templater()->formatAttributes($config['button']['options']) ?>>
        <?php if ($config['split']): ?>
            <span class="visually-hidden">Toggle Dropdown</span>
        <?php else: ?>
            <?= $config['button']['text'] ?>
        <?php endif; ?>
    </button>

    <ul class="<?= $config['menu']['class'] ?>">
        <?php foreach ($config['menu']['items'] as $item): ?>
            <?php if (isset($item['divider'])): ?>
                <li>
                    <hr class="dropdown-divider">
                </li>
            <?php elseif (isset($item['header'])): ?>
                <li>
                    <h6 class="dropdown-header"><?= $item['header'] ?></h6>
                </li>
            <?php else: ?>
                <li>
                    <?= $this->Html->link(
                        $item['text'],
                        $item['url'] ?? '#',
                        ['class' => 'dropdown-item'] + ($item['options'] ?? [])
                    ) ?>
                </li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ul>
</div>