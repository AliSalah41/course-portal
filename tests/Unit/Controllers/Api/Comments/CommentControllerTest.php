<?php

namespace Tests\Unit\Controllers\Api\Comments;

use App\Http\Controllers\Api\Comments\commentController;
use App\Models\Comment;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Subscription;
use App\Models\User;
use App\Services\UserCourseActivityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class CommentControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_stores_comment_when_user_subscribed_to_lesson()
    {
        $user = User::factory()->create();
        Auth::shouldReceive('user')->andReturn($user);

        Course::factory(5)->create();
        $lesson = Lesson::factory()->create();
        Subscription::factory()->create(['user_id' => $user->id, 'course_id' => $lesson->course_id]);

        $request = Request::create('/comments', 'POST', [
            'lesson_id' => $lesson->id,
            'comments' => 'Test comment'
        ]);
        $controller = new commentController(new UserCourseActivityService());

        $response = $controller->store($request);

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $this->assertEquals(201, $response->status());

        $this->assertTrue(Comment::where('lesson_id', $lesson->id)->where('user_id', $user->id)->exists());
    }

    /** @test */
    public function it_returns_error_if_lesson_not_found()
    {
        $request = Request::create('/comments', 'POST', [
            'lesson_id' => 999,
            'comments' => 'Test comment'
        ]); // Lesson with id 999 doesn't exist
        $controller = new commentController(new UserCourseActivityService());

        $response = $controller->store($request);

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $this->assertEquals(404, $response->status());

        $responseData = $response->getData(true);
        $this->assertFalse($responseData['status']);
        $this->assertEquals('Lesson Not Found!.', $responseData['message']);
    }

    /** @test */
    public function it_returns_error_if_user_not_subscribed_to_lesson()
    {
        $user = User::factory()->create();
        Auth::shouldReceive('user')->andReturn($user);

        Course::factory(5)->create();
        $lesson = Lesson::factory()->create();

        $request = Request::create('/comments', 'POST', [
            'lesson_id' => $lesson->id,
            'comments' => 'Test comment'
        ]);
        $controller = new commentController(new UserCourseActivityService());

        $response = $controller->store($request);

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $this->assertEquals(403, $response->status());

        $responseData = $response->getData(true);
        $this->assertFalse($responseData['status']);
        $this->assertEquals('User is not subscribed to this course!.', $responseData['message']);
    }
}
