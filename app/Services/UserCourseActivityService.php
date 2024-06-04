<?php

namespace App\Services;

use App\Models\UserLessonHistory;
use App\Models\Comment;
use App\Models\Lesson;
use App\Models\Subscription;

class UserCourseActivityService
{
    // Define the achievement milestones
    protected $lessonAchievements = [1, 5, 10, 25, 50];
    protected $commentAchievements = [1, 3, 5, 10, 20];

    // Define the badges based on achievements count
    protected $badges = [
        'Beginner' => 0,
        'Intermediate' => 4,
        'Advanced' => 8,
        'Master' => 10,
    ];

    public function getUserCourseActivity($userId, $courseId)
    {
        // Count lessons watched by the user in the specific course
        $lessonsWatched = UserLessonHistory::where('user_id', $userId)
            ->whereHas('lesson', function ($query) use ($courseId) {
                $query->where('course_id', $courseId);
            })
            ->distinct('lesson_id')
            ->count('lesson_id');

        // Count comments made by the user on lessons in the specific course
        $commentsMade = Comment::where('user_id', $userId)
            ->whereHas('lesson', function ($query) use ($courseId) {
                $query->where('course_id', $courseId);
            })
            ->count();

        // Check and update achievements
        $achievements = $this->checkAchievements($lessonsWatched, $commentsMade);

        // Update the user's badge in the subscriptions table
        $this->updateBadge($userId, $courseId, $achievements);

        return [
            'lessons_watched' => $lessonsWatched,
            'comments_made' => $commentsMade,
            'achievements' => $achievements,
        ];
    }

    public function checkAchievements($lessonsWatched, $commentsMade)
    {
        $achievements = [];

        foreach ($this->lessonAchievements as $achievement) {
            if ($lessonsWatched >= $achievement) {
                $achievements[] = "$achievement Lessons Watched";
            }
        }

        foreach ($this->commentAchievements as $achievement) {
            if ($commentsMade >= $achievement) {
                $achievements[] = "$achievement Comments Written";
            }
        }

        return $achievements;
    }

    public function getNextAvailableAchievements($lessonsWatched, $commentsMade)
    {
        $nextAchievements = [];

        foreach ($this->lessonAchievements as $achievement) {
            if ($lessonsWatched < $achievement) {
                $nextAchievements[] = "$achievement Lessons Watched";
                break;
            }
        }

        foreach ($this->commentAchievements as $achievement) {
            if ($commentsMade < $achievement) {
                $nextAchievements[] = "$achievement Comments Written";
                break;
            }
        }

        return $nextAchievements;
    }

    public function updateBadge($userId, $courseId, $achievements)
    {
        $achievementCount = count($achievements);

        // Determine the badge based on the number of achievements
        $badge = 'Beginner';
        foreach ($this->badges as $badgeName => $requiredAchievements) {
            if ($achievementCount >= $requiredAchievements) {
                $badge = $badgeName;
            }
        }

        // Update the subscription with the new badge
        Subscription::updateOrCreate(
            ['user_id' => $userId, 'course_id' => $courseId],
            ['badge' => $badge]
        );
    }
}
