<?php

namespace App\DTO\User;

use App\DTO\Traits\DTOToArrayTrait;
use App\Models\User;

class UserDetailsDTO
{
    use DTOToArrayTrait;

    public function __construct(
        private int $id,
        private string $name,
        private string $email
    ) {}

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public static function fromModel(User $model): UserDetailsDTO
    {
        return new self(
            $model->id,
            $model->name,
            $model->email
        );
    }
}
