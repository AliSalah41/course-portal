<?php

namespace App\Http\Controllers\Api\Lessons;

use App\Http\Controllers\Controller;
use App\Http\Resources\lessonResource;
use App\Models\Lesson;
use App\Models\Subscription;
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

    public function index($id)
    {
        $lessons = Lesson::where('course_id', $id)->get();

        if ($lessons->isEmpty())
        {
            return response()->json([
                'status' => false,
                'message' => "No Lessons added yet.",
            ]);
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
            ]);
        }
        $userId = Auth::id();
        $courseId = $lesson->course_id;

        $subscribe = Subscription::where('course_id', $courseId)->where('user_id', $userId)->first();
        if (!$subscribe)
        {
            return response()->json([
                'status' => false,
                'message' => "User is not subscribed to this course!.",
            ]);
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
            ]);
        }
        
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
            ]);
        }

        $activity = $this->userCourseActivityService->getUserCourseActivity($user->id, $lesson->course_id);

        
        return response()->json([
            'status' => true,
            'lessons' => "lesson submitted successfully",
        ]);
    }
}
