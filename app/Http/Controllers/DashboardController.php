<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Task;
use App\Support\DashboardMetrics;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private DashboardMetrics $metrics) {}

    public function __invoke(Request $request): View
    {
        $this->authorize('viewAny', Task::class);

        $actor = $request->user();

        return view('dashboard', [
            'pageTitle' => 'Dashboard',
            ...$this->metrics->for($actor),
        ]);
    }
}
