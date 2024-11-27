<?php

namespace App\DTO\User;

use App\Models\User;
use Illuminate\Http\Request;

class UpdateUserDTO
{
    public function __construct(
        private string $name,
        private string $email
    ) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function toModel(): User
    {
        $user = new User();
        $user->name = $this->getName();
        $user->email = $this->getEmail();
        return $user;
    }

    public static function fromRequest(Request $request)
    {
        $data = $request->validated();
        return new self($data['name'], $data['email']);
    }
}
