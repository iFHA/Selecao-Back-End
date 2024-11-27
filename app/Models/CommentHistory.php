<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommentHistory extends Model
{
    use HasFactory;

    protected $fillable = ['comment'];
    protected $dateFormat = 'Y-m-d H:i:s';
    protected $table = 'comment_history';
}
