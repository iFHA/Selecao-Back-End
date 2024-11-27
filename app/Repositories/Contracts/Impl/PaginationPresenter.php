<?php

namespace App\Repositories\Contracts\Impl;

use App\Repositories\Contracts\PaginationInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use stdClass;

class PaginationPresenter implements PaginationInterface
{
    /**
     * Itens
     *
     * @var stdClass[]
     */
    private array $items;
    public function __construct(private LengthAwarePaginator $paginator, $transformFunction = null)
    {
        $this->items = $this->resolveItems($this->paginator->items() ?? [], $transformFunction);
    }
    public function items(): array
    {
        return $this->items;
    }
    public function total(): int
    {
        return $this->paginator->total() ?? 0;
    }
    public function isFirstPage(): bool
    {
        return $this->paginator->currentPage() === 1;
    }
    public function isLastPage(): bool
    {
        return $this->paginator->currentPage() === $this->paginator->lastPage();
    }
    public function currentPage(): int
    {
        return $this->paginator->currentPage() ?? 1;
    }
    public function getPreviousPageNumber(): int
    {
        return $this->paginator->currentPage() - 1;
    }
    public function getNextPageNumber(): int
    {
        return $this->paginator->currentPage() + 1;
    }
    private function resolveItems(array $items, $transform): array
    {
        if (is_null($transform)) {
            return $items;
        }
        $arrayItems = [];
        foreach ($items as $item) {
            $arrayItems[] = $transform($item);
        }
        return $arrayItems;
    }
}
