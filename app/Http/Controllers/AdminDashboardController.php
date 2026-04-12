<?php

namespace App\Http\Controllers;

use App\Models\LanguageProgram;
use App\Support\DashboardDataProvider;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __construct(private DashboardDataProvider $dashboardDataProvider) {}

    public function index(Request $request): View
    {
        $programs = LanguageProgram::query()->ordered()->get();

        return view('dashboards.admin', [
            ...$this->dashboardDataProvider->forUser($request->user()),
            'user' => $request->user(),
            'programs' => $programs,
            'activeProgramsCount' => $programs->where('is_active', true)->count(),
        ]);
    }
}
