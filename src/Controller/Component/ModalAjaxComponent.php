<?php
declare(strict_types=1);

namespace BootstrapTools\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Cake\Event\EventInterface;

/**
 * ModalAjax component
 */
class ModalAjaxComponent extends Component
{
    /**
     * Default configuration.
     *
     * @var array<string, mixed>
     */
    protected array $_defaultConfig = [
        'redirectKey' => 'X-ModalAjax-Redirect',
        'modalSuccessKey' => 'X-ModalAjax-Success',
    ];
}
