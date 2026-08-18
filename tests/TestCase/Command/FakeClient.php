<?php
declare(strict_types=1);

namespace BootstrapTools\Test\TestCase\Command;

use Cake\Http\Client;
use Cake\Http\Client\Response;

/**
 * Fake Client returning a fixed response.
 */
class FakeClient extends Client
{
    public function __construct(private string $body)
    {
    }

    public function get(string $url, array|string $data = [], array $options = []): Response
    {
        return new FakeResponse($this->body);
    }
}
