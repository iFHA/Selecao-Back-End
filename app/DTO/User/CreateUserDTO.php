<?php

namespace App\DTO\User;

use App\DTO\Traits\DTOToArrayTrait;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CreateUserDTO
{

    use DTOToArrayTrait;

    public function __construct(
        private string $name,
        private string $email,
        private string $password
    ) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function toModel(): User
    {
        $user = new User();
        $user->name = $this->getName();
        $user->email = $this->getEmail();
        $user->password = $this->getPassword();
        return $user;
    }

    public static function fromRequest(Request $request)
    {
        $data = $request->validated();
        return new self($data['name'], $data['email'], $data['password']);
    }
}
