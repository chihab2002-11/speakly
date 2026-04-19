<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\AccountApprovedNotification;
use App\Notifications\AccountRejectedNotification;
use App\Support\DashboardRedirector;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    /**
     * @var array<string, list<string>>
     */
    private const APPROVAL_SCOPES = [
        'standard' => ['student', 'parent', 'teacher'],
        'office' => ['secretary'],
    ];

    public function index(Request $request, string $role)
    {
        $manageableRequestedRoles = $this->manageableRequestedRoles($request);

        abort_if($manageableRequestedRoles === [], 403);

        $pendingUsersQuery = User::query()
            ->whereNull('approved_at')
            ->whereNull('rejected_at')
            ->whereNotNull('requested_role')
            ->whereIn('requested_role', $manageableRequestedRoles)
            ->orderBy('created_at');

        $pendingUsers = $pendingUsersQuery->get();

        return view('approvals.index', [
            'pendingUsers' => $pendingUsers,
            'currentRole' => $role,
        ]);
    }

    public function approve(Request $request, string $role, User $user)
    {
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

        abort_unless($this->canManageRequestedRole($request, $requestedRole, 'approve'), 403);

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

        abort_unless($this->canManageRequestedRole($request, $requestedRole, 'reject'), 403);

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

    private function canManageRequestedRole(Request $request, ?string $requestedRole, string $action): bool
    {
        if ($requestedRole === null) {
            return false;
        }

        $permission = $this->permissionForRequestedRole($requestedRole, $action);

        if ($permission === null) {
            return false;
        }

        return $request->user()->can($permission);
    }

    /**
     * @return list<string>
     */
    private function manageableRequestedRoles(Request $request): array
    {
        $manageableRequestedRoles = [];

        foreach (self::APPROVAL_SCOPES as $scope => $requestedRoles) {
            if (
                $request->user()->can("approvals.approve.{$scope}")
                || $request->user()->can("approvals.reject.{$scope}")
            ) {
                $manageableRequestedRoles = [...$manageableRequestedRoles, ...$requestedRoles];
            }
        }

        return array_values(array_unique($manageableRequestedRoles));
    }

    private function permissionForRequestedRole(string $requestedRole, string $action): ?string
    {
        foreach (self::APPROVAL_SCOPES as $scope => $requestedRoles) {
            if (in_array($requestedRole, $requestedRoles, true)) {
                return "approvals.{$action}.{$scope}";
            }
        }

        return null;
    }

    /**
     * @return array<string, string>
     */
    private function approvalsRouteParameters(Request $request, string $role = ''): array
    {
        return ['role' => $role !== '' ? $role : $this->routeRole($request)];
    }
}
