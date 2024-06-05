<?php

namespace App\Http\Controllers\Api\Badges;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserCourseActivityService;
use Illuminate\Http\Request;

class badgeController extends Controller
{
    protected $userCourseActivityService;

    public function __construct(UserCourseActivityService $userCourseActivityService)
    {
        $this->userCourseActivityService = $userCourseActivityService;
    }

    public function getUserAchievements($userId)
    {
        $activity = $this->userCourseActivityService->getUserActivity($userId);

        $user = User::find($userId);
        $userBadge = $user->badge;

        $nextBadge = $this->userCourseActivityService->getNextBadge($user);
        $unLockedBadge = $this->userCourseActivityService->getunlockedBadge($user);

        return response()->json([
            'lessons_watched' => $activity['lessons_watched'],
            'comments_made' => $activity['comments_made'],
            'Achievement Score' => $user->Achievements,
            'current_badge' => $userBadge,
            'Un Locked' => $unLockedBadge,
            'next_badge' => $nextBadge,
        ]);
    }

}
