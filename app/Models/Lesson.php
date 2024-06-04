<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'lesson_link',
    ];

    public function userHistory()
    {
        return $this->hasMany(UserLessonHistory::class);
    }

    public function comment()
    {
        return $this->hasMany(Comment::class);
    }
}
