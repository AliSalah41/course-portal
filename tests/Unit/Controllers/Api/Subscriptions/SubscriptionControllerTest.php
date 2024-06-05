<?php

namespace Tests\Unit\Controllers\Api\Subscriptions;

use App\Http\Controllers\Api\Subscriptions\SubscriptionController;
use App\Models\Course;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class SubscriptionControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_subscribes_user_to_course()
    {
        $user = User::factory()->create();
        Auth::shouldReceive('user')->andReturn($user);

        $course = Course::factory()->create();

        $request = Request::create('/subscribe', 'POST', ['course_id' => $course->id]);
        $controller = new SubscriptionController();

        $response = $controller->subscribe($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(201, $response->status());

        $this->assertTrue(Subscription::where('user_id', $user->id)->where('course_id', $course->id)->exists());
    }

    /** @test */
    public function it_returns_error_if_course_not_found()
    {
        $request = Request::create('/subscribe', 'POST', ['course_id' => 999]); // Course with id 999 doesn't exist
        $controller = new SubscriptionController();

        $response = $controller->subscribe($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(404, $response->status());

        $responseData = $response->getData(true);
        $this->assertFalse($responseData['status']);
        $this->assertEquals('course not found', $responseData['message']);
    }

    /** @test */
    public function it_returns_error_if_user_already_subscribed_to_course()
    {
        $user = User::factory()->create();
        Auth::shouldReceive('user')->andReturn($user);
        $course = Course::factory()->create();
        Subscription::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);

        $request = Request::create('/subscribe', 'POST', ['course_id' => $course->id]);
        $controller = new SubscriptionController();

        $response = $controller->subscribe($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(400, $response->status());

        $responseData = $response->getData(true);
        $this->assertFalse($responseData['status']);
        $this->assertEquals('You are already subscribed to this course', $responseData['message']);
    }
}
