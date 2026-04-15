<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Collection;

class SecretaryPaymentEstimator
{
    /**
     * @param  Collection<int, User>  $students
     * @return Collection<int, array<string, mixed>>
     */
    public function estimateMany(Collection $students): Collection
    {
        return $students->map(fn (User $student): array => $this->estimateForStudent($student));
    }

    /**
     * @return array<string, mixed>
     */
    public function estimateForStudent(User $student): array
    {
        $enrolledClassesCount = (int) ($student->enrolled_classes_count ?? $student->enrolledClasses()->count());

        $baseTuition = 18000 + ($enrolledClassesCount * 2200);
        $resourceFee = 1200;
        $grossDue = $baseTuition + $resourceFee;

        $scholarshipPercent = match ($student->id % 5) {
            0 => 20,
            1 => 10,
            default => 0,
        };

        $discount = (int) round($grossDue * ($scholarshipPercent / 100));
        $netDue = max($grossDue - $discount, 0);

        $paymentProgress = match ($student->id % 4) {
            0 => 1.0,
            1 => 0.75,
            2 => 0.5,
            default => 0.25,
        };

        if ($student->id % 7 === 0) {
            $paymentProgress = 0.0;
        }

        $amountPaid = (int) round($netDue * $paymentProgress);
        $balance = max($netDue - $amountPaid, 0);
        $status = $balance === 0 ? 'paid' : 'pending';

        return [
            'student' => $student,
            'academic_year' => now()->year.'/'.(now()->year + 1),
            'gross_due' => $grossDue,
            'discount' => $discount,
            'net_due' => $netDue,
            'amount_paid' => $amountPaid,
            'balance' => $balance,
            'status' => $status,
            'due_date' => now()->startOfMonth()->addDays(($student->id % 18) + 7),
            'last_payment_at' => $amountPaid > 0 ? now()->subDays(($student->id % 45) + 3) : null,
            'enrolled_classes_count' => $enrolledClassesCount,
        ];
    }
}
