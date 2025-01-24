<?php

declare(strict_types=1);

namespace BootstrapTools\View\Trait;

use Cake\Cache\Cache;
use Cake\Utility\Hash;

/**
 * MenuLoaderTrait
 */
trait MenuLoaderTrait
{
    protected array $menuTemplates = [
        'Menu' => [],
        'Dropdown' => [
            'menuClass' => 'dropdown-menu',
            'dropdownClass' => 'dropdown',
            'activeClass' => 'active',
            'dropdownOpenClass' => 'dropdown-open',
            'defaultIcon' => null,
            'templates' => [
                'menuContainer' => '<ul class="{{menuClass}}">{{items}}</ul>',
                'menuItem' => '<li class="dropdown-item{{class}}{{dropdownClass}}"{{attrs}}>{{text}}{{nest}}</li>',
                'menuItemDisabled' => '<li class="dropdown-item{{class}}"><a class="dropdown-item disabled" aria-disabled="true"{{attrs}}>{{icon}}{{text}}</a></li>',
                'menuItemLink' => '<a class="dropdown-item{{linkClass}}{{activeClass}}" href="{{url}}"{{attrs}}>{{icon}}{{text}}</a>',
                'menuItemLinkNest' => '<a class="dropdown-item dropdown-toggle{{linkClass}}{{activeClass}}" href="{{url}}" role="button" data-bs-toggle="dropdown" aria-expanded="false"{{attrs}}>{{icon}}{{text}}</a>',
                'menuItemDivider' => '<li><hr class="dropdown-divider"></li>',
                'dropdownContainer' => '<ul class="dropdown-menu">{{items}}</ul>',
                'dropdownItem' => '<li{{attrs}}>{{text}}{{nest}}</li>',
                'dropdownItemDisabled' => '<li{{attrs}}>{{text}}{{nest}}</li>',
                'dropdownItemLink' => '<a class="dropdown-item{{linkClass}}{{activeClass}}" href="{{url}}"{{attrs}}>{{icon}}{{text}}</a>',
                'dropdownItemLinkNest' => '<a class="dropdown-item{{linkClass}}{{activeClass}}" href="{{url}}"{{attrs}}>{{icon}}{{text}}</a>',
                'dropdownItemDivider' => '<li><hr class="dropdown-divider"></li>',
                'icon' => '<i class="{{icon}}"></i>',
                'menuTitle' => '<li class="dropdown-header">{{icon}}{{text}}</li>',
            ],
        ],
        'Navbar' => [
            'templates' => [
                ''
            ]
        ],
        'Tabs' => [
            'menuClass' => 'nav nav-tabs',
            'dropdownClass' => 'dropdown',
            'activeClass' => 'active',
            'dropdownOpenClass' => 'dropdown-open',
            'defaultIcon' => null,
            'templates' => [
                'menuContainer' => '<ul class="{{menuClass}}">{{items}}</ul>',
                'menuItem' => '<li class="nav-item{{class}}{{dropdownClass}}"{{attrs}}>{{text}}{{nest}}</li>',
                'menuItemDisabled' => '<li class="nav-item{{class}}"><a class="nav-link disabled" aria-disabled="true"{{attrs}}>{{icon}}{{text}}</a></li>',
                'menuItemLink' => '<a class="nav-link{{linkClass}}{{activeClass}}" href="{{url}}"{{attrs}}>{{icon}}{{text}}</a>',
                'menuItemLinkNest' => '<a class="nav-link dropdown-toggle{{linkClass}}{{activeClass}}" href="{{url}}" role="button" data-bs-toggle="dropdown" aria-expanded="false"{{attrs}}>{{icon}}{{text}}</a>',
                'menuItemDivider' => '<li><hr class="dropdown-divider"></li>',
                'dropdownContainer' => '<ul class="dropdown-menu">{{items}}</ul>',
                'dropdownItem' => '<li{{attrs}}>{{text}}{{nest}}</li>',
                'dropdownItemDisabled' => '<li{{attrs}}>{{text}}{{nest}}</li>',
                'dropdownItemLink' => '<a class="dropdown-item{{linkClass}}{{activeClass}}" href="{{url}}"{{attrs}}>{{icon}}{{text}}</a>',
            ],
        ],
        'Pills' => [
            'parentTemplate' => 'Tabs',
            'menuClass' => 'nav nav-pills',
            'dropdownClass' => 'dropdown',
            'activeClass' => 'active',
            'dropdownOpenClass' => 'dropdown-open',
            'defaultIcon' => null,
            'templates' => [
                'menuContainer' => '<ul class="{{menuClass}}">{{items}}</ul>',
                'menuItem' => '<li class="nav-item{{class}}{{dropdownClass}}"{{attrs}}>{{text}}{{nest}}</li>',
                'menuItemDisabled' => '<li class="nav-item{{class}}"><a class="nav-link disabled" aria-disabled="true"{{attrs}}>{{icon}}{{text}}</a></li>',
                'menuItemLink' => '<a class="nav-link{{linkClass}}{{activeClass}}" href="{{url}}"{{attrs}}>{{icon}}{{text}}</a>',
                'menuItemLinkNest' => '<a class="nav-link dropdown-toggle{{linkClass}}{{activeClass}}" href="{{url}}" role="button" data-bs-toggle="dropdown" aria-expanded="false"{{attrs}}>{{icon}}{{text}}</a>',
                'menuItemDivider' => '<li><hr class="dropdown-divider"></li>',
                'dropdownContainer' => '<ul class="dropdown-menu">{{items}}</ul>',
                'dropdownItem' => '<li{{attrs}}>{{text}}{{nest}}</li>',
                'dropdownItemDisabled' => '<li{{attrs}}>{{text}}{{nest}}</li>',
                'dropdownItemLink' => '<a class="dropdown-item{{linkClass}}{{activeClass}}" href="{{url}}"{{attrs}}>{{icon}}{{text}}</a>',
                'dropdownItemLinkNest' => '<a class="dropdown-item{{linkClass}}{{activeClass}}" href="{{url}}"{{attrs}}>{{icon}}{{text}}</a>',
                'dropdownItemDivider' => '<li><hr class="dropdown-divider"></li>',
                'icon' => '<i class="{{icon}}"></i>',
                'menuTitle' => '<li class="nav-header">{{icon}}{{text}}</li>',
            ],
        ],
    ];

    /**
     * @param string $key
     * @param array $options
     * @return void
     */
    public function loadMenuHelper(string $key = 'Menu', array $options = []): void
    {
        $options = Cache::remember('menuOptions_' . $key, function () use ($key, $options) {
            $options['className'] ??= 'BootstrapTools.Menu';

            return $this->buildMenuOptions($key, $options ?? []);
        }, 'bootstrapToolsMenu');

        $this->loadHelper($key, $options);
    }

    /**
     * @param string $key
     * @param array $options
     * @return array
     */
    protected function buildMenuOptions(string $key, array $options): array
    {
        if (isset($this->menuTemplates[$key])) {
            $options = Hash::merge($this->menuTemplates[$key] ?? [], $options);
        }

        if (isset($options['parentTemplate']) && isset($this->menuTemplates[$options['parentTemplate']])) {
            $parentTemplate = $options['parentTemplate'];
            unset($options['parentTemplate']);
            $options = $this->buildMenuOptions($parentTemplate, $options);
        }

        return $options;
    }
}
