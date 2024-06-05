<?php

namespace App\Http\Controllers\Api\Comments;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Lesson;
use App\Models\Subscription;
use App\Services\UserCourseActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class commentController extends Controller
{
    protected $userCourseActivityService;

    public function __construct(UserCourseActivityService $userCourseActivityService)
    {
        $this->userCourseActivityService = $userCourseActivityService;
    }

    public function store(Request $request)
    {
        $request->validate([
            "lesson_id" => "required|integer",
            "comments" => "required|string|max:255" 
        ]);

        $user = Auth::user();
        $lesson = Lesson::where('id', $request->lesson_id)->first();

        if (!$lesson)
        {
            return response()->json([
                'status' => false,
                'message' => "Lesson Not Found!.",
            ],404);
        }

        $subscribe = Subscription::where('course_id', $lesson->course_id)->where('user_id', $user->id)->first();
        if (!$subscribe)
        {
            return response()->json([
                'status' => false,
                'message' => "User is not subscribed to this course!.",
            ],403);
        }

        Comment::create([
            'lesson_id' => $lesson->id,
            'user_id' => $user->id,
            'comments' => $request->comments
        ]);

        $activity = $this->userCourseActivityService->getUserActivity($user->id);

        //update AchievementsNumber & Badge
        $updateAchievementsNumber = $this->userCourseActivityService->updateCommentAchievements($user->id, $activity['comments_made']);

        return response()->json([
            'status' => true,
            'message' =>"Comment Saved successfully",
        ],201);
    }
}
