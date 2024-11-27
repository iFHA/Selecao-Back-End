<?php

namespace App\Exceptions;

use App\DTO\CustomErrorDTO;
use Exception;
use Illuminate\Http\JsonResponse;

class NotOwnerException extends Exception
{
    public function __construct($msg)
    {
        parent::__construct($msg);
    }

    public function render($request): JsonResponse
    {
        $status = JsonResponse::HTTP_FORBIDDEN;
        $obj = new CustomErrorDTO($this->getMessage());
        return response()->json($obj, $status);
    }
}
