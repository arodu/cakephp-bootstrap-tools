<?php
declare(strict_types=1);

namespace BootstrapTools\Test\TestCase\Command;

use Cake\Http\Client\Response;

/**
 * Fake Response returning a fixed body.
 */
class FakeResponse extends Response
{
    public function __construct(private string $body)
    {
        parent::__construct();
    }

    public function isOk(): bool
    {
        return true;
    }

    public function getStringBody(): string
    {
        return $this->body;
    }
}
