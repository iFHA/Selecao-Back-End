<?php

namespace App\Repositories\Contracts\Impl\Eloquent;

use App\Exceptions\RecordNotFoundException;
use App\Models\Comment;
use App\Repositories\Contracts\CommentRepository;
use App\Repositories\Contracts\Impl\PaginationPresenter;
use App\Repositories\Contracts\PaginationInterface;

class CommentEloquentORMRepository implements CommentRepository
{
    public function __construct(private Comment $model) {}

    public function findAllPaged(
        int $page = 1,
        int $perPage = 15,
        $filter = null,
        $transformFunction = null
    ): PaginationInterface {

        $result = $this->model
            ->with('author')
            ->where(function ($query) use ($filter) {
                if ($filter) {
                    $query->where('comment', 'like', '%' . $filter . '%');
                    $query->orWhereHas(
                        'author',
                        function ($query) use ($filter) {
                            $query->where('name', 'like', '%' . $filter . '%');
                        }
                    );
                }
            })
            ->paginate($perPage, ['*'], 'page', $page);
        return new PaginationPresenter($result, $transformFunction);
    }

    public function findByIdOrFail(int $id): Comment
    {
        $comment = $this->model->find($id);
        if (!$comment) {
            throw new RecordNotFoundException("Comentário de id = $id não encontrado!");
        }
        return $comment;
    }

    public function create(Comment $comment): Comment
    {
        return $this->model->create($comment->toArray());
    }

    public function update(int $commentId, Comment $comment): Comment
    {
        $commentEntity = $this->findByIdOrFail($commentId);
        $commentEntity->update($comment->toArray());
        return $commentEntity;
    }

    public function delete(int $commentId): void
    {
        $this->findByIdOrFail($commentId)->delete();
    }

    public function deleteAll(): void
    {
        $this->model->query()->delete();
    }
}
