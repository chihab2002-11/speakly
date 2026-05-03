<?php

namespace App\Support;

use App\Models\Course;
use App\Models\CourseClass;
use App\Models\Schedule;
use App\Models\TeacherResource;
use App\Models\TuitionPayment;
use App\Models\User;
use App\Notifications\ClassResourceUploadedNotification;
use App\Notifications\EmployeePaymentRecordedNotification;
use App\Notifications\ScheduleChangedNotification;
use App\Notifications\StudentGroupEnrollmentChangedNotification;
use App\Notifications\TeacherGroupAssignedNotification;
use App\Notifications\TuitionPaymentRecordedNotification;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Route;

class RoleNotificationService
{
    /**
     * @param  array<string, mixed>  $paymentData
     */
    public function notifyEmployeePaymentChanged(User $employee, ?int $previousAmountPaid, array $paymentData): void
    {
        $amountPaid = (int) ($paymentData['amount_paid'] ?? 0);

        if ($previousAmountPaid !== null && $previousAmountPaid === $amountPaid) {
            return;
        }

        $employee->notify(new EmployeePaymentRecordedNotification(
            paidAmount: $amountPaid,
            remainingAmount: (int) ($paymentData['remaining'] ?? 0),
            fullSalary: (int) ($paymentData['expected_salary'] ?? 0),
            status: (string) ($paymentData['status'] ?? 'pending'),
            url: $this->employeePaymentUrl($employee),
        ));
    }

    public function notifyTeacherAssignedToGroup(CourseClass $group, ?int $previousTeacherId, User $issuer): void
    {
        $newTeacherId = $group->teacher_id === null ? null : (int) $group->teacher_id;

        if ($previousTeacherId === $newTeacherId) {
            return;
        }

        $group->loadMissing(['course.program:id,name,code', 'teacher.roles:id,name']);
        $actorRole = $this->primaryRole($issuer);

        if ($previousTeacherId !== null) {
            $previousTeacher = User::query()
                ->role('teacher')
                ->whereKey($previousTeacherId)
                ->first();

            if ($previousTeacher instanceof User) {
                $previousTeacher->notify(new TeacherGroupAssignedNotification(
                    groupId: (int) $group->id,
                    groupName: $this->groupName($group),
                    courseName: $this->courseName($group->course),
                    programName: $group->course?->program?->name,
                    issuerId: (int) $issuer->id,
                    issuerName: (string) $issuer->name,
                    url: Route::has('timetable.teacher') ? route('timetable.teacher') : null,
                    action: 'removed',
                    actorRole: $actorRole,
                ));
            }
        }

        if ($newTeacherId === null) {
            return;
        }

        $teacher = $group->teacher;

        if (! $teacher instanceof User || ! $teacher->hasRole('teacher')) {
            return;
        }

        $teacher->notify(new TeacherGroupAssignedNotification(
            groupId: (int) $group->id,
            groupName: $this->groupName($group),
            courseName: $this->courseName($group->course),
            programName: $group->course?->program?->name,
            issuerId: (int) $issuer->id,
            issuerName: (string) $issuer->name,
            url: Route::has('timetable.teacher') ? route('timetable.teacher') : null,
            actorRole: $actorRole,
        ));
    }

    public function notifyStudentGroupEnrollmentChanged(CourseClass $group, User $student, User $actor, string $action): void
    {
        if (! in_array($action, ['enrolled', 'removed'], true)) {
            return;
        }

        $group->loadMissing(['course.program:id,name,code']);
        $student->loadMissing(['parent.roles:id,name']);

        $courseName = $this->courseName($group->course);
        $groupName = $this->groupName($group);
        $programName = $group->course?->program?->name;
        $actorRole = $this->primaryRole($actor);

        $student->notify(new StudentGroupEnrollmentChangedNotification(
            action: $action,
            groupId: (int) $group->id,
            groupName: $groupName,
            courseName: $courseName,
            programName: $programName,
            actorId: (int) $actor->id,
            actorName: (string) $actor->name,
            actorRole: $actorRole,
            recipientType: 'student',
            url: Route::has('student.academic') ? route('student.academic') : null,
        ));

        $parent = $student->parent;

        if (! $parent instanceof User || ! $parent->hasRole('parent')) {
            return;
        }

        $parent->notify(new StudentGroupEnrollmentChangedNotification(
            action: $action,
            groupId: (int) $group->id,
            groupName: $groupName,
            courseName: $courseName,
            programName: $programName,
            actorId: (int) $actor->id,
            actorName: (string) $actor->name,
            actorRole: $actorRole,
            recipientType: 'parent',
            childId: (int) $student->id,
            childName: (string) $student->name,
            url: Route::has('parent.child.academic') ? route('parent.child.academic', ['child' => $student->id]) : null,
        ));
    }

