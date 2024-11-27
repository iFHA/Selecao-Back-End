<?php

namespace App\Observers;

use App\Models\Comment;
use App\Models\CommentHistory;

class CommentObserver
{
    /**
     * Handle the Comment "creating" event.
     */
    public function creating(Comment $comment): void
    {
        if (is_null($comment->user_id)) {
            $comment->user_id = auth()->user()->id;
        }
    }

    /**
     * Handle the Comment "created" event.
     */
    public function created(Comment $comment): void
    {
        $this->addHistory($comment);
    }

    /**
     * Handle the Comment "updating" event.
     */
    public function updated(Comment $comment): void
    {
        if ($comment->wasChanged('comment')) {
            $this->addHistory($comment);
        }
    }

    private function addHistory(Comment $comment): void
    {
        $comment->history()->create(['comment' => $comment->comment]);
    }
}
