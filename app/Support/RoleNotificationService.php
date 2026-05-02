<?php

namespace App\Support;

use App\Models\Course;
use App\Models\CourseClass;
use App\Models\TeacherResource;
use App\Models\User;
use App\Notifications\ClassResourceUploadedNotification;
use App\Notifications\EmployeePaymentRecordedNotification;
use App\Notifications\TeacherGroupAssignedNotification;
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

        if ($newTeacherId === null || $previousTeacherId === $newTeacherId) {
            return;
        }

        $group->loadMissing(['course.program:id,name,code', 'teacher.roles:id,name']);

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
        ));
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
