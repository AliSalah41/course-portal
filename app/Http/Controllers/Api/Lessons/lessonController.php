<?php

namespace App\Http\Controllers\Api\Lessons;

use App\Http\Controllers\Controller;
use App\Http\Resources\lessonResource;
use App\Models\Lesson;
use App\Models\Subscription;
use App\Models\User;
use App\Models\UserLessonHistory;
use App\Services\UserCourseActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class lessonController extends Controller
{
    protected $userCourseActivityService;

    public function __construct(UserCourseActivityService $userCourseActivityService)
    {
        $this->userCourseActivityService = $userCourseActivityService;
    }

    public function index($id): \Illuminate\Http\JsonResponse
    {
        $lessons = Lesson::where('course_id', $id)->get();

        if ($lessons->isEmpty())
        {
            return response()->json([
                'status' => false,
                'message' => "No Lessons added yet.",
            ],404);
        }

        return response()->json([
            'status' => true,
            'lessons' => lessonResource::collection($lessons),
        ]);
    }

    public function show($id)
    {
        $lesson = Lesson::where('id', $id)->first();

        if (!$lesson)
        {
            return response()->json([
                'status' => false,
                'message' => "Lesson Not Found!.",
            ],404);
        }
        $userId = Auth::id();
        $courseId = $lesson->course_id;

        $subscribe = Subscription::where('course_id', $courseId)->where('user_id', $userId)->first();
        if (!$subscribe)
        {
            return response()->json([
                'status' => false,
                'message' => "User is not subscribed to this course!.",
            ],403);
        }

        return response()->json([
            'status' => true,
            'lessons' =>new lessonResource($lesson),
        ]);
    }

    public function watchConfirm(Request $request)
    {
        $request->validate([
            'lesson_id' => 'required',
            'status' => 'required'
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

        $UserLessonHistory = UserLessonHistory::where('user_id', $user->id)->where('lesson_id', $lesson->id)->first();
        if(!$UserLessonHistory)
        {
            if($request->status == "done")
            {
                $history = UserLessonHistory::create([
                    'user_id' => $user->id,
                    'lesson_id' => $lesson->id,
                ]);
            }
            else {
                return response()->json([
                    'status' => false,
                    'message' => "user must finish the lesson",
                ],403);
            }
        }


        $activity = $this->userCourseActivityService->getUserActivity($user->id);

        //update AchievementsNumber & Badge
        $updateAchievementsNumber = $this->userCourseActivityService->updateLessonAchievements($user->id, $activity['lessons_watched']);

        return response()->json([
            'status' => true,
            'lessons' => "lesson submitted successfully",
        ],201);
    }
}
