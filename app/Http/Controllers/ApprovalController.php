<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Notifications\AccountApprovedNotification;
use App\Notifications\AccountRejectedNotification;

class ApprovalController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->hasAnyRole(['admin', 'secretary']), 403);

        $pendingUsers = User::query()
            ->whereNull('approved_at')
            ->whereNull('rejected_at')
            ->whereNotNull('requested_role')
            ->orderBy('created_at')
            ->get();

        return view('approvals.index', compact('pendingUsers'));
    }

    public function approve(Request $request, User $user)
    {
        abort_unless($request->user()->hasAnyRole(['admin', 'secretary']), 403);

        if ($user->approved_at !== null) {
            return redirect()->route('approvals.index');
        }

        if ($user->rejected_at !== null) {
            return back()->with('error', 'User is already rejected.');
        }

        $requestedRole = $user->requested_role;

        if (! $requestedRole) {
            return back()->with('error', 'User has no requested role.');
        }

        if ($request->user()->hasRole('admin')) {
            // allowed
        } elseif ($request->user()->hasRole('secretary')) {
            abort_unless(in_array($requestedRole, ['student', 'parent'], true), 403);
        }

        $user->forceFill([
            'approved_at' => now(),
            'approved_by' => $request->user()->id,
        ])->save();

        $user->syncRoles([$requestedRole]);

        // ✅ notify approved user
        $user->notify(new AccountApprovedNotification());

        return redirect()
            ->route('approvals.index')
            ->with('success', 'User approved.');
    }

    public function reject(Request $request, User $user)
    {
        abort_unless($request->user()->hasAnyRole(['admin', 'secretary']), 403);

        if ($user->approved_at !== null) {
            return back()->with('error', 'User is already approved.');
        }

        if ($user->rejected_at !== null) {
            return back()->with('error', 'User is already rejected.');
        }

        $requestedRole = $user->requested_role;

        if (! $requestedRole) {
            return back()->with('error', 'User has no requested role.');
        }

        if ($request->user()->hasRole('admin')) {
            // allowed
        } elseif ($request->user()->hasRole('secretary')) {
            abort_unless(in_array($requestedRole, ['student', 'parent'], true), 403);
        }

        $user->forceFill([
            'rejected_at' => now(),
            'rejected_by' => $request->user()->id,
            'rejection_reason' => $request->input('reason'),
        ])->save();

        // ✅ notify rejected user
        $user->notify(new AccountRejectedNotification($request->input('reason')));

        return redirect()
            ->route('approvals.index')
            ->with('success', 'User rejected.');
    }
}
