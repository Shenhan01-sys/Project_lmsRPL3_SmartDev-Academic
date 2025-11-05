<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\Assignment;
use App\Models\GradeComponent;
use App\Models\Material;
use App\Models\CourseModule;
use App\Models\Course;

class EnrollmentService
{
    /**
     * Check if student is enrolled in a specific course
     *
     * @param int $studentId
     * @param int $courseId
     * @return bool
     */
    public function isStudentEnrolledInCourse(int $studentId, int $courseId): bool
    {
        return Enrollment::where('student_id', $studentId)
                        ->where('course_id', $courseId)
                        ->exists();
    }

    /**
     * Check if student is enrolled in the course of a specific assignment
     *
     * @param int $studentId
     * @param int $assignmentId
     * @return bool
     */
    public function isStudentEnrolledInAssignmentCourse(int $studentId, int $assignmentId): bool
    {
        $assignment = Assignment::find($assignmentId);

        if (!$assignment) {
            return false;
        }

        return $this->isStudentEnrolledInCourse($studentId, $assignment->course_id);
    }

    /**
     * Check if student is enrolled in the course of a specific grade component
     *
     * @param int $studentId
     * @param int $gradeComponentId
     * @return bool
     */
    public function isStudentEnrolledInGradeComponentCourse(int $studentId, int $gradeComponentId): bool
    {
        $gradeComponent = GradeComponent::find($gradeComponentId);

        if (!$gradeComponent) {
            return false;
        }

        return $this->isStudentEnrolledInCourse($studentId, $gradeComponent->course_id);
    }

    /**
     * Check if student is enrolled in the course of a specific material
     *
     * @param int $studentId
     * @param int $materialId
     * @return bool
     */
    public function isStudentEnrolledInMaterialCourse(int $studentId, int $materialId): bool
    {
        $material = Material::with('courseModule')->find($materialId);

        if (!$material || !$material->courseModule) {
            return false;
        }

        return $this->isStudentEnrolledInCourse($studentId, $material->courseModule->course_id);
    }

    /**
     * Check if student is enrolled in the course of a specific course module
     *
     * @param int $studentId
     * @param int $moduleId
     * @return bool
     */
    public function isStudentEnrolledInModuleCourse(int $studentId, int $moduleId): bool
    {
        $module = CourseModule::find($moduleId);

        if (!$module) {
            return false;
        }

        return $this->isStudentEnrolledInCourse($studentId, $module->course_id);
    }

    /**
     * Get all enrolled course IDs for a student
     *
     * @param int $studentId
     * @return array
     */
    public function getEnrolledCourseIds(int $studentId): array
    {
        return Enrollment::where('student_id', $studentId)
                        ->pluck('course_id')
                        ->toArray();
    }

    /**
     * Get course ID from assignment
     *
     * @param int $assignmentId
     * @return int|null
     */
    public function getCourseIdFromAssignment(int $assignmentId): ?int
    {
        $assignment = Assignment::find($assignmentId);
        return $assignment ? $assignment->course_id : null;
    }

    /**
     * Get course ID from grade component
     *
     * @param int $gradeComponentId
     * @return int|null
     */
    public function getCourseIdFromGradeComponent(int $gradeComponentId): ?int
    {
        $gradeComponent = GradeComponent::find($gradeComponentId);
        return $gradeComponent ? $gradeComponent->course_id : null;
    }

    /**
     * Get course ID from material
     *
     * @param int $materialId
     * @return int|null
     */
    public function getCourseIdFromMaterial(int $materialId): ?int
    {
        $material = Material::with('courseModule')->find($materialId);
        return $material && $material->courseModule ? $material->courseModule->course_id : null;
    }

    /**
     * Get course ID from course module
     *
     * @param int $moduleId
     * @return int|null
     */
    public function getCourseIdFromModule(int $moduleId): ?int
    {
        $module = CourseModule::find($moduleId);
        return $module ? $module->course_id : null;
    }

    /**
     * Validate multiple students enrollment in bulk
     * Used for bulk operations like bulk grading
     *
     * @param array $studentIds
     * @param int $courseId
     * @return array Returns array of not enrolled student IDs
     */
    public function validateBulkEnrollment(array $studentIds, int $courseId): array
    {
        $enrolledStudentIds = Enrollment::where('course_id', $courseId)
                                       ->whereIn('student_id', $studentIds)
                                       ->pluck('student_id')
                                       ->toArray();

        // Return student IDs that are NOT enrolled
        return array_diff($studentIds, $enrolledStudentIds);
    }

    /**
     * Get all students enrolled in a course
     *
     * @param int $courseId
     * @return \Illuminate\Support\Collection
     */
    public function getEnrolledStudents(int $courseId)
    {
        return Enrollment::where('course_id', $courseId)
                        ->with('student.user')
                        ->get()
                        ->pluck('student');
    }

    /**
     * Check if any students from array are not enrolled in course
     *
     * @param array $studentIds
     * @param int $courseId
     * @return bool
     */
    public function hasUnenrolledStudents(array $studentIds, int $courseId): bool
    {
        $notEnrolledIds = $this->validateBulkEnrollment($studentIds, $courseId);
        return count($notEnrolledIds) > 0;
    }
}
