<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\AccountApprovedNotification;
use App\Notifications\AccountRejectedNotification;
use App\Support\DashboardRedirector;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    public function index(Request $request, string $role)
    {
        abort_unless($request->user()->hasAnyRole(['admin', 'secretary']), 403);

        $pendingUsers = User::query()
            ->whereNull('approved_at')
            ->whereNull('rejected_at')
            ->whereNotNull('requested_role')
            ->orderBy('created_at')
            ->get();

        return view('approvals.index', [
            'pendingUsers' => $pendingUsers,
            'currentRole' => $role,
        ]);
    }

    public function approve(Request $request, string $role, User $user)
    {
        abort_unless($request->user()->hasAnyRole(['admin', 'secretary']), 403);

        if ($user->approved_at !== null) {
            return redirect()->route('approvals.index', $this->approvalsRouteParameters($request, $role));
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
        $user->notify(new AccountApprovedNotification);

        return redirect()
            ->route('approvals.index', $this->approvalsRouteParameters($request, $role))
            ->with('success', 'User approved.');
    }

    public function reject(Request $request, string $role, User $user)
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
            ->route('approvals.index', $this->approvalsRouteParameters($request, $role))
            ->with('success', 'User rejected.');
    }

    private function routeRole(Request $request): string
    {
        $routeRole = (string) $request->route('role');

        if ($routeRole !== '') {
            return $routeRole;
        }

        return DashboardRedirector::roleFor($request->user());
    }

    /**
     * @return array<string, string>
     */
    private function approvalsRouteParameters(Request $request, string $role = ''): array
    {
        return ['role' => $role !== '' ? $role : $this->routeRole($request)];
    }
}
