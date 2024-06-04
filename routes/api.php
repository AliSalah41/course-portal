<?php

use App\Http\Controllers\Api\Comments\commentController;
use App\Http\Controllers\Api\Lessons\lessonController;
use App\Http\Controllers\Api\Subscriptions\subscriptionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:sanctum'])->group(function (){

    Route::post('subscribe',[subscriptionController::class,'subscribe']);
    Route::get('showLesson/{id}',[lessonController::class,'show']);
    Route::post('ConfirmWatch',[lessonController::class,'watchConfirm']);
    Route::post('commentStore',[commentController::class,'store']);
});

Route::get('allLessons/{courseId}',[lessonController::class,'index']);
