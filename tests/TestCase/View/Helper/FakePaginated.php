<?php
declare(strict_types=1);

namespace BootstrapTools\Test\TestCase\View\Helper;

use ArrayIterator;
use Cake\Datasource\Paging\PaginatedInterface;
use IteratorAggregate;
use Traversable;

/**
 * Minimal fake pagination instance for helper tests.
 *
 * @implements \IteratorAggregate<int, mixed>
 */
class FakePaginated implements PaginatedInterface, IteratorAggregate
{
    public function currentPage(): int
    {
        return 1;
    }

    public function perPage(): int
    {
        return 20;
    }

    public function totalCount(): ?int
    {
        return 50;
    }

    public function pageCount(): ?int
    {
        return 3;
    }

    public function hasPrevPage(): bool
    {
        return false;
    }

    public function hasNextPage(): bool
    {
        return true;
    }

    public function items(): iterable
    {
        return [];
    }

    public function pagingParam(string $name): mixed
    {
        return null;
    }

    public function pagingParams(): array
    {
        return [];
    }

    public function count(): int
    {
        return 0;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator([]);
    }
}
