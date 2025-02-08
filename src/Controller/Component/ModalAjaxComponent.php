<?php

declare(strict_types=1);

namespace BootstrapTools\Controller\Component;

use BootstrapTools\Http\JsonResponse;
use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Cake\Event\EventInterface;
use Cake\Http\Response;
use Cake\Log\Log;
use Cake\Routing\Router;

/**
 * ModalAjax component
 */
class ModalAjaxComponent extends Component
{
    const STRATEGY_HTML = 'html';
    const STRATEGY_JSON = 'json';

    /**
     * Default configuration.
     *
     * @var array<string, mixed>
     */
    protected array $_defaultConfig = [
        'strategy' => self::STRATEGY_HTML,
        'ajaxClassNames' => 'Ajax',
        'modalSuccessKey' => 'X-Modal-Ajax-Success',
    ];

    public function initialize(array $config): void
    {
        $controller = $this->getController();
        if ($controller->getRequest()->is('ajax')) {
            $controller->viewBuilder()->setClassName($this->getConfig('ajaxClassNames'));
        }
    }

    public function beforeRender(EventInterface $event): void
    {
        $controller = $this->getController();
        $controller->set('modalSuccessKey', $this->getConfig('modalSuccessKey'));
    }

    public function send(bool $success, string $message = null, array $data = [], array $meta = [], int $status = 200): Response
    {
        if ($this->getConfig('strategy') === self::STRATEGY_HTML) {
            return $this->handleHtmlResponse(
                success: $success,
                message: $message ?? 'Operation successful.',
                data: $data,
                meta: $meta,
                status: $status
            );
        }

        if ($this->getConfig('strategy') === self::STRATEGY_JSON) {
            return $this->handleJsonResponse(
                success: $success,
                message: $message ?? 'Operation successful.',
                data: $data,
                meta: $meta,
                status: $status
            );
        }

        throw new \RuntimeException('Invalid strategy');
    }


    public function success(string $message = null, array $data = [], array $meta = [], int $status = 200): Response
    {
        return $this->send(true, $message, $data, $meta, $status);
    }

    public function error(string $message = null, array $data = [], array $meta = [], int $status = 400): Response
    {
        return $this->send(false, $message, $data, $meta, $status);
    }

    protected function handleHtmlResponse(bool $success, string $message, array $data = [], array $meta = [], int $status = 200): Response
    {
        $controller = $this->getController();
        $response = $controller->getResponse();

        return $response
            ->withStatus($status)
            ->withBody($controller->render()->getBody())
            ->withHeader($this->getConfig('modalSuccessKey'), $success ? '1' : '0');
    }

    protected function handleJsonResponse(bool $success, string $message, array $data = [], array $meta = [], int $status = 200): Response
    {
        $controller = $this->getController();
        try {
            $html = (string) $this->getController()->render()->getBody();
        } catch (\Exception $e) {
            Log::error("HTML Render Error: {$e->getMessage()}");

            throw $e;
        }

        $jsonResponse = new JsonResponse($controller->getResponse());

        return $jsonResponse
            ->success($success)
            ->status($status)
            ->message($message)
            ->data($data)
            ->meta($meta)
            ->html($html)
            ->build();
    }
}
