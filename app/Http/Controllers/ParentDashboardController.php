<?php

namespace App\Http\Controllers;

use App\Providers\ParentDashboardDataProvider;
use App\Support\DashboardDataProvider;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ParentDashboardController extends Controller
{
    public function __construct(
        private DashboardDataProvider $dashboardDataProvider,
        private ParentDashboardDataProvider $parentDashboardDataProvider,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $selectedChildId = $request->integer('child');

        // Merge base dashboard data with parent-specific data
        $baseData = $this->dashboardDataProvider->forUser($user);
        $parentData = $this->parentDashboardDataProvider->forParent($user, $selectedChildId);

        return view('parent.dashboard', array_merge($baseData, $parentData, [
            'user' => $user,
        ]));
    }
}
