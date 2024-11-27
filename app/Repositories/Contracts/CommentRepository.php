<?php

namespace App\Repositories\Contracts;

use App\Models\Comment;

interface CommentRepository
{
    public function findAllPaged(int $page = 1, int $perPage = 15, $filter = null, $transformFunction = null): PaginationInterface;
    public function findByIdOrFail(int $id): Comment;
    public function create(Comment $comment): Comment;
    public function update(int $commentId, Comment $comment): Comment;
    public function delete(int $commentId): void;
    public function deleteAll(): void;
}
