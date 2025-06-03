# Documentación para DropdownHelper de BootstrapTools

## Introducción
El `DropdownHelper` permite crear dropdowns de Bootstrap 5 mediante una API fluida en vistas de CakePHP. Proporciona métodos para construir dropdowns complejos manteniendo un código limpio y mantenible.

## Métodos principales

### `create(array $options = [])`
Crea una instancia del constructor de dropdowns.

```php
$dropdown = $this->Dropdown->create();
```

### `render(DropdownBuilderInterface $builder, array $options = [])`
Renderiza un dropdown a partir de un constructor.

```php
echo $this->Dropdown->render($dropdown);
```

### `make(array $options = [], array $items = [], array $renderOptions = [])`
Crea y renderiza un dropdown en un solo paso.

```php
echo $this->Dropdown->make($options, $items);
```

## Ejemplos de uso

### Ejemplo mínimo
```php
echo $this->Dropdown->make();
```

Esto generará:
- Botón con texto "Dropdown"
- Menú vacío (sin items)
- Con clases Bootstrap por defecto

### Dropdown básico con items
```php
echo $this->Dropdown->make(
    ['button' => ['text' => 'Acciones']],
    [
        ['text' => 'Editar', 'url' => ['action' => 'edit', $id]],
        ['text' => 'Eliminar', 'url' => ['action' => 'delete', $id]],
        ['divider' => true],
        ['text' => 'Reporte', 'url' => ['action' => 'report', $id]],
    ]
);
```

### Dropdown con constructor avanzado
```php
$dropdown = $this->Dropdown->create([
    'button' => [
        'text' => 'Opciones',
        'options' => ['class' => 'btn btn-info']
    ],
    'split' => true,
    'direction' => 'end'
]);

$dropdown->addItem('Ver detalle', ['action' => 'view', $id])
    ->addItem('Editar', ['action' => 'edit', $id])
    ->addDivider()
    ->addHeader('Administración')
    ->addItem('Eliminar', ['action' => 'delete', $id], ['class' => 'text-danger']);

echo $this->Dropdown->render($dropdown);
```

### Dropdown con items dinámicos
```php
$items = [];
$items[] = ['text' => 'Crear nuevo', 'url' => ['action' => 'add']];

if ($hasSelection) {
    $items[] = ['divider' => true];
    $items[] = ['text' => 'Exportar selección', 'url' => ['action' => 'export']];
}

echo $this->Dropdown->make(
    ['button' => ['text' => 'Herramientas']],
    $items
);
```

## Opciones de configuración

### Configuración principal
```php
$options = [
    'button' => [
        'text' => 'Mi Dropdown',     // Texto del botón
        'options' => [               // Atributos HTML del botón
            'class' => 'btn-custom',
            'id' => 'my-dropdown'
        ]
    ],
    'split' => true,                 // Habilitar botón dividido
    'direction' => 'up',             // Dirección (down|up|start|end)
    'menu' => [
        'class' => 'dropdown-menu-custom', // Clase CSS adicional para el menú
        'items' => []                // Items predefinidos
    ],
    'element' => 'custom/dropdown'   // Elemento de vista personalizado
];
```

### Estructura de items
Cada item puede ser:
```php
// Enlace simple
[
    'text' => 'Texto del enlace',
    'url' => ['controller' => 'Users', 'action' => 'edit'],
    'options' => ['class' => 'custom-class']
]

// Divisor
['divider' => true]

// Encabezado
['header' => 'Título de sección']
```

## Personalización avanzada

### Crear elemento de vista personalizado
1. Crea un archivo en `templates/element/BootstrapTools/Dropdown/custom.php`
```php
<div class="dropdown custom-dropdown">
    <button <?= $this->Html->templater()->formatAttributes($config['button']['options']) ?>>
        <?= $config['button']['text'] ?>
    </button>
    
    <div class="custom-menu">
        <?php foreach ($config['menu']['items'] as $item): ?>
            <!-- Personaliza la renderización aquí -->
        <?php endforeach; ?>
    </div>
</div>
```

2. Usa el elemento personalizado:
```php
echo $this->Dropdown->make([
    'element' => 'BootstrapTools.Dropdown/custom'
]);
```

### Extender el builder
1. Crea un builder personalizado:
```php
namespace App\View\Dropdown;

use BootstrapTools\View\Dropdown\DropdownBuilder;

class CustomDropdownBuilder extends DropdownBuilder
{
    public function addCustomItem(string $text, string $icon)
    {
        $html = '<i class="' . $icon . '"></i> ' . h($text);
        return $this->addItem($html, '#', ['escape' => false]);
    }
}
```

2. Configura el helper:
```php
// En AppView.php
$this->loadHelper('BootstrapTools.Dropdown', [
    'builder' => \App\View\Dropdown\CustomDropdownBuilder::class
]);
```

3. Usa el nuevo método:
```php
echo $this->Dropdown->make([], [
    ['custom' => ['text' => 'Usuario', 'icon' => 'bi bi-person']]
]);
```

## Mejores prácticas
1. Para dropdowns simples, usa `make()` para código más conciso
2. Para dropdowns complejos o dinámicos, usa el patrón create-render
3. Agrupa items relacionados con divisores
4. Usa encabezados para secciones grandes
5. Personaliza elementos de vista para necesidades específicas en lugar de modificar el helper directamente