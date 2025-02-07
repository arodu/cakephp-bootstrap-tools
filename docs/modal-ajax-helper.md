# ModalAjaxHelper Documentation

CakePHP View Helper for creating Bootstrap modals with AJAX content loading capabilities.

## 📦 Installation
Ensure helper is loaded in your AppView:
```php
// src/View/AppView.php
$this->loadHelper('BootstrapTools.ModalAjax');
```

## ⚙️ Configuration Options
Set in controller or view:
```php
$this->ModalAjax->setConfig([
    'target' => 'custom-modal-id',  // Default modal container ID
    'element' => 'custom_element',  // Custom modal template
    'script' => 'path/to/manager.js', // JS manager path
    'closeOnSuccess' => true,       // Auto-close after successful AJAX submit
    'modalOptions' => [
        'size' => 'modal-lg',       // Bootstrap modal size
        'scrollable' => true,
        'centered' => true,
        'classes' => 'extra-class',
        'dialogClasses' => 'dialog-class',
        'attributes' => ['data-foo' => 'bar']
    ]
]);
```

## 🎯 Methods

### `link()`
Create modal trigger links:
```php
echo $this->ModalAjax->link(
    '<i class="fas fa-edit"></i> Edit',  // HTML content
    ['action' => 'edit', 123],          // URL to load
    [
        'target' => 'custom-modal-id',    // Custom target ID
        'class' => 'btn btn-primary',
    ]
);
```

### `render()`
Render modal containers (place in layout/view):
```php
echo $this->ModalAjax->render([
    'modalOptions' => [
        'classes' => 'shadow-lg'
    ]
]);
```

### `renderItem()`
Render individual modal:
```php
echo $this->ModalAjax->renderItem('custom-modal-id', [
    'title' => __('Modal Title'),
    'modalOptions' => [
        'size' => 'modal-lg',
        'scrollable' => true,
        'centered' => true,
        'classes' => 'extra-class',
    ]
]);
```

## 🔧 Generated HTML Structure
```html
<!-- Modal trigger -->
<a href="#" 
   class="ajax-modal" 
   data-bs-toggle="modal" 
   data-bs-target="#custom-modal-id"
   data-url="/path/to/content"
   data-modal-options='{"title":"Modal Title"}'>
   Link Text
</a>

<!-- Rendered modal -->
<div id="custom-modal-id" class="modal fade">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"></h5>
      </div>
      <div class="modal-body"></div>
    </div>
  </div>
</div>
```

## 🔄 AJAX Response Requirements
Server responses should include either:
- HTML content with optional `X-Modal-Title` header
- JSON format: 
```json
{
    "title": "Modal Title",
    "html": "<div>Content</div>"
}
```

---

- [Back to index](index.md)