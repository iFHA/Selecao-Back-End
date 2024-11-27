<?php

namespace App\Services;

use App\DTO\Comment\CommentDetailsDTO;
use App\DTO\Comment\CreateUpdateCommentDTO;
use App\DTO\CommentHistory\CommentHistoryDetailsDTO;
use App\Exceptions\NotOwnerException;
use App\Repositories\Contracts\CommentRepository;
use App\Repositories\Contracts\PaginationInterface;
use Illuminate\Support\Facades\Gate;

class CommentService
{
    public function __construct(
        private CommentRepository $commentRepository,
        private AuthService $authService
    ) {}

    public function findAll(
        int $page = 1,
        int $perPage = 15,
        $filter = null
    ): PaginationInterface {
        return $this->commentRepository->findAllPaged(
            $page,
            $perPage,
            $filter,
            fn($model) => CommentDetailsDTO::fromModel($model)
        );
    }

    public function findById(int $id): CommentDetailsDTO
    {
        $model = $this->commentRepository->findByIdOrFail($id);
        return CommentDetailsDTO::fromModel($model);
    }

    public function getHistory(int $id): array
    {
        $model = $this->commentRepository->findByIdOrFail($id);
        $this->validateOwner($id);
        $history = [];
        if ($model->history) {
            foreach ($model->history as $historyModel) {
                $history[] = CommentHistoryDetailsDTO::fromModel($historyModel);
            }
        }
        return $history;
    }

    public function create(CreateUpdateCommentDTO $dto): CommentDetailsDTO
    {
        $model = $this->commentRepository->create($dto->toModel());
        return CommentDetailsDTO::fromModel($model);
    }

    public function update(int $commentId, CreateUpdateCommentDTO $dto): CommentDetailsDTO
    {
        $this->validateOwner($commentId);
        $model = $this->commentRepository->update($commentId, $dto->toModel());
        return CommentDetailsDTO::fromModel($model);
    }

    public function delete(int $commentId): void
    {
        if (!$this->authService->isLoggedUserAdmin()) {
            $this->validateOwner($commentId);
        }
        $this->commentRepository->delete($commentId);
    }

    public function deleteAll(): void
    {
        $this->authService->validateAdmin();
        $this->commentRepository->deleteAll();
    }

    protected function validateOwner(int $commentId): void
    {
        $comment = $this->commentRepository->findByIdOrFail($commentId);
        if (Gate::denies('owner', $comment->author->id)) {
            throw new NotOwnerException("Permissão negada! somente o autor do comentário pode realizar essa operação.");
        }
    }
}
