<?php

declare(strict_types=1);
/**
 * BootstrapTools CakePHP Plugin
 * 
 * @copyright 2025 Alberto Rodriguez
 * @author Alberto Rodriguez <arodu.dev@gmail.com>
 * @link https://github.com/arodu
 */

namespace BootstrapTools\Listener;

use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Event\EventListenerInterface;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
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
        $config = $this->getConfig();

        if (!$this->isRedirectEnabled($config)) {
            return;
        }

        $controller = $event->getSubject();
        if ($controller instanceof Controller) {
            $redirect = $this->getRedirectUrl($controller->getRequest(), $config);
            $controller->set($config['key'], $redirect);
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
        $config = $this->getConfig();

        if (!$this->isRedirectEnabled($config)) {
            return;
        }

        $controller = $event->getSubject();
        if ($controller instanceof Controller) {
            $redirect = $this->getRedirectUrl($controller->getRequest(), $config);
            $url = Router::url($redirect ?? $url, true);
            $response = $response->withLocation($url);
            $event->setResult($response);
        }
    }

    /**
     * @param ServerRequest $request
     * @param array $config
     * @return string|null
     */
    protected function getRedirectUrl(ServerRequest $request, array $config): ?string
    {
        return $request->getQuery($config['key'])
            ?? $request->getData($config['key'])
            ?? null;
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        $config = Configure::read('BootstrapTools.redirect', []);
        if (!is_array($config) || !isset($config['key'], $config['enable'])) {
            return ['key' => 'redirect', 'enable' => true];
        }

        return $config;
    }

    /**
     * @param array $config
     * @return boolean
     */
    public function isRedirectEnabled(array $config): bool
    {
        return $config['enable'] ?? false;
    }
}
