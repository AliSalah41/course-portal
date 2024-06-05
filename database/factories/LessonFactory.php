<?php

namespace Database\Factories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lesson>
 */
class LessonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        if ($course = Course::inRandomOrder()->first()) {
            return [
                'title' => fake()->sentence(20),
                'lesson_link' => fake()->url,
                'course_id' => $course->id,
            ];
        }
    }
}
