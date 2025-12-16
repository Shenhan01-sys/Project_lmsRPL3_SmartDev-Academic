<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="SmartDev LMS API Documentation",
 *     version="1.0.0",
 *     description="Comprehensive API documentation for SmartDev Academic Learning Management System. This API provides endpoints for managing courses, students, instructors, assignments, submissions, grades, attendance, and more.",
 *     @OA\Contact(
 *         email="admin@smartdevlms.com",
 *         name="SmartDev LMS Support"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 *
 * @OA\Server(
 *     url="https://portohansgunawan.my.id",
 *     description="Production Server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="Sanctum Token",
 *     description="Enter your bearer token in the format: Bearer {token}"
 * )
 *
 * @OA\Tag(
 *     name="Authentication",
 *     description="Authentication endpoints - login, logout, password reset"
 * )
 *
 * @OA\Tag(
 *     name="Registration",
 *     description="Student registration endpoints for calon siswa"
 * )
 *
 * @OA\Tag(
 *     name="Courses",
 *     description="Course management endpoints"
 * )
 *
 * @OA\Tag(
 *     name="Course Modules",
 *     description="Course module management"
 * )
 *
 * @OA\Tag(
 *     name="Materials",
 *     description="Learning materials management"
 * )
 *
 * @OA\Tag(
 *     name="Assignments",
 *     description="Assignment management endpoints"
 * )
 *
 * @OA\Tag(
 *     name="Submissions",
 *     description="Assignment submission endpoints"
 * )
 *
 * @OA\Tag(
 *     name="Enrollments",
 *     description="Course enrollment management"
 * )
 *
 * @OA\Tag(
 *     name="Students",
 *     description="Student management endpoints"
 * )
 *
 * @OA\Tag(
 *     name="Instructors",
 *     description="Instructor management endpoints"
 * )
 *
 * @OA\Tag(
 *     name="Parents",
 *     description="Parent management endpoints"
 * )
 *
 * @OA\Tag(
 *     name="Grades",
 *     description="Grade management endpoints"
 * )
 *
 * @OA\Tag(
 *     name="Grade Components",
 *     description="Grade component management"
 * )
 *
 * @OA\Tag(
 *     name="Attendance",
 *     description="Attendance session and record management"
 * )
 *
 * @OA\Tag(
 *     name="Announcements",
 *     description="Announcement management endpoints"
 * )
 *
 * @OA\Tag(
 *     name="Notifications",
 *     description="Notification management endpoints"
 * )
 *
 * @OA\Tag(
 *     name="Certificates",
 *     description="Certificate generation and management"
 * )
 *
 * @OA\Tag(
 *     name="Users",
 *     description="User management endpoints"
 * )
 */
abstract class Controller
{
    //
}
