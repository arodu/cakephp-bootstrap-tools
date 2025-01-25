# Bootstrap Tools plugin for CakePHP

## Installation

You can install this plugin into your CakePHP application using [composer](https://getcomposer.org).

The recommended way to install composer packages is:

```
composer require arodu/cakephp-bootstrap-tools
```

## Load the plugin

You need to add the following line to your application's `src/Application.php`:

```php
// src/Application.php
public function bootstrap(): void
{
    parent::bootstrap();
    $this->addPlugin('BootstrapTools');
}
```

or run the following command:
```bash
bin/cake plugin load BootstrapTools
```
