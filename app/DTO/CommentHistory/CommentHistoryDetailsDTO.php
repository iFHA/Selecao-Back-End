<?php

namespace App\DTO\CommentHistory;

use App\Models\CommentHistory;

class CommentHistoryDetailsDTO
{
    public function __construct(
        public string $comment,
        public string $created_at,
    ) {}

    public static function fromModel(CommentHistory $model)
    {
        return new self(
            $model->comment,
            $model->created_at
        );
    }
}
