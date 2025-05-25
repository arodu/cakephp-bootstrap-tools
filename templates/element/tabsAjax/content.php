<?php

/**
 * @var \App\View\AppView $this
 * @var array $tabs
 * @var array $config
 */

$tabs ??= [];
$target = $config['target'] ?? 'ajax-tabs';
$script = $config['script'] ?? 'BootstrapTools./js/bst-ajax-manager';
?>
<div id="<?= $target ?>" class="tab-content">
    <?php foreach ($tabs as $key => $options): ?>
        <?php
        if (empty($options['url'])) {
            $options['url'] = '#';
        } elseif (is_array($options['url'])) {
            $options['url'] = $this->Url->build($options['url']);
        }
        ?>
        <?= $this->Html->tag(
            'div',
            __('Loading...'),
            [
                'id' => $key,
                'class' => 'ajax-tab-pane tab-pane fade' . ($options['active'] ?? false ? ' show active' : ''),
                'role' => 'tabpanel',
                'aria-labelledby' => $key . '-tab',
                'tabindex' => '0',
                'data-url' => $options['url'],
            ]
        );
        ?>
    <?php endforeach; ?>
</div>

<?= $this->Html->script($script, ['block' => true, 'once' => true]) ?>
<script>
    <?= $this->Html->scriptStart(['block' => true]) ?>
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        let activeTabParam = urlParams.get('tab');
        const tabPanes = document.querySelectorAll('#<?= $target ?> .ajax-tab-pane');
        const containers = {};

        tabPanes.forEach(pane => {
            const tabId = pane.id;
            const isFirstPane = !activeTabParam && pane === tabPanes[0];
            const autoLoad = activeTabParam ? tabId === activeTabParam : isFirstPane;

            containers[tabId] = new ContainerAjax(pane, {
                autoLoad: autoLoad
            });

            if (isFirstPane) {
                activeTabParam = tabId;
            }
        });

        if (activeTabParam) {
            const tabTrigger = document.querySelector(`a[href="#${activeTabParam}"]`);
            if (tabTrigger) {
                bootstrap.Tab.getOrCreateInstance(tabTrigger).show();
            } else {
                console.error('Tab trigger not found for ID:', activeTabParam);
            }
        }

        document.querySelectorAll('[data-bs-toggle="tab"]').forEach(tab => {
            tab.addEventListener('shown.bs.tab', function(e) {
                const tabId = e.target.getAttribute('href').substring(1);
                if (containers[tabId]) {
                    containers[tabId].reload();
                }
                const newUrl = new URL(window.location);
                newUrl.searchParams.set('tab', tabId);
                window.history.replaceState({}, '', newUrl);
            });
        });

        window.addEventListener('popstate', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const tabParam = urlParams.get('tab');
            if (tabParam) {
                const tabTrigger = document.querySelector(`a[href="#\${tabParam}"]`);
                if (tabTrigger) {
                    bootstrap.Tab.getOrCreateInstance(tabTrigger).show();
                }
            }
        });
    });
    <?= $this->Html->scriptEnd() ?>
</script>