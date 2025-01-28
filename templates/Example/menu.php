<?php

/**
 * @var \App\View\AppView $this
 */

use BootstrapTools\View\Helper\MenuHelper;
use Cake\Utility\Hash;

$this->assign('title', 'Menu test');

$this->loadMenuHelper('Navbar');
$this->loadMenuHelper('Nav');
$this->loadMenuHelper('Pills');
$this->loadMenuHelper('Tabs');
$this->loadMenuHelper('Underline');

$menuItems = [
    [
        'label' => 'Menu',
        'type' => MenuHelper::ITEM_TYPE_TITLE,
    ],
    'link1' => [
        'label' => 'Link 1',
        'url' => ['action' => 'menu', '?' => ['activeItem' => 'link1']],
        'icon' => 'bi bi-link-45deg',
    ],
    'link2' => [
        'label' => 'Link 2',
        'url' => ['action' => 'menu', '?' => ['activeItem' => 'link2']],
        'append' => '<span class="badge rounded-pill bg-danger ms-2">4</span>',
    ],
    [
        'type' => MenuHelper::ITEM_TYPE_DIVIDER,
    ],
    'link3' => [
        'label' => 'Link 3',
        'url' => ['action' => 'menu', '?' => ['activeItem' => 'link3']],
        'icon' => 'bi bi-link-45deg',
        'disabled' => true,
    ],
    'link4' => [
        'label' => 'Link 4',
        'url' => ['action' => 'menu', '?' => ['activeItem' => 'link4']],
        'disabled' => true,
    ],
    'dropdown' => [
        'label' => 'Dropdown',
        'icon' => 'bi bi-link-45deg',
        'children' => [
            [
                'label' => 'Dropdown',
                'type' => MenuHelper::ITEM_TYPE_TITLE,
            ],
            'link4' => [
                'label' => 'Link 4',
                'url' => ['action' => 'menu', '?' => ['activeItem' => 'dropdown.link4']],
                'icon' => 'bi bi-link-45deg',
            ],
            'submenu' => [
                'label' => 'Link 5',
                'icon' => 'bi bi-link-45deg',
                'children' => [
                    'link5-1' => [
                        'label' => 'Link 5.1',
                        'url' => ['action' => 'menu', '?' => ['activeItem' => 'dropdown.submenu.link5-1']],
                        'icon' => 'bi bi-link-45deg',
                    ],
                    'link5-2' => [
                        'label' => 'Link 5.2',
                        'url' => ['action' => 'menu', '?' => ['activeItem' => 'dropdown.submenu.link5-2']],
                        'icon' => 'bi bi-link-45deg',
                    ],
                ],
            ],
            [
                'type' => MenuHelper::ITEM_TYPE_DIVIDER,
            ],
            'link6' => [
                'label' => 'Link 6',
                'url' => ['action' => 'menu', '?' => ['activeItem' => 'dropdown.link6']],
                'disabled' => true,
                'icon' => 'fa fa-fw fa-flag',
            ],
        ],
    ],
];

$activeItem = $this->getRequest()->getQuery('activeItem', 'link1');
?>

<?php
$config = ['activeItem' => $activeItem];
?>

<div class="row mb-4">
    <div class="col">
        <?= $this->Nav->render($menuItems, $config) ?>
    </div>
</div>

<div class="row mb-4">
    <div class="col">
        <?= $this->Pills->render($menuItems, $config) ?>
    </div>
</div>

<div class="row mb-4">
    <div class="col">
        <?= $this->Tabs->render($menuItems, $config) ?>
    </div>
</div>

<div class="row mb-4">
    <div class="col">
        <?= $this->Underline->render($menuItems, $config) ?>
    </div>
</div>

<div class="row mb-4">
    <div class="col">
        <nav class="navbar navbar-expand-lg bg-light link-dark">
            <div class="container-fluid">
                <a class="navbar-brand" href="#">Navbar</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <?= $this->Navbar->render($menuItems, Hash::merge($config, [
                        'menuClass' => 'navbar-nav me-auto',
                    ])); ?>
                    <form class="d-flex" role="search">
                        <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search">
                        <button class="btn btn-outline-success" type="submit">Search</button>
                    </form>
                </div>
            </div>
        </nav>
    </div>
</div>