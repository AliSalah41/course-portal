<?php

namespace App\Http\Controllers\Api\Badges;

use App\Http\Controllers\Controller;
use App\Services\UserCourseActivityService;
use Illuminate\Http\Request;

class badgeController extends Controller
{
    protected $userCourseActivityService;

    public function __construct(UserCourseActivityService $userCourseActivityService)
    {
        $this->userCourseActivityService = $userCourseActivityService;
    }

    
}