    public function notifyTuitionPaymentRecorded(TuitionPayment $payment, User $actor): void
    {
        $payment->loadMissing(['student.parent.roles:id,name']);

        $student = $payment->student;

        if (! $student instanceof User || ! $student->hasRole('student')) {
            return;
        }

        $actorRole = $this->primaryRole($actor);
        $paidOn = $payment->paid_on?->format('Y-m-d') ?? now()->toDateString();

        $student->notify(new TuitionPaymentRecordedNotification(
            paymentId: (int) $payment->id,
            amount: (int) $payment->amount,
            paidOn: $paidOn,
            method: (string) $payment->method,
            actorId: (int) $actor->id,
            actorName: (string) $actor->name,
            actorRole: $actorRole,
            recipientType: 'student',
            url: Route::has('student.financial') ? route('student.financial') : null,
        ));

        $parent = $student->parent;

        if (! $parent instanceof User || ! $parent->hasRole('parent')) {
            return;
        }

        $parent->notify(new TuitionPaymentRecordedNotification(
            paymentId: (int) $payment->id,
            amount: (int) $payment->amount,
            paidOn: $paidOn,
            method: (string) $payment->method,
            actorId: (int) $actor->id,
            actorName: (string) $actor->name,
            actorRole: $actorRole,
            recipientType: 'parent',
            childId: (int) $student->id,
            childName: (string) $student->name,
            url: Route::has('parent.financial') ? route('parent.financial') : null,
        ));
    }

    public function notifyScheduleChanged(Schedule $schedule, User $actor, string $action): void
    {
        if (! in_array($action, ['created', 'updated', 'cancelled'], true)) {
            return;
        }

        $schedule->loadMissing([
            'class.course:id,name,code',
            'class.teacher.roles:id,name',
            'class.students.parent.roles:id,name',
            'room:id,name',
        ]);

        $group = $schedule->class;

        if (! $group instanceof CourseClass) {
            return;
        }

        $actorRole = $this->primaryRole($actor);
        $courseName = $this->courseName($group->course);
        $groupName = $this->groupName($group);
        $dayOfWeek = ucfirst((string) $schedule->day_of_week);
        $startTime = $schedule->start_time?->format('H:i') ?? '';
        $endTime = $schedule->end_time?->format('H:i') ?? '';
        $roomName = $schedule->room?->name;

        $teacher = $group->teacher;

        if ($teacher instanceof User && $teacher->hasRole('teacher')) {
            $teacher->notify($this->scheduleNotification(
                schedule: $schedule,
                group: $group,
                action: $action,
                actor: $actor,
                actorRole: $actorRole,
                courseName: $courseName,
                groupName: $groupName,
                dayOfWeek: $dayOfWeek,
                startTime: $startTime,
                endTime: $endTime,
                roomName: $roomName,
                recipientType: 'teacher',
                url: Route::has('timetable.teacher') ? route('timetable.teacher') : null,
            ));
        }

        foreach ($group->students as $student) {
            if (! $student instanceof User || ! $student->hasRole('student')) {
                continue;
            }

            $student->notify($this->scheduleNotification(
                schedule: $schedule,
                group: $group,
                action: $action,
                actor: $actor,
                actorRole: $actorRole,
                courseName: $courseName,
                groupName: $groupName,
                dayOfWeek: $dayOfWeek,
                startTime: $startTime,
                endTime: $endTime,
                roomName: $roomName,
                recipientType: 'student',
                url: Route::has('student.academic') ? route('student.academic') : null,
            ));

            $parent = $student->parent;

            if (! $parent instanceof User || ! $parent->hasRole('parent')) {
                continue;
            }

            $parent->notify($this->scheduleNotification(
                schedule: $schedule,
                group: $group,
                action: $action,
                actor: $actor,
                actorRole: $actorRole,
                courseName: $courseName,
                groupName: $groupName,
                dayOfWeek: $dayOfWeek,
                startTime: $startTime,
                endTime: $endTime,
                roomName: $roomName,
                recipientType: 'parent',
                childId: (int) $student->id,
                childName: (string) $student->name,
                url: Route::has('parent.child.academic') ? route('parent.child.academic', ['child' => $student->id]) : null,
            ));
        }
    }

