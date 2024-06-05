<?php

namespace App\Services;

use App\Models\UserLessonHistory;
use App\Models\Comment;
use App\Models\Lesson;
use App\Models\Subscription;
use App\Models\User;

class UserCourseActivityService
{
    public function getUserActivity($userId)
    {
        $totalLessonsWatched = $this->getTotalLessonsWatched($userId);

        $totalCommentsMade = $this->getTotalCommentsMade($userId);

        return [
            'lessons_watched' => $totalLessonsWatched,
            'comments_made' => $totalCommentsMade,
        ];
    }

    public function getTotalLessonsWatched($userId)
    {
        return UserLessonHistory::where('user_id', $userId)
            ->distinct('lesson_id')
            ->count('lesson_id');
    }

    public function getTotalCommentsMade($userId)
    {
        return Comment::where('user_id', $userId)
            ->count();
    }

    public function updateBadge($userId)
    {
        $user = User::find($userId);

        if ($user->Achievements == 4) {
            $user->update(['badge' => "Intermediate"]);
            $user->sendEmail($user);
        } elseif ($user->Achievements == 8) {
            $user->update(['badge' => "Advanced"]);
            $user->sendEmail($user);
        } elseif ($user->Achievements == 10) {
            $user->update(['badge' => "Master"]);
            $user->sendEmail($user);
        }
    }

    public function getNextBadge($user)
    {
        if($user->badge == "Beginner") {
            $nextBadge = "Intermediate: 4 Achievements";
        } elseif ($user->badge == "Intermediate") {
            $nextBadge = "Advanced: 8 Achievements";
        } elseif ($user->badge == "Advanced") {
            $nextBadge = "Master: 10 Achievements";
        }

        return $nextBadge;
    }

    public function getunlockedBadge($user)
    {
        if($user->badge == "Beginner") {
            $lockedBadge = ['Beginner' => "0 Achievements"];
        } elseif ($user->badge == "Intermediate") {
            $lockedBadge = [
                'Beginner' => "0 Achievements",
                'Intermediate' => "4 Achievements"
            ];
        } elseif ($user->badge == "Advanced") {
            $lockedBadge = [
                'Beginner' => "0 Achievements",
                'Intermediate' => "4 Achievements",
                'Advanced'=> "8 Achievements",
            ];
        } elseif ($user->badge == "Master") {
            $lockedBadge = [
                'Beginner' => "0 Achievements",
                'Intermediate' => "4 Achievements",
                'Advanced'=> "8 Achievements",
                'Master' => "10 Achievements",
            ];
        }

        return $lockedBadge;
    }

    public function updateLessonAchievements($userId, $lessonsWatched)
    {
        $lessonMilestones = [1, 5, 10, 25, 50];

        $user = User::find($userId);

        if (in_array($lessonsWatched, $lessonMilestones)) {
            $user->update([
                'Achievements' => $user->Achievements + 1,
            ]);
            $this->updateBadge($user->id);
        }
    }

    public function updateCommentAchievements($userId, $commentsMade)
    {
        $commentMilestones = [1, 3, 5, 10, 20];

        $user = User::find($userId);

        if (in_array($commentsMade, $commentMilestones)) {
            $user->update([
                'Achievements' => $user->Achievements + 1,
            ]);
            $this->updateBadge($user->id);
        }
    }
}
