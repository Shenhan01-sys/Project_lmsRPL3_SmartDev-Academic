<?php

namespace App\Providers;

use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\GradeComponent;
use App\Models\Notification;
use App\Models\Submission;
use App\Models\User;
use App\Policies\AnnouncementPolicy;
use App\Policies\AssignmentPolicy;
use App\Policies\AttendanceRecordPolicy;
use App\Policies\AttendanceSessionPolicy;
use App\Policies\CertificatePolicy;
use App\Policies\CoursePolicy;
use App\Policies\EnrollmentPolicy;
use App\Policies\GradeComponentPolicy;
use App\Policies\GradePolicy;
use App\Policies\NotificationPolicy;
use App\Policies\SubmissionPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        Course::class => CoursePolicy::class,
        Enrollment::class => EnrollmentPolicy::class,
        Assignment::class => AssignmentPolicy::class,
        Submission::class => SubmissionPolicy::class,
        Grade::class => GradePolicy::class,
        GradeComponent::class => GradeComponentPolicy::class,
        Announcement::class => AnnouncementPolicy::class,
        Notification::class => NotificationPolicy::class,
        AttendanceSession::class => AttendanceSessionPolicy::class,
        AttendanceRecord::class => AttendanceRecordPolicy::class,
        Certificate::class => CertificatePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        //
    }
}
