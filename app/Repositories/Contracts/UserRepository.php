<?php

namespace App\Repositories\Contracts;

use App\Models\User;

interface UserRepository
{
    public function findByEmail(string $email): User | null;
    public function findByIdOrFail(int $id): User;
    public function create(User $user): User;
    public function update(int $userId, User $user): User;
    public function changePassword(int $userId, string $password): void;
}
