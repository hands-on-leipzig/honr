<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EarnedBadge;

class AdminEarnedBadgeController extends Controller
{
    public function index()
    {
        return response()->json(
            EarnedBadge::with([
                'user:id,nickname,email',
                'badge:id,name,type',
                'currentThreshold:id,level_name,threshold_value',
            ])
                ->orderBy('earned_at', 'desc')
                ->get()
        );
    }
}

