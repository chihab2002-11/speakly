<?php

namespace App\Support;

use App\Models\TuitionPayment;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class TuitionFinancialService
{
    private ?bool $hasTuitionPaymentsTable = null;

    private ?bool $hasCoursePriceColumn = null;

    private ?bool $hasStudentTuitionsTable = null;

    public function canRecordPayments(): bool
    {
        return $this->hasTuitionPaymentsTable();
    }

    public function hasCoursePricing(): bool
    {
        return $this->hasCoursePriceColumn();
    }

    /**
     * @param  Collection<int, User>  $students
     * @return Collection<int, array<string, mixed>>
     */
    public function buildSecretaryRows(Collection $students): Collection
    {
        return $students->map(fn (User $student): array => $this->buildSecretaryRowForStudent($student));
    }

    /**
     * @return array<string, mixed>
     */
    public function buildStudentPageData(User $student): array
    {
        $summary = $this->summarizeStudent($student);

        return [
            'academicYear' => $summary['academic_year'],
            'ledgerItems' => $summary['ledger_items'],
            'receipts' => $summary['receipts'],
            'totalOutstanding' => $summary['balance'],
            'totalCoursesPrice' => $summary['gross_due'],
            'totalPaid' => $summary['amount_paid'],
            'totalRemaining' => $summary['balance'],
            'paidPercentage' => $summary['paid_percentage'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function buildParentPageData(User $parent): array
    {
        $children = User::query()
            ->where('parent_id', $parent->id)
            ->whereNotNull('approved_at')
            ->whereHas('roles', function ($query): void {
                $query->where('name', 'student');
            })
            ->with($this->studentFinancialRelations())
            ->orderBy('name')
            ->get();

        $childCards = $children
            ->values()
            ->map(function (User $child, int $index): array {
                $theme = $index % 2 === 0
                    ? ['color' => 'var(--lumina-child-1)', 'textColor' => 'var(--lumina-child-1-text)']
                    : ['color' => 'var(--lumina-child-2)', 'textColor' => 'var(--lumina-child-2-text)'];

                return [
                    'id' => $child->id,
                    'name' => $child->name,
                    'initials' => $child->initials(),
                    'grade' => 'Student',
                    'stream' => 'Language Track',
                    'gpa' => '-',
                    'status' => 'Active',
                    'color' => $theme['color'],
                    'textColor' => $theme['textColor'],
                ];
            })
            ->all();

        $summariesByStudent = [];
        $invoices = [];
        $totalOutstanding = 0;

        foreach ($children as $child) {
            $summary = $this->summarizeStudent($child);
            $summariesByStudent[$child->id] = $summary;

            $totalOutstanding += $summary['balance'];

            if ($summary['balance'] <= 0) {
                continue;
            }

            $invoices[] = [
                'id' => 'INV-'.now()->format('Y').'-'.str_pad((string) $child->id, 4, '0', STR_PAD_LEFT),
                'child' => $child->name,
                'description' => ($summary['selected_course_name'] ?? 'Course').' Tuition Balance',
                'amount' => $summary['balance'],
                'dueDate' => now()->endOfMonth()->format('F j, Y'),
                'status' => 'pending',
            ];
        }

        $childIds = $children->pluck('id');
        $payments = $this->hasTuitionPaymentsTable()
            ? TuitionPayment::query()
                ->when($childIds->isNotEmpty(), fn ($query) => $query->whereIn('student_id', $childIds), fn ($query) => $query->whereRaw('1 = 0'))
                ->with('student:id,name')
                ->orderByDesc('paid_on')
                ->orderByDesc('id')
                ->get()
            : collect();

        $yearStart = now()->startOfYear();

        $totalPaid = (int) $payments
            ->filter(fn (TuitionPayment $payment): bool => $payment->paid_on !== null && $payment->paid_on->greaterThanOrEqualTo($yearStart))
            ->sum('amount');

        $paymentHistory = $payments->map(function (TuitionPayment $payment) use ($summariesByStudent): array {
            $studentSummary = $summariesByStudent[$payment->student_id] ?? null;

            return [
                'id' => $payment->reference ?: 'PAY-'.str_pad((string) $payment->id, 6, '0', STR_PAD_LEFT),
                'child' => $payment->student?->name ?? 'Student',
                'description' => $studentSummary && ! empty($studentSummary['selected_course_name'])
                    ? 'Tuition Payment ('.$studentSummary['selected_course_name'].')'
                    : 'Tuition Payment',
                'amount' => (int) $payment->amount,
                'paidDate' => $payment->paid_on?->format('F j, Y') ?? '-',
                'method' => $this->methodLabel((string) $payment->method),
            ];
        })->values()->all();

        return [
            'children' => $childCards,
            'invoices' => $invoices,
            'paymentHistory' => $paymentHistory,
            'totalOutstanding' => $totalOutstanding,
            'totalPaid' => $totalPaid,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildSecretaryRowForStudent(User $student): array
    {
        $summary = $this->summarizeStudent($student);

        return [
            'student' => $student,
            'academic_year' => $summary['academic_year'],
            'gross_due' => $summary['gross_due'],
            'net_due' => $summary['net_due'],
            'selected_course_name' => $summary['selected_course_name'],
            'selected_course_code' => $summary['selected_course_code'],
            'course_price' => $summary['course_price'],
            'amount_paid' => $summary['amount_paid'],
            'balance' => $summary['balance'],
            'status' => $summary['status'],
            'due_date' => now()->endOfMonth(),
            'last_payment_at' => $summary['last_payment_at'],
            'enrolled_classes_count' => $summary['enrolled_classes_count'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function summarizeStudent(User $student): array
    {
        $student->loadMissing($this->studentFinancialRelations());

        $payments = $this->hasTuitionPaymentsTable()
            ? $student->tuitionPaymentsAsStudent
            : collect();
        $amountPaid = (int) $payments->sum('amount');
        $academicYear = $this->academicYearLabel();
        $studentTuition = $this->hasStudentTuitionsTable() ? $student->studentTuition : null;

        $tuitionEntries = collect();
        $studentTuitionCourseId = null;

        if ($studentTuition !== null) {
            $studentTuitionCourseId = $studentTuition->course_id;

            $tuitionEntries->push([
                'course_id' => $studentTuitionCourseId,
                'name' => (string) ($studentTuition->course?->name ?? 'Selected Course'),
                'code' => $studentTuition->course?->code,
                'price' => max(0, (int) $studentTuition->course_price),
            ]);
        }

        $student->enrolledClasses
            ->filter(fn ($courseClass): bool => $courseClass->course !== null)
            ->sortBy(fn ($courseClass): string => (string) $courseClass->course->name)
            ->each(function ($courseClass) use ($tuitionEntries, $studentTuitionCourseId): void {
                $course = $courseClass->course;

                if ($course === null || $course->id === $studentTuitionCourseId) {
                    return;
                }

                $coursePrice = 0;

                if ($this->hasCoursePriceColumn() && array_key_exists('price', $course->getAttributes())) {
                    $coursePrice = max(0, (int) ($course->price ?? 0));
                }

                $tuitionEntries->push([
                    'course_id' => $course->id,
                    'name' => (string) $course->name,
                    'code' => $course->code,
                    'price' => $coursePrice,
                ]);
            });

        $tuitionEntries = $tuitionEntries
            ->sortBy(fn (array $entry): string => (string) $entry['name'])
            ->values();

        $grossDue = (int) $tuitionEntries->sum('price');
        $balance = max($grossDue - $amountPaid, 0);
        $status = $balance === 0 ? 'paid' : 'pending';
        $paidPercentage = $grossDue === 0 ? 0 : (int) round(($amountPaid / $grossDue) * 100);

        $remainingPaid = $amountPaid;

        $ledgerItems = $tuitionEntries->map(function (array $entry) use (&$remainingPaid, $academicYear): array {
            $coursePrice = max(0, (int) $entry['price']);
            $paidForCourse = min($remainingPaid, $coursePrice);
            $remainingPaid = max(0, $remainingPaid - $coursePrice);
            $remainingForCourse = max($coursePrice - $paidForCourse, 0);

            return [
                'name' => (string) $entry['name'],
                'type' => 'Course Fee',
                'period' => $academicYear,
                'amount' => $coursePrice,
                'paid' => $paidForCourse,
                'remaining' => $remainingForCourse,
                'status' => $remainingForCourse === 0 ? 'paid' : 'outstanding',
                'icon' => 'course',
            ];
        })->all();
        $selectedCourse = $tuitionEntries->count() === 1 ? $tuitionEntries->first() : null;

        return [
            'academic_year' => $academicYear,
            'gross_due' => $grossDue,
            'discount' => 0,
            'net_due' => $grossDue,
            'course_price' => $tuitionEntries->count() === 1
                ? max(0, (int) ($selectedCourse['price'] ?? 0))
                : $grossDue,
            'selected_course_name' => $tuitionEntries->isEmpty()
                ? 'No course selected'
                : ($tuitionEntries->count() === 1
                    ? (string) ($selectedCourse['name'] ?? 'Selected Course')
                    : $tuitionEntries->pluck('name')->implode(', ')),
            'selected_course_code' => $tuitionEntries->count() === 1 ? ($selectedCourse['code'] ?? null) : null,
            'amount_paid' => $amountPaid,
            'balance' => $balance,
            'paid_percentage' => $paidPercentage,
            'status' => $status,
            'last_payment_at' => $payments->first()?->paid_on,
            'enrolled_classes_count' => (int) $tuitionEntries->count(),
            'ledger_items' => $ledgerItems,
            'receipts' => $this->buildReceipts($payments),
        ];
    }

    private function academicYearLabel(): string
    {
        $now = now();
        $startYear = $now->month >= 9 ? $now->year : $now->year - 1;

        return $startYear.'/'.($startYear + 1);
    }

    private function methodLabel(string $method): string
    {
        return match ($method) {
            'bank_transfer' => 'Bank Transfer',
            'card' => 'Card',
            'online' => 'Online',
            default => 'Cash',
        };
    }

    /**
     * @return array<int|string, mixed>
     */
    private function studentFinancialRelations(): array
    {
        $relations = ['enrolledClasses.course'];

        if ($this->hasStudentTuitionsTable()) {
            $relations[] = 'studentTuition.course';
        }

        if ($this->hasTuitionPaymentsTable()) {
            $relations['tuitionPaymentsAsStudent'] = fn ($query) => $query->orderByDesc('paid_on')->orderByDesc('id');
        }

        return $relations;
    }

    private function hasTuitionPaymentsTable(): bool
    {
        return $this->hasTuitionPaymentsTable ??= Schema::hasTable('tuition_payments');
    }

    private function hasCoursePriceColumn(): bool
    {
        return $this->hasCoursePriceColumn ??= Schema::hasTable('courses') && Schema::hasColumn('courses', 'price');
    }

    /**
     * @param  Collection<int, TuitionPayment>  $payments
     * @return array<int, array<string, mixed>>
     */
    private function buildReceipts(Collection $payments): array
    {
        return $payments->map(function (TuitionPayment $payment): array {
            $referenceDigits = preg_replace('/\D+/', '', (string) $payment->reference);
            $last4 = null;

            if ((string) $payment->method === 'card' && $referenceDigits !== null && strlen($referenceDigits) >= 4) {
                $last4 = substr($referenceDigits, -4);
            }

            return [
                'payment_id' => $payment->id,
                'invoice' => $payment->reference ?: 'PAY-'.str_pad((string) $payment->id, 6, '0', STR_PAD_LEFT),
                'amount' => (float) $payment->amount,
                'date' => $payment->paid_on?->format('M d, Y') ?? '-',
                'method' => $this->methodLabel((string) $payment->method),
                'last4' => $last4,
            ];
        })->all();
    }

    private function hasStudentTuitionsTable(): bool
    {
        return $this->hasStudentTuitionsTable ??= Schema::hasTable('student_tuitions');
    }
}
