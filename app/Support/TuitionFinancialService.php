<?php

namespace App\Support;

use App\Models\ScholarshipActivation;
use App\Models\TuitionPayment;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class TuitionFinancialService
{
    private const SCHOLARSHIP_GRADE_TARGET = 80;

    private const SCHOLARSHIP_MONTHS_WINDOW = 2;

    private const SCHOLARSHIP_MULTI_COURSE_TARGET = 4;

    private const SCHOLARSHIP_MULTI_CHILD_TARGET = 3;

    private ?bool $hasTuitionPaymentsTable = null;

    private ?bool $hasCoursePriceColumn = null;

    private ?bool $hasStudentTuitionsTable = null;

    private ?bool $hasScholarshipActivationsTable = null;

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
    public function buildParentPageData(User $parent, ?string $selectedOfferKey = null): array
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

        $summariesByStudent = [];
        $invoices = [];
        $ledgerItems = [];
        $childBalances = [];
        $totalOutstandingBeforeDiscount = 0;

        foreach ($children as $child) {
            $summary = $this->summarizeStudent($child);
            $summariesByStudent[$child->id] = $summary;
            $childBalances[(int) $child->id] = (int) $summary['balance'];

            $totalOutstandingBeforeDiscount += $summary['balance'];

            foreach ($summary['ledger_items'] as $item) {
                $ledgerItems[] = [
                    'child_id' => (int) $child->id,
                    'child' => $child->name,
                    'name' => $item['name'] ?? 'Tuition Fee',
                    'period' => $item['period'] ?? $summary['academic_year'],
                    'amount' => (int) ($item['amount'] ?? 0),
                    'status' => $item['status'] ?? 'outstanding',
                ];
            }

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

        $scholarshipOffers = $this->buildParentScholarshipOffers($parent, $children, $summariesByStudent);
        $offerKeys = collect($scholarshipOffers)->pluck('key')->filter()->values();

        $activations = $this->hasScholarshipActivationsTable() && $offerKeys->isNotEmpty()
            ? ScholarshipActivation::query()
                ->where('parent_id', $parent->id)
                ->whereIn('offer_key', $offerKeys->all())
                ->orderByDesc('activated_at')
                ->get()
            : collect();

        $activationsByOffer = $activations->groupBy('offer_key');

        $childDiscountPercents = [];
        foreach ($children as $child) {
            $childDiscountPercents[(int) $child->id] = 0;
        }

        foreach ($activations as $activation) {
            $percent = (int) ($activation->discount_percent ?? 0);
            if ($percent <= 0) {
                continue;
            }

            if ((string) $activation->offer_key === 'family_3_children') {
                foreach (array_keys($childDiscountPercents) as $childId) {
                    $childDiscountPercents[$childId] += $percent;
                }

                continue;
            }

            $targetChildId = (int) ($activation->student_id ?? 0);
            if ($targetChildId > 0 && array_key_exists($targetChildId, $childDiscountPercents)) {
                $childDiscountPercents[$targetChildId] += $percent;
            }
        }

        $scholarshipOffers = collect($scholarshipOffers)->map(function (array $offer) use ($activationsByOffer): array {
            $offerActivations = $activationsByOffer->get($offer['key']);
            $latestActivation = $offerActivations instanceof Collection ? $offerActivations->first() : null;
            $activeStudentIds = $offerActivations instanceof Collection
                ? $offerActivations
                    ->pluck('student_id')
                    ->filter(fn ($id): bool => ! is_null($id))
                    ->map(fn ($id): int => (int) $id)
                    ->values()
                    ->all()
                : [];

            $offer['isActive'] = $latestActivation !== null;
            $offer['activatedAt'] = $latestActivation?->activated_at?->format('M d, Y');
            $offer['activeStudentIds'] = $activeStudentIds;

            return $offer;
        })->values()->all();

        $selectedOffer = collect($scholarshipOffers)->first(function (array $offer) use ($selectedOfferKey): bool {
            if ($selectedOfferKey !== null && $selectedOfferKey !== '') {
                return $offer['key'] === $selectedOfferKey;
            }

            return (bool) ($offer['isActive'] ?? false);
        });

        if (! is_array($selectedOffer)) {
            $selectedOffer = collect($scholarshipOffers)->first();
        }

        $activeDiscountPercent = empty($childDiscountPercents) ? 0 : max($childDiscountPercents);

        $discountAmount = 0;
        foreach ($childBalances as $childId => $childBalance) {
            $rate = (int) ($childDiscountPercents[(int) $childId] ?? 0);
            $discountAmount += (int) round(((int) $childBalance) * ($rate / 100));
        }
        $totalOutstanding = max($totalOutstandingBeforeDiscount - $discountAmount, 0);

        $ledgerItems = collect($ledgerItems)->map(function (array $item) use ($childDiscountPercents): array {
            $original = (int) ($item['amount'] ?? 0);
            $childId = (int) ($item['child_id'] ?? 0);
            $discountPercent = (int) ($childDiscountPercents[$childId] ?? 0);
            $discountValue = (int) round($original * ($discountPercent / 100));

            $item['discount_percent'] = $discountPercent;
            $item['discount_amount'] = $discountValue;
            $item['final_amount'] = max($original - $discountValue, 0);

            return $item;
        })->values()->all();

        $yearStart = now()->startOfYear();
        $totalPaid = (int) $payments
            ->filter(fn (TuitionPayment $payment): bool => $payment->paid_on !== null && $payment->paid_on->greaterThanOrEqualTo($yearStart))
            ->sum('amount');

        $paymentHistory = $payments->map(function (TuitionPayment $payment) use ($summariesByStudent, $childDiscountPercents): array {
            $studentSummary = $summariesByStudent[$payment->student_id] ?? null;
            $paymentChildDiscount = (int) ($childDiscountPercents[(int) $payment->student_id] ?? 0);

            return [
                'id' => $payment->reference ?: 'PAY-'.str_pad((string) $payment->id, 6, '0', STR_PAD_LEFT),
                'paymentId' => $payment->id,
                'child' => $payment->student?->name ?? 'Student',
                'description' => $studentSummary && ! empty($studentSummary['selected_course_name'])
                    ? 'Tuition Payment ('.$studentSummary['selected_course_name'].')'
                    : 'Tuition Payment',
                'amount' => (int) $payment->amount,
                'paidDate' => $payment->paid_on?->format('F j, Y') ?? '-',
                'method' => $this->methodLabel((string) $payment->method),
                'status' => 'paid',
                'discountApplied' => $paymentChildDiscount > 0 ? $paymentChildDiscount.'%' : 'None',
                'receiptUrl' => route('parent.financial.receipts.download', ['payment' => $payment->id]),
            ];
        })->values()->all();

        return [
            'academicYear' => $this->academicYearLabel(),
            'ledgerItems' => $ledgerItems,
            'children' => $children
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
                ->all(),
            'invoices' => $invoices,
            'paymentHistory' => $paymentHistory,
            'totalOutstanding' => $totalOutstanding,
            'totalOutstandingBeforeDiscount' => $totalOutstandingBeforeDiscount,
            'discountAmount' => $discountAmount,
            'totalPaid' => $totalPaid,
            'scholarshipOffers' => $scholarshipOffers,
            'selectedScholarshipOffer' => $selectedOffer,
            'activeScholarshipOffer' => collect($scholarshipOffers)->first(fn (array $offer): bool => (bool) ($offer['isActive'] ?? false)),
            'scholarshipDiscount' => $activeDiscountPercent,
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
     * @return array<int, array<string, mixed>>
     */
    private function buildParentScholarshipOffers(User $parent, Collection $children, array $summariesByStudent): array
    {
        $recentWindowStart = now()->subMonths(self::SCHOLARSHIP_MONTHS_WINDOW);

        $academicChildStats = [];
        foreach ($children as $child) {
            $records = $child->attendanceRecords()
                ->whereDate('attendance_date', '>=', $recentWindowStart)
                ->whereNotNull('grade')
                ->get(['grade']);

            $averageGrade = $records->isNotEmpty()
                ? (float) round((float) $records->avg('grade'), 1)
                : 0.0;

            $progressPercent = min(100, (int) round(($averageGrade / self::SCHOLARSHIP_GRADE_TARGET) * 100));
            $remaining = max(self::SCHOLARSHIP_GRADE_TARGET - $averageGrade, 0);

            $academicChildStats[(string) $child->id] = [
                'targetLabel' => $child->name,
                'progressPercent' => $progressPercent,
                'remainingText' => $remaining <= 0
                    ? 'Eligible now'
                    : number_format((float) $remaining, 1).' grade points left to unlock',
                'isEligible' => $averageGrade >= self::SCHOLARSHIP_GRADE_TARGET,
                'studentId' => $child->id,
            ];
        }

        $bestAcademic = collect($academicChildStats)
            ->sortByDesc(fn (array $row): int => (int) ($row['progressPercent'] ?? 0))
            ->first();

        $academicDiscount = [
            'key' => 'academic_progress_2m',
            'title' => 'Academic Excellence Grant',
            'description' => 'Average grade over the last '.self::SCHOLARSHIP_MONTHS_WINDOW.' months must reach '.self::SCHOLARSHIP_GRADE_TARGET.'/100.',
            'discountPercent' => 15,
            'targetLabel' => (string) ($bestAcademic['targetLabel'] ?? 'Best child progress'),
            'progressPercent' => (int) ($bestAcademic['progressPercent'] ?? 0),
            'remainingText' => (string) ($bestAcademic['remainingText'] ?? 'No grade records in the last 2 months'),
            'isEligible' => (bool) ($bestAcademic['isEligible'] ?? false),
            'studentId' => $bestAcademic['studentId'] ?? null,
            'childStats' => $academicChildStats,
        ];

        $multiCourseChildStats = [];
        foreach ($children as $child) {
            $summary = $summariesByStudent[$child->id] ?? null;
            $courseCount = (int) ($summary['enrolled_classes_count'] ?? 0);

            $progressPercent = min(100, (int) round(($courseCount / self::SCHOLARSHIP_MULTI_COURSE_TARGET) * 100));
            $remaining = max(self::SCHOLARSHIP_MULTI_COURSE_TARGET - $courseCount, 0);

            $multiCourseChildStats[(string) $child->id] = [
                'targetLabel' => $child->name,
                'progressPercent' => $progressPercent,
                'remainingText' => $remaining === 0
                    ? 'Eligible now'
                    : $remaining.' more course(s) needed',
                'isEligible' => $remaining === 0,
                'studentId' => $child->id,
            ];
        }

        $bestMultiCourse = collect($multiCourseChildStats)
            ->sortByDesc(fn (array $row): int => (int) ($row['progressPercent'] ?? 0))
            ->first();

        $multiCourseOffer = [
            'key' => 'multi_course_4_plus',
            'title' => 'Multi Course Commitment',
            'description' => 'Assign one child to at least '.self::SCHOLARSHIP_MULTI_COURSE_TARGET.' courses to unlock this discount.',
            'discountPercent' => 10,
            'targetLabel' => (string) ($bestMultiCourse['targetLabel'] ?? 'Any child'),
            'progressPercent' => (int) ($bestMultiCourse['progressPercent'] ?? 0),
            'remainingText' => (string) ($bestMultiCourse['remainingText'] ?? 'No data'),
            'isEligible' => (bool) ($bestMultiCourse['isEligible'] ?? false),
            'studentId' => $bestMultiCourse['studentId'] ?? null,
            'childStats' => $multiCourseChildStats,
        ];

        $childrenCount = $children->count();
        $multiChildProgress = min(100, (int) round(($childrenCount / self::SCHOLARSHIP_MULTI_CHILD_TARGET) * 100));
        $multiChildRemaining = max(self::SCHOLARSHIP_MULTI_CHILD_TARGET - $childrenCount, 0);

        $multiChildOffer = [
            'key' => 'family_3_children',
            'title' => 'Family Growth Offer',
            'description' => 'Enroll '.self::SCHOLARSHIP_MULTI_CHILD_TARGET.' children in the academy to unlock this family offer.',
            'discountPercent' => 12,
            'targetLabel' => 'Family enrollment',
            'progressPercent' => $multiChildProgress,
            'remainingText' => $multiChildRemaining === 0
                ? 'Eligible now'
                : $multiChildRemaining.' more child(ren) needed',
            'isEligible' => $multiChildRemaining === 0,
            'studentId' => null,
            'childStats' => [],
        ];

        return [$academicDiscount, $multiCourseOffer, $multiChildOffer];
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
        $classes = $student->enrolledClasses
            ->filter(fn ($courseClass): bool => $courseClass->course !== null)
            ->sortBy(fn ($courseClass): string => (string) $courseClass->course->name)
            ->values();

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

        $classes->each(function ($courseClass) use ($tuitionEntries, $studentTuitionCourseId): void {
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

        if ($grossDue === 0 && $studentTuition !== null) {
            $grossDue = max(0, (int) $studentTuition->course_price);
        }
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

    private function hasScholarshipActivationsTable(): bool
    {
        return $this->hasScholarshipActivationsTable ??= Schema::hasTable('scholarship_activations');
    }
}
