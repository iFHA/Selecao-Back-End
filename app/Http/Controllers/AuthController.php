<?php

namespace App\Http\Controllers;

use App\DTO\Auth\AuthUserDTO;
use App\DTO\Auth\ChangePasswordDTO;
use App\DTO\SuccessResponseDTO;
use App\DTO\User\CreateUserDTO;
use App\DTO\User\UpdateUserDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AuthRequest;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function __construct(private AuthService $authService) {}

    public function auth(AuthRequest $request): JsonResponse
    {
        $token = $this->authService->auth(AuthUserDTO::fromRequest($request));
        return response()->json(compact('token'));
    }

    public function register(CreateUserRequest $request): JsonResponse
    {
        $createdUser = $this->authService->register(CreateUserDTO::fromRequest($request));
        return response()->json($createdUser->toArray(), JsonResponse::HTTP_CREATED);
    }

    public function logout(): JsonResponse
    {
        $this->authService->logout();
        return response()->json(new SuccessResponseDTO('Logout realizado com sucesso'));
    }

    public function me(): JsonResponse
    {
        $me = $this->authService->me();
        return response()->json($me->toArray());
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $dto = ChangePasswordDTO::fromRequest($request);
        $this->authService->changePassword($dto);
        return response()->json(new SuccessResponseDTO('Senha atualizada com sucesso'));
    }

    public function updateMe(UpdateUserRequest $request): JsonResponse
    {
        $dto = $this->authService
            ->updateMe(UpdateUserDTO::fromRequest($request));
        return response()->json($dto->toArray());
    }
}
