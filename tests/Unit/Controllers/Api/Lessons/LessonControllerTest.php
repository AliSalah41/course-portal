<?php

namespace Tests\Unit\Controllers\Api\Lessons;

use App\Http\Controllers\Api\Lessons\lessonController;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use App\Models\UserLessonHistory;
use App\Services\UserCourseActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

//use PHPUnit\Framework\TestCase;

class LessonControllerTest extends TestCase
{
    /** @test */
    public function it_submits_lesson_history_when_lesson_watched(): void
    {
        $user = User::factory()->create();
        Auth::shouldReceive('user')->andReturn($user);

        Course::factory(5)->create();
        $lesson = Lesson::factory()->create();

        $request = Request::create('/watch-confirm', 'POST', ['lesson_id' => $lesson->id, 'status' => 'done']);
        $controller = new lessonController(new UserCourseActivityService());

        $response = $controller->watchConfirm($request);

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $this->assertEquals(201, $response->status());

        $this->assertTrue(UserLessonHistory::where('user_id', $user->id)->where('lesson_id', $lesson->id)->exists());
    }

    /** @test */
    public function it_returns_error_if_lesson_not_found(): void
    {
        $request = Request::create('/watch-confirm', 'POST', ['lesson_id' => 999, 'status' => 'done']); // Lesson with id 999 doesn't exist
        $controller = new lessonController(new UserCourseActivityService());

        $response = $controller->watchConfirm($request);

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $this->assertEquals(404, $response->status());

        $responseData = $response->getData(true);
        $this->assertFalse($responseData['status']);
        $this->assertEquals('Lesson Not Found!.', $responseData['message']);
    }

    /** @test */
    public function it_returns_error_if_user_has_not_finished_lesson(): void
    {
        $user = User::factory()->create();
        Auth::shouldReceive('user')->andReturn($user);

        $lesson = Lesson::factory()->create();

        $request = Request::create('/watch-confirm', 'POST', ['lesson_id' => $lesson->id, 'status' => 'not-done']);
        $controller = new lessonController(new UserCourseActivityService());

        $response = $controller->watchConfirm($request);

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $this->assertEquals(403, $response->status());

        $responseData = $response->getData(true);
        $this->assertFalse($responseData['status']);
        $this->assertEquals('user must finish the lesson', $responseData['message']);
    }
}
