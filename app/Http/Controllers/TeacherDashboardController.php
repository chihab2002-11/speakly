<?php

namespace App\Http\Controllers;

use App\Support\DashboardDataProvider;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeacherDashboardController extends Controller
{
    public function __construct(private DashboardDataProvider $dashboardDataProvider) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $dashboardData = $this->dashboardDataProvider->forUser($user);

        // Mock data for teacher dashboard (will be replaced by real backend data)
        $teacherData = [
            'user' => $user,
            'todaysClasses' => 4,
            'totalStudents' => 127,
            'avgAttendance' => 94,
            'pendingTasks' => 3,
            'todaySchedule' => [
                ['time' => '08:00 - 09:30', 'subject' => 'French B2 - Grammar', 'room' => 'Room 101', 'students' => 24, 'status' => 'completed'],
                ['time' => '10:00 - 11:30', 'subject' => 'French A2 - Conversation', 'room' => 'Room 203', 'students' => 18, 'status' => 'current'],
                ['time' => '13:00 - 14:30', 'subject' => 'Spanish B1 - Writing', 'room' => 'Lab 2', 'students' => 22, 'status' => 'upcoming'],
                ['time' => '15:00 - 16:30', 'subject' => 'French C1 - Advanced', 'room' => 'Room 101', 'students' => 15, 'status' => 'upcoming'],
            ],
            'quickResources' => [
                ['name' => 'Grammar Worksheet B2', 'type' => 'PDF', 'size' => '2.4 MB', 'color' => '#DC2626'],
                ['name' => 'Vocabulary List A2', 'type' => 'PDF', 'size' => '1.1 MB', 'color' => '#DC2626'],
                ['name' => 'Audio Materials Pack', 'type' => 'ZIP', 'size' => '45 MB', 'color' => '#D97706'],
            ],
            'recentMessages' => [
                ['name' => 'Julian Alvarez', 'message' => "I've sent the linguistics report for review...", 'time' => '10 min ago', 'unread' => true, 'online' => true],
                ['name' => 'Elena Vance', 'message' => 'Check out the new vocabulary list!', 'time' => '2 hours ago', 'unread' => false, 'online' => false],
                ['name' => 'Marcus Chen', 'message' => 'Are we still meeting at the library at 5?', 'time' => 'Yesterday', 'unread' => true, 'online' => true],
            ],
        ];

        return view('teacher.dashboard', array_merge($dashboardData, $teacherData));
    }
}
