<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminClassroomController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $rooms = Room::query()
            ->withCount('schedules')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where('name', 'like', '%'.$search.'%');
            })
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('admin.classrooms', [
            'rooms' => $rooms,
            'search' => $search,
            'totalClassrooms' => Room::query()->count(),
            'activeClassrooms' => Room::query()->whereHas('schedules')->count(),
            'unusedClassrooms' => Room::query()->whereDoesntHave('schedules')->count(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:rooms,name'],
        ]);

        Room::query()->create([
            'name' => $validated['name'],
            'location' => null,
            'capacity' => 30,
        ]);

        return redirect()
            ->route('admin.classrooms.index')
            ->with('success', 'Classroom added successfully.');
    }

    public function update(Request $request, Room $room): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:rooms,name,'.$room->id],
        ]);

        $room->update([
            'name' => $validated['name'],
            'location' => null,
        ]);

        return redirect()
            ->route('admin.classrooms.index')
            ->with('success', 'Classroom updated successfully.');
    }

    public function destroy(Room $room): RedirectResponse
    {
        if ($room->schedules()->exists()) {
            return redirect()
                ->route('admin.classrooms.index')
                ->with('error', 'Cannot delete classroom that is used in schedules.');
        }

        $room->delete();

        return redirect()
            ->route('admin.classrooms.index')
            ->with('success', 'Classroom deleted successfully.');
    }
}