    public function notifyClassResourceUploaded(TeacherResource $resource): void
    {
        $resource->loadMissing([
            'courseClass.course.program:id,name,code',
            'courseClass.students.parent',
        ]);

        $courseClass = $resource->courseClass;

        if (! $courseClass instanceof CourseClass) {
            return;
        }

        $courseName = $this->courseName($courseClass->course);
        $groupName = $this->groupName($courseClass);
        $deadline = $resource->deadline?->format('Y-m-d');

        foreach ($courseClass->students as $student) {
            if (! $this->alreadySentResourceNotification($student, (int) $resource->id, 'student', null)) {
                $student->notify(new ClassResourceUploadedNotification(
                    resourceId: (int) $resource->id,
                    classId: (int) $courseClass->id,
                    recipientType: 'student',
                    courseName: $courseName,
                    groupName: $groupName,
                    resourceCategory: (string) $resource->category,
                    deadline: $deadline,
                    url: Route::has('student.materials') ? route('student.materials') : null,
                ));
            }

            $parent = $student->parent;

            if (! $parent instanceof User) {
                continue;
            }

            if ($this->alreadySentResourceNotification($parent, (int) $resource->id, 'parent', (int) $student->id)) {
                continue;
            }

            $parent->notify(new ClassResourceUploadedNotification(
                resourceId: (int) $resource->id,
                classId: (int) $courseClass->id,
                recipientType: 'parent',
                courseName: $courseName,
                groupName: $groupName,
                resourceCategory: (string) $resource->category,
                deadline: $deadline,
                childId: (int) $student->id,
                childName: (string) $student->name,
                url: Route::has('parent.child.materials') ? route('parent.child.materials', ['child' => $student->id]) : null,
            ));
        }
    }

    private function employeePaymentUrl(User $employee): ?string
    {
        $employee->loadMissing('roles:id,name');

        if ($employee->hasRole('teacher') && Route::has('teacher.my-payments')) {
            return route('teacher.my-payments');
        }

        if ($employee->hasRole('secretary') && Route::has('secretary.my-payments')) {
            return route('secretary.my-payments');
        }

        return null;
    }

    private function groupName(CourseClass $group): string
    {
        return 'Group #'.$group->id;
    }

    private function courseName(?Course $course): string
    {
        if (! $course instanceof Course) {
            return 'the selected course';
        }

        $name = trim((string) $course->name);
        $code = trim((string) ($course->code ?? ''));

        if ($code === '' || str_contains(strtolower($name), strtolower($code))) {
            return $name !== '' ? $name : 'the selected course';
        }

        return trim($name.' '.$code);
    }

    private function primaryRole(User $user): ?string
    {
        $user->loadMissing('roles:id,name');

        return $user->roles
            ->pluck('name')
            ->first();
    }

    private function scheduleNotification(
        Schedule $schedule,
        CourseClass $group,
        string $action,
        User $actor,
        ?string $actorRole,
        string $courseName,
        string $groupName,
        string $dayOfWeek,
        string $startTime,
        string $endTime,
        ?string $roomName,
        string $recipientType,
        ?int $childId = null,
        ?string $childName = null,
        ?string $url = null,
    ): ScheduleChangedNotification {
        return new ScheduleChangedNotification(
            action: $action,
            scheduleId: (int) $schedule->id,
            groupId: (int) $group->id,
            groupName: $groupName,
            courseName: $courseName,
            dayOfWeek: $dayOfWeek,
            startTime: $startTime,
            endTime: $endTime,
            roomName: $roomName,
            actorId: (int) $actor->id,
            actorName: (string) $actor->name,
            actorRole: $actorRole,
            recipientType: $recipientType,
            childId: $childId,
            childName: $childName,
            url: $url,
        );
    }

    private function alreadySentResourceNotification(User $recipient, int $resourceId, string $recipientType, ?int $childId): bool
    {
        return $recipient->notifications()
            ->where('type', ClassResourceUploadedNotification::class)
            ->get(['data'])
            ->contains(function (DatabaseNotification $notification) use ($resourceId, $recipientType, $childId): bool {
                $data = (array) $notification->data;

                return (int) ($data['resource_id'] ?? 0) === $resourceId
                    && (string) ($data['recipient_type'] ?? '') === $recipientType
                    && ($childId === null || (int) ($data['child_id'] ?? 0) === $childId);
            });
    }
}
