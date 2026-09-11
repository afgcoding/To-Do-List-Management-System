<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(): View
    {
        $activityLogs = ActivityLog::query()
            ->with(['task', 'user'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('activity-logs.index', [
            'activityLogs' => $activityLogs,
        ]);
    }
}
