<?php

namespace App\Adapters;

use App\Repositories\Contracts\PaginationInterface;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiAdapter
{
    public static function toJson(PaginationInterface $data)
    {
        return JsonResource::collection($data->items())
            ->additional([
                'meta' => [
                    'total' => $data->total(),
                    'is_first_page' => $data->isFirstPage(),
                    'is_last_page' => $data->isLastPage(),
                    'current_page' => $data->currentPage(),
                    'next_page' => $data->getNextPageNumber(),
                    'previous_page' => $data->getPreviousPageNumber()
                ]
            ])
        ;
    }
}
