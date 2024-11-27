<?php
namespace App\Exceptions;

use App\DTO\CustomErrorDTO;
use Illuminate\Http\JsonResponse;
use Exception;

class RecordNotFoundException extends Exception {
    public function __construct($msg)
    {
        parent::__construct($msg);
    }

    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function render($request): JsonResponse
    {
        $status = JsonResponse::HTTP_NOT_FOUND;
        $obj = new CustomErrorDTO($this->getMessage());
        return response()->json($obj, $status);
    }
}