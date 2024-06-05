<?php

namespace App\Http\Controllers\Api\Subscriptions;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class subscriptionController extends Controller
{
    public function subscribe(Request $request)
    {
        $request->validate([
            'course_id' => 'required'
        ]);

        $user = Auth::user();

        $course = Course::find($request->course_id);

        if(!$course)
        {
            return response()->json([
                'status' => false,
                'message' => 'course not found',
            ],404);
        }

        Subscription::create([
            'course_id' => $course->id,
            'user_id' => $user->id
        ]);

        return response()->json([
            'status' => true,
            'message' => 'User Subscribed successfully',
        ],201);
    }
}
