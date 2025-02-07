# Documentation for `MenuHelper`

The `MenuHelper` class is a utility designed to simplify the creation of dynamic menus in CakePHP applications. This documentation covers its configuration, features, and how to use it effectively.

---

## Table of Contents
- [Documentation for `MenuHelper`](#documentation-for-menuhelper)
  - [Table of Contents](#table-of-contents)
  - [Basic Usage](#basic-usage)
  - [Key Features](#key-features)
    - [URLs](#urls)
    - [Disabled Items](#disabled-items)
    - [Active Items](#active-items)
    - [Conditional Visibility (`show`)](#conditional-visibility-show)
  - [Customizing Templates](#customizing-templates)
    - [Example: Customizing the Template](#example-customizing-the-template)
    - [Default Templates](#default-templates)
  - [Example Implementation](#example-implementation)
    - [Output](#output)

---

## Basic Usage
To render a menu, define an array of menu items and call the `render` method. Example:

```php
$menu = [
    [
        'label' => 'Home',
        'url' => '/',
        'active' => true,
    ],
    [
        'label' => 'About',
        'url' => '/about',
    ],
];

echo $this->Menu->render($menu);
```

This generates an HTML menu based on the default templates and configuration.

---

## Key Features

### URLs
Menu items can include a `url` key to define their hyperlink. Use CakePHP's routing system to define URLs:

```php
$menu = [
    [
        'label' => 'Profile',
        'url' => ['controller' => 'Users', 'action' => 'profile'],
    ]
];
```

The `MenuHelper` will automatically use the `UrlHelper` to generate valid links.

---

### Disabled Items
To mark an item as disabled, set its `disabled` key to `true` or a callable function:

```php
$menu = [
    [
        'label' => 'Settings',
        'url' => '/settings',
        'disabled' => true, // Always disabled
    ],
    [
        'label' => 'Admin Panel',
        'url' => '/admin',
        'disabled' => function () use ($user) {
            return !$user->hasAdminAccess(); // Conditionally disabled
        },
    ]
];
```

Disabled items are rendered using a specific template, without clickable links.

---

### Active Items
The `active` key highlights the current active item. You can:

- Explicitly mark an item active using `true`.
- Use a callable function to dynamically decide.
- Automatically highlight based on the `activeItem()` method.

```php
$menu = [
    [
        'label' => 'Dashboard',
        'url' => '/',
        'active' => true, // Explicitly active
    ],
    [
        'label' => 'Reports',
        'url' => '/reports',
        'active' => function () {
            return isCurrentPage('/reports'); // Dynamically active
        },
    ]
];

$this->Menu->activeItem('1'); // Highlights the first item
```

---

### Conditional Visibility (`show`)
Control item visibility using the `show` key. Set it to `false` or a callable to hide items:

```php
$menu = [
    [
        'label' => 'Admin',
        'url' => '/admin',
        'show' => function () {
            return userIsAdmin(); // Visible only to admins
        },
    ]
];
```

Hidden items are not included in the rendered menu.

---

## Customizing Templates
The `MenuHelper` uses templates to define the HTML structure of menus. You can override these templates globally or per instance:

### Example: Customizing the Template

```php
$customTemplates = [
    'menuContainer' => '<div class="menu">{{items}}</div>',
    'menuItem' => '<div class="menu-item{{class}}">{{text}}</div>',
];

$this->Menu->render($menu, [
    'templates' => $customTemplates,
]);
```

### Default Templates
Default templates include:
- `menuContainer`: The wrapper for the menu.
- `menuItem`: A single menu item.
- `menuItemDisabled`: A disabled menu item.
- `dropdownContainer`: Wrapper for dropdown menus.
- `icon`: Icon template for items with icons.

Refer to the `_defaultConfig` property in the helper for the full list.

---

## Example Implementation
Below is a complete example demonstrating the use of `MenuHelper`:

```php
$menu = [
    [
        'label' => 'Home',
        'url' => '/',
        'active' => true,
    ],
    [
        'label' => 'About',
        'url' => '/about',
    ],
    [
        'label' => 'Services',
        'children' => [
            [
                'label' => 'Consulting',
                'url' => '/services/consulting',
            ],
            [
                'label' => 'Support',
                'url' => '/services/support',
            ],
        ],
    ],
    [
        'label' => 'Admin',
        'url' => '/admin',
        'disabled' => function () {
            return !userIsAdmin();
        },
    ],
];

$this->Menu->activeItem('2.0');

echo $this->Menu->render($menu, [
    'menuClass' => 'nav nav-pills',
]);
```

### Output
The above code generates a navigation menu styled with `nav-pills`. Items include a mix of active, disabled, and nested items.

---

For further customization or issues, refer to the CakePHP documentation or the source code of `MenuHelper`.

---

- [Back to top](#documentation-for-menuhelper)
- [Back to index](index.md)