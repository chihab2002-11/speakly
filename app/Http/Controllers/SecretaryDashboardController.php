<?php

namespace App\Http\Controllers;

use App\Support\DashboardDataProvider;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SecretaryDashboardController extends Controller
{
    public function __construct(private DashboardDataProvider $dashboardDataProvider) {}

    public function index(Request $request): View
    {
        return view('dashboards.secretary', $this->dashboardDataProvider->forUser($request->user()));
    }
}
