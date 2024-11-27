<?php

namespace App\Repositories\Contracts\Impl\Eloquent;

use App\Exceptions\RecordNotFoundException;
use App\Models\User;
use App\Repositories\Contracts\UserRepository;

class UserEloquentORMRepository implements UserRepository
{
    public function __construct(private User $model) {}

    public function findByEmail(string $email): User | null
    {
        return $this->model->where('email', $email)->first();
    }

    public function findByIdOrFail(int $id): User
    {
        $user = $this->model->find($id);
        if (!$user) {
            throw new RecordNotFoundException("Usuário de id = $id não encontrado!");
        }
        return $user;
    }

    public function create(User $user): User
    {
        return $this->model->create($user->makeVisible('password')->toArray());
    }

    public function update(int $userId, User $user): User
    {
        $userEntity = $this->findByIdOrFail($userId);
        $userEntity->update($user->toArray());
        return $userEntity;
    }
    public function changePassword(int $userId, string $password): void
    {
        $userEntity = $this->findByIdOrFail($userId);
        $userEntity->password = $password;
        $userEntity->save();
    }
}
