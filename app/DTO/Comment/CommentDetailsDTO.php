<?php

namespace App\DTO\Comment;

use App\DTO\Traits\DTOToArrayTrait;
use App\Models\Comment;

class CommentDetailsDTO
{
    use DTOToArrayTrait;

    public function __construct(
        private int $id,
        private string $comment,
        private string $author,
        private string $posted_at,
        private string $last_modified_at,
    ) {}

    /**
     * Get the value of id
     */
    public function getId(): int
    {
        return $this->id;
    }
    /**
     * Get the value of comment
     */
    public function getComment(): string
    {
        return $this->comment;
    }

    /**
     * Get the value of author
     */
    public function getAuthor(): string
    {
        return $this->author;
    }

    /**
     * Get the value of posted_at
     */
    public function getPostedAt(): string
    {
        return $this->posted_at;
    }

    /**
     * Get the value of last_modified_at
     */
    public function getLastModifiedAt()
    {
        return $this->last_modified_at;
    }

    public static function fromModel(Comment $model)
    {
        return new self(
            $model->id,
            $model->comment,
            $model->author->name,
            $model->created_at,
            $model->updated_at
        );
    }
}
