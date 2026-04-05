<?php

namespace App\Http\Controllers;

use App\Support\DashboardDataProvider;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ParentDashboardController extends Controller
{
    public function __construct(private DashboardDataProvider $dashboardDataProvider) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        // Placeholder children data (will be replaced by backend)
        $children = [
            [
                'id' => 1,
                'name' => 'Alex Johnson',
                'initials' => 'A',
                'grade' => 'Grade 10',
                'stream' => 'Science Stream',
                'gpa' => '3.8',
                'status' => 'On Track',
                'color' => 'var(--lumina-child-1)',
                'textColor' => 'var(--lumina-child-1-text)',
            ],
            [
                'id' => 2,
                'name' => 'Sophie Johnson',
                'initials' => 'S',
                'grade' => 'Grade 8',
                'stream' => 'Arts Stream',
                'gpa' => '3.6',
                'status' => 'On Track',
                'color' => 'var(--lumina-child-2)',
                'textColor' => 'var(--lumina-child-2-text)',
            ],
        ];

        $totalOutstanding = 245000;
        $payments = [
            ['child' => 'Alex', 'term' => 'Term 3', 'amount' => 122500],
            ['child' => 'Sophie', 'term' => 'Term 3', 'amount' => 122500],
        ];

        // Merge base dashboard data with parent-specific data
        $baseData = $this->dashboardDataProvider->forUser($user);

        return view('parent.dashboard', array_merge($baseData, [
            'user' => $user,
            'children' => $children,
            'selectedChild' => $children[0] ?? null,
            'academicTerm' => 'Spring 2024',
            'totalOutstanding' => $totalOutstanding,
            'payments' => $payments,
            'documentsCount' => 12,
            'teachersCount' => 8,
            'eventsCount' => 3,
        ]));
    }
}
