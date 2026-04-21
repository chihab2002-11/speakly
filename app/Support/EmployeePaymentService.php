<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Collection;

class EmployeePaymentService
{
    /**
     * @return array{
     *     rows: Collection<int, array<string, mixed>>,
     *     totals: array<string, int>
     * }
     */
    public function overview(string $role, string $status, string $search): array
    {
        $employees = User::query()
            ->role($this->roleFilter($role))
            ->whereNotNull('approved_at')
            ->with(['roles:id,name', 'employeePayment'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($innerQuery) use ($search): void {
                    $innerQuery
                        ->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')
                        ->orWhere('phone', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('name')
            ->get();

        $rows = $employees
            ->map(fn (User $employee): array => $this->employeeRow($employee))
            ->when($status !== 'all', fn (Collection $rows): Collection => $rows->where('status', $status)->values())
            ->values();

        return [
            'rows' => $rows,
            'totals' => [
                'total_employees' => $rows->count(),
                'total_salaries' => (int) $rows->sum('expected_salary'),
                'total_paid' => (int) $rows->sum('amount_paid'),
                'total_remaining' => (int) $rows->sum('remaining'),
                'count_paid' => $rows->where('status', 'paid')->count(),
                'count_unpaid' => $rows->where('status', 'unpaid')->count(),
                'count_partial' => $rows->where('status', 'partial')->count(),
                'count_pending' => $rows->where('status', 'pending')->count(),
            ],
        ];
    }

    /**
     * @return list<string>
     */
    private function roleFilter(string $role): array
    {
        return match ($role) {
            'teacher' => ['teacher'],
            'secretary' => ['secretary'],
            default => ['teacher', 'secretary'],
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function employeeRow(User $employee): array
    {
        $expectedSalary = (int) ($employee->employeePayment?->expected_salary ?? 0);
        $amountPaid = (int) ($employee->employeePayment?->amount_paid ?? 0);
        $remaining = max($expectedSalary - $amountPaid, 0);

        return [
            'employee' => $employee,
            'role' => $this->displayRole($employee),
            'expected_salary' => $expectedSalary,
            'amount_paid' => $amountPaid,
            'remaining' => $remaining,
            'status' => $this->statusFor($expectedSalary, $amountPaid),
            'notes' => $employee->employeePayment?->notes,
            'updated_at' => $employee->employeePayment?->updated_at,
        ];
    }

    private function displayRole(User $employee): string
    {
        $employee->loadMissing('roles:id,name');

        if ($employee->hasRole('teacher')) {
            return 'Teacher';
        }

        return 'Secretary';
    }

    private function statusFor(int $expectedSalary, int $amountPaid): string
    {
        if ($expectedSalary <= 0) {
            return 'pending';
        }

        if ($amountPaid <= 0) {
            return 'unpaid';
        }

        if ($amountPaid >= $expectedSalary) {
            return 'paid';
        }

        return 'partial';
    }
}
