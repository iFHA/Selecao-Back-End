<?php

namespace App\DTO\Auth;

use Illuminate\Http\Request;

class ChangePasswordDTO
{
    public function __construct(
        private string $currentPassword,
        private string $newPassword
    ) {}

    public function getCurrentPassword(): string
    {
        return $this->currentPassword;
    }

    public function getNewPassword(): string
    {
        return $this->newPassword;
    }

    public static function fromRequest(Request $request): ChangePasswordDTO
    {
        $data = $request->validated();
        return new self($data['current_password'], $data['new_password']);
    }
}
