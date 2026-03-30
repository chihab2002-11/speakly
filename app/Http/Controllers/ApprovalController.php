<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    public function index(Request $request)
    {
        // Only admin/secretary can access approvals page
        abort_unless($request->user()->hasAnyRole(['admin', 'secretary']), 403);

        $pendingUsers = User::query()
            ->whereNull('approved_at')
            ->whereNull('rejected_at') // exclude rejected users
            ->whereNotNull('requested_role')
            ->orderBy('created_at')
            ->get();

        return view('approvals.index', compact('pendingUsers'));
    }

    public function approve(Request $request, User $user)
    {
        // Only admin/secretary can approve
        abort_unless($request->user()->hasAnyRole(['admin', 'secretary']), 403);

        // already approved -> nothing
        if ($user->approved_at !== null) {
            return redirect()->route('approvals.index');
        }

        // rejected users should not be approved directly
        if ($user->rejected_at !== null) {
            return back()->with('error', 'User is already rejected.');
        }

        $requestedRole = $user->requested_role;

        if (! $requestedRole) {
            return back()->with('error', 'User has no requested role.');
        }

        // Authorization rules:
        // - admin can approve anyone
        // - secretary can approve only student + parent
        if ($request->user()->hasRole('admin')) {
            // allowed for any requested_role
        } elseif ($request->user()->hasRole('secretary')) {
            abort_unless(in_array($requestedRole, ['student', 'parent'], true), 403);
        }

        $user->forceFill([
            'approved_at' => now(),
            'approved_by' => $request->user()->id,
        ])->save();

        // Assign the real role now (Spatie)
        $user->syncRoles([$requestedRole]);

        return redirect()
            ->route('approvals.index')
            ->with('success', 'User approved.');
    }

    public function reject(Request $request, User $user)
    {
        // Only admin/secretary can reject
        abort_unless($request->user()->hasAnyRole(['admin', 'secretary']), 403);

        // already approved -> cannot reject
        if ($user->approved_at !== null) {
            return back()->with('error', 'User is already approved.');
        }

        // already rejected -> nothing
        if ($user->rejected_at !== null) {
            return back()->with('error', 'User is already rejected.');
        }

        $requestedRole = $user->requested_role;

        if (! $requestedRole) {
            return back()->with('error', 'User has no requested role.');
        }

        // Authorization rules:
        // - admin can reject anyone
        // - secretary can reject only student + parent
        if ($request->user()->hasRole('admin')) {
            // allowed for any requested_role
        } elseif ($request->user()->hasRole('secretary')) {
            abort_unless(in_array($requestedRole, ['student', 'parent'], true), 403);
        }

        $user->forceFill([
            'rejected_at' => now(),
            'rejected_by' => $request->user()->id,
            'rejection_reason' => $request->input('reason'),
        ])->save();

        return redirect()
            ->route('approvals.index')
            ->with('success', 'User rejected.');
    }
}
