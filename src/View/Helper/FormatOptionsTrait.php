<?php
declare(strict_types=1);

namespace BootstrapTools\View\Helper;

trait FormatOptionsTrait
{
    /**
     * @param array $item
     * @return array
     */
    protected function formatOptions(array $item): array
    {
        $label = $item['label'] ?? null;
        $url = $item['url'] ?? null;

        $keysToRemove = ['label', 'url', 'color', 'outline', 'size', 'block', 'active', 'disabled', 'icon', 'type'];
        $options = array_diff_key($item, array_flip($keysToRemove));

        $classes = ['btn'];
        if (!empty($item['color']) && !($item['outline'] ?? false)) {
            $classes[] = 'btn-' . $item['color'];
        }
        if (!empty($item['outline']) && !empty($item['color'])) {
            $classes[] = 'btn-outline-' . $item['color'];
        }
        if (!empty($item['size'])) {
            $classes[] = 'btn-' . $item['size'];
        }
        if (!empty($item['block'])) {
            $classes[] = 'btn-block';
        }
        if (!empty($item['active'])) {
            $classes[] = 'active';
        }
        if (!empty($item['disabled'])) {
            $classes[] = 'disabled';
            $options['disabled'] = true;
            $options['aria-disabled'] = 'true';
        }
        $options['class'] = implode(' ', $classes);

        if (!empty($item['icon'])) {
            $label = $this->Html->tag('i', '', ['class' => $item['icon']]) . $label;
            $options['escape'] = false;
        }

        return compact('label', 'url', 'options');
    }
}