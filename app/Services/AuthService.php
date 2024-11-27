<?php

namespace App\Services;

use App\DTO\Auth\AuthUserDTO;
use App\DTO\Auth\ChangePasswordDTO;
use App\DTO\User\CreateUserDTO;
use App\DTO\User\UpdateUserDTO;
use App\DTO\User\UserDetailsDTO;
use App\Exceptions\NotAdminException;
use App\Models\User;
use App\Repositories\Contracts\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(private UserRepository $userRepository) {}

    public function auth(AuthUserDTO $dto): string
    {
        $error = ValidationException::withMessages([
            'email' => "Credenciais inválidas"
        ]);

        $user = $this->userRepository->findByEmail($dto->getEmail());
        if (!$user) {
            throw $error;
        }

        $isPasswordCorrect = Hash::check($dto->getPassword(), $user->password);
        if (!$isPasswordCorrect) {
            throw $error;
        }

        $user->tokens()->delete();
        $token = $user->createToken($user->email)->plainTextToken;
        return $token;
    }

    public function logout(): void
    {
        $userEntity = $this->getLoggedUserEntity();
        $userEntity->tokens()->delete();
    }

    protected function getLoggedUserEntity(): User
    {
        $authenticatable = auth()->user();
        return $this->userRepository
            ->findByIdOrFail($authenticatable->getAuthIdentifier());
    }

    public function me(): UserDetailsDTO
    {
        return UserDetailsDTO::fromModel($this->getLoggedUserEntity());
    }

    public function changePassword(ChangePasswordDTO $dto): void
    {
        $user = $this->getLoggedUserEntity();
        $isCurrentPasswordCorrect = Hash::check($dto->getCurrentPassword(), $user->password);
        if (!$isCurrentPasswordCorrect) {
            throw ValidationException::withMessages([
                'current_password' => "Senha atual inválida"
            ]);
        }
        $newPassword = Hash::make($dto->getNewPassword());
        $this->userRepository->changePassword($user->id, $newPassword);
    }

    public function register(CreateUserDTO $dto): UserDetailsDTO
    {
        $model = $dto->toModel();
        $model->password = Hash::make($dto->getPassword());

        $model = $this->userRepository->create($model);
        return UserDetailsDTO::fromModel($model);
    }

    public function updateMe(UpdateUserDTO $dto): UserDetailsDTO
    {
        $userId = $this->getLoggedUserEntity()->id;
        $model = $this->userRepository->update($userId, $dto->toModel());
        return UserDetailsDTO::fromModel($model);
    }

    public function validateAdmin(): void
    {
        if (!$this->isLoggedUserAdmin()) {
            throw new NotAdminException("Permissão negada! para realizar essa operação o usuário necessita de privilégios administrativos");
        }
    }

    public function isLoggedUserAdmin(): bool
    {
        return auth()->user()->is_admin;
    }
}
