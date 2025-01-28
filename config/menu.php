<?php
/**
 * BootstrapTools CakePHP Plugin
 * 
 * @copyright 2025 Alberto Rodriguez
 * @author Alberto Rodriguez <arodu.dev@gmail.com>
 * @link https://github.com/arodu
 */

use BootstrapTools\View\Helper\MenuHelper;
use Cake\Http\ServerRequest;

return [
    'menu' => [
        [
            'label' => __('Menu'),
            'type' => MenuHelper::ITEM_TYPE_TITLE
        ],
        [
            'label' => __('Dashboard'),
            'url' => [
                'controller' => __('Projects'),
                'action' => 'index',
            ],
            'icon' => 'bi bi-grid-fill',
            'active' => function (ServerRequest $request = null) {
                return $request->getParam('controller') === 'Projects';
            }
        ],
    ],
];