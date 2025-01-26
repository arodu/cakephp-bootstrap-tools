<?php

declare(strict_types=1);

namespace BootstrapTools\Listener;

use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Event\EventListenerInterface;
use Cake\Http\Response;
use Cake\Routing\Router;

class ControllerRedirectListener implements EventListenerInterface
{
    /**
     * @inheritDoc
     */
    public function implementedEvents(): array
    {
        return [
            'Controller.beforeRender' => 'beforeRender',
            'Controller.beforeRedirect' => 'beforeRedirect',
        ];
    }

    /**
     * @param EventInterface $event
     * @return void
     */
    public function beforeRender(EventInterface $event): void
    {
        $config = Configure::read('BootstrapTools.redirect');
        if (!($config['enable'] ?? false)) {
            return;
        }

        $controller = $event->getSubject();
        if ($controller instanceof Controller) {
            $controller->set($config['key'], $this->getRedirectUrl($controller));
        }
    }

    /**
     * @param EventInterface $event
     * @param mixed $url
     * @param Response $response
     * @return void
     */
    public function beforeRedirect(EventInterface $event, $url, Response $response): void
    {
        $config = Configure::read('BootstrapTools.redirect');
        if (!($config['enable'] ?? false)) {
            return;
        }

        $controller = $event->getSubject();
        if ($controller instanceof Controller) {
            $url = Router::url($this->getRedirectUrl($controller) ?? $url, true);
            $response = $response->withLocation($url);
            $event->setResult($response);
        }
    }

    /**
     * Get the redirect URL if it exists.
     *
     * @return string|null
     */
    protected function getRedirectUrl(Controller $controller): ?string
    {
        $config = Configure::read('BootstrapTools.redirect');

        return $controller->getRequest()->getQuery($config['key'])
            ?? $controller->getRequest()->getData($config['key'])
            ?? null;
    }
}
