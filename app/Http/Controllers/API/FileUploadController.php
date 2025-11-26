<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class FileUploadController extends Controller
{
    protected $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/upload/profile-photo",
     *     tags={"File Upload"},
     *     summary="Upload profile photo",
     *     description="Upload a profile photo for the authenticated user",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="photo",
     *                     type="string",
     *                     format="binary",
     *                     description="Profile photo file (max 2MB)"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Photo uploaded successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Upload failed or validation error"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function uploadProfilePhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|file|image|max:2048', // max 2MB
        ]);

        try {
            $user = $request->user();
            
            // Hapus foto profil lama jika ada
            if ($user->profile_photo_path) {
                $this->fileUploadService->deleteFile($user->profile_photo_path);
            }

            $result = $this->fileUploadService->uploadProfilePhoto(
                $request->file('photo'),
                $user->id
            );

            // Update user dengan path foto baru
            $user->update([
                'profile_photo_path' => $result['path']
            ]);

            return response()->json([
                'message' => 'Foto profil berhasil diupload.',
                'data' => [
                    'photo_url' => $result['url'],
                    'photo_path' => $result['path']
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal upload foto profil.',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/upload/material/{materialId}",
     *     tags={"File Upload"},
     *     summary="Upload material file",
     *     description="Upload a file for a learning material",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="materialId",
     *         in="path",
     *         required=true,
     *         description="Material ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="file",
     *                     type="string",
     *                     format="binary",
     *                     description="Material file (max 50MB)"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="File uploaded successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Upload failed or validation error"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function uploadMaterialFile(Request $request, $materialId)
    {
        $request->validate([
            'file' => 'required|file|max:51200', // max 50MB
        ]);

        try {
            $result = $this->fileUploadService->uploadMaterialFile(
                $request->file('file'),
                $materialId
            );

            return response()->json([
                'message' => 'File materi berhasil diupload.',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal upload file materi.',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/upload/assignment/{assignmentId}",
     *     tags={"File Upload"},
     *     summary="Upload assignment file",
     *     description="Upload a file for an assignment (e.g., instructions, resources)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="assignmentId",
     *         in="path",
     *         required=true,
     *         description="Assignment ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="file",
     *                     type="string",
     *                     format="binary",
     *                     description="Assignment file (max 10MB)"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="File uploaded successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Upload failed or validation error"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function uploadAssignmentFile(Request $request, $assignmentId)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // max 10MB
        ]);

        try {
            $result = $this->fileUploadService->uploadAssignmentFile(
                $request->file('file'),
                $assignmentId
            );

            return response()->json([
                'message' => 'File tugas berhasil diupload.',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal upload file tugas.',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/upload/submission/{submissionId}",
     *     tags={"File Upload"},
     *     summary="Upload submission file",
     *     description="Upload a file for an assignment submission",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="submissionId",
     *         in="path",
     *         required=true,
     *         description="Submission ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="file",
     *                     type="string",
     *                     format="binary",
     *                     description="Submission file (max 20MB)"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="File uploaded successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Upload failed or validation error"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function uploadSubmissionFile(Request $request, $submissionId)
    {
        $request->validate([
            'file' => 'required|file|max:20480', // max 20MB
        ]);

        try {
            $result = $this->fileUploadService->uploadSubmissionFile(
                $request->file('file'),
                $submissionId
            );

            return response()->json([
                'message' => 'File jawaban berhasil diupload.',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal upload file jawaban.',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/upload/file",
     *     tags={"File Upload"},
     *     summary="Delete file",
     *     description="Delete a file from storage",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"file_path"},
     *             @OA\Property(property="file_path", type="string", description="Path of the file to delete")   
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="File deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Failed to delete file"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function deleteFile(Request $request)
    {
        $request->validate([
            'file_path' => 'required|string',
        ]);

        try {
            $result = $this->fileUploadService->deleteFile($request->file_path);

            if ($result) {
                return response()->json([
                    'message' => 'File berhasil dihapus.'
                ]);
            } else {
                return response()->json([
                    'message' => 'Gagal menghapus file.'
                ], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error saat menghapus file.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/upload/file-info",
     *     tags={"File Upload"},
     *     summary="Get file info",
     *     description="Get information about a file",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="file_path",
     *         in="query",
     *         required=true,
     *         description="Path of the file",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="File info retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="File not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function getFileInfo(Request $request)
    {
        $request->validate([
            'file_path' => 'required|string',
        ]);

        try {
            $fileInfo = $this->fileUploadService->getFileInfo($request->file_path);

            if ($fileInfo) {
                return response()->json([
                    'message' => 'Info file berhasil diambil.',
                    'data' => $fileInfo
                ]);
            } else {
                return response()->json([
                    'message' => 'File tidak ditemukan.'
                ], 404);
            }

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error saat mengambil info file.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}