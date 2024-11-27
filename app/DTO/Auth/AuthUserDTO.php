<?php

namespace App\DTO\Auth;

use Illuminate\Http\Request;

class AuthUserDTO
{
    public function __construct(
        private string $email,
        private string $password
    ) {}

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public static function fromRequest(Request $request): AuthUserDTO
    {
        $data = $request->validated();
        return new self(
            $data['email'],
            $data['password']
        );
    }
}
