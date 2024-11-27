<?php

namespace App\DTO\Comment;

use App\DTO\Traits\DTOToArrayTrait;
use App\Models\Comment;
use Illuminate\Http\Request;

class CreateUpdateCommentDTO
{
    public function __construct(
        private string $comment
    ) {}

    /**
     * Get the value of comment
     */
    public function getComment(): string
    {
        return $this->comment;
    }

    public function toModel(): Comment
    {
        $model = new Comment();
        $model->comment = $this->getComment();
        return $model;
    }

    public static function fromModel(Comment $model)
    {
        return new self($model->comment);
    }

    public static function fromRequest(Request $request)
    {
        return new self($request->comment);
    }
}
