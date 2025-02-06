<?php
declare(strict_types=1);

namespace BootstrapTools\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Cake\Event\EventInterface;
use Cake\Http\Response;
use Cake\Routing\Router;

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
        'strategy' => 'html', // html, json
        'ajaxClassNames' => 'Ajax',
    ];


    public function beforeRender(EventInterface $event)
    {
        $controller = $this->getController();
        if ($controller->getRequest()->is('ajax')) {
            $controller->viewBuilder()->setClassName($this->getConfig('ajaxClassNames'));
        }
    }

    public function success(): Response
    {
        $controller = $this->getController();
        //$response = $controller->getResponse()->withHeader($this->getConfig('modalSuccessKey'), '1');
        //$controller->setResponse($response);

        return $controller->getResponse();
    }

    public function error(): Response
    {
        $controller = $this->getController();
        //$response = $controller->getResponse()->withHeader($this->getConfig('modalSuccessKey'), '0');
        //$controller->setResponse($response);

        return $controller->getResponse();
    }
}