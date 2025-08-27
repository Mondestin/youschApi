<?php

namespace App\Http\Controllers\Api\Teachers;

use App\Http\Controllers\Controller;
use App\Models\Teachers\Teacher;
use App\Repositories\Teachers\TeacherRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TeacherController extends Controller
{
    protected $teacherRepository;

    public function __construct(TeacherRepositoryInterface $teacherRepository)
    {
        $this->teacherRepository = $teacherRepository;
    }

    /**
     * Display a paginated list of teachers
     *
     * @param Request $request
     * @return JsonResponse
     * @group Teachers
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['search', 'department_id', 'faculty_id', 'status', 'per_page']);
            $teachers = $this->teacherRepository->getAllTeachers($filters);
            
            return response()->json([
                'success' => true,
                'data' => $teachers,
                'message' => 'Teachers retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve teachers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created teacher
     *
     * @param Request $request
     * @return JsonResponse
     * @group Teachers
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email|unique:teachers,email',
                'phone' => 'nullable|string|max:20',
                'date_of_birth' => 'required|date',
                'gender' => 'required|in:male,female,other',
                'address' => 'nullable|string',
                'department_id' => 'required|exists:departments,id',
                'faculty_id' => 'required|exists:faculties,id',
                'hire_date' => 'required|date',
                'employment_type' => 'required|in:full-time,part-time,contract,temporary',
                'qualification' => 'required|string',
                'specialization' => 'nullable|string',
                'status' => 'required|in:active,inactive,on_leave,terminated'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $teacher = $this->teacherRepository->createTeacher($request->all());
            
            return response()->json([
                'success' => true,
                'data' => $teacher,
                'message' => 'Teacher created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create teacher: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified teacher
     *
     * @param int $id
     * @return JsonResponse
     * @group Teachers
     */
    public function show(int $id): JsonResponse
    {
        try {
            $teacher = $this->teacherRepository->getTeacherById($id);
            
            if (!$teacher) {
                return response()->json([
                    'success' => false,
                    'message' => 'Teacher not found'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'data' => $teacher,
                'message' => 'Teacher retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve teacher: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified teacher
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     * @group Teachers
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $teacher = $this->teacherRepository->getTeacherById($id);
            
            if (!$teacher) {
                return response()->json([
                    'success' => false,
                    'message' => 'Teacher not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'first_name' => 'sometimes|required|string|max:255',
                'last_name' => 'sometimes|required|string|max:255',
                'email' => [
                    'sometimes',
                    'required',
                    'email',
                    Rule::unique('teachers', 'email')->ignore($id)
                ],
                'phone' => 'nullable|string|max:20',
                'date_of_birth' => 'sometimes|required|date',
                'gender' => 'sometimes|required|in:male,female,other',
                'address' => 'nullable|string',
                'department_id' => 'sometimes|required|exists:departments,id',
                'faculty_id' => 'sometimes|required|exists:faculties,id',
                'hire_date' => 'sometimes|required|date',
                'employment_type' => 'sometimes|required|in:full-time,part-time,contract,temporary',
                'qualification' => 'sometimes|required|string',
                'specialization' => 'nullable|string',
                'status' => 'sometimes|required|in:active,inactive,on_leave,terminated'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $updated = $this->teacherRepository->updateTeacher($teacher, $request->all());
            
            if ($updated) {
                $teacher->refresh();
                return response()->json([
                    'success' => true,
                    'data' => $teacher,
                    'message' => 'Teacher updated successfully'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update teacher'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update teacher: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified teacher
     *
     * @param int $id
     * @return JsonResponse
     * @group Teachers
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $teacher = $this->teacherRepository->getTeacherById($id);
            
            if (!$teacher) {
                return response()->json([
                    'success' => false,
                    'message' => 'Teacher not found'
                ], 404);
            }

            $deleted = $this->teacherRepository->deleteTeacher($teacher);
            
            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Teacher deleted successfully'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete teacher'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete teacher: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get teachers by department
     *
     * @param int $departmentId
     * @return JsonResponse
     * @group Teachers
     */
    public function getByDepartment(int $departmentId): JsonResponse
    {
        try {
            $teachers = $this->teacherRepository->getTeachersByDepartment($departmentId);
            
            return response()->json([
                'success' => true,
                'data' => $teachers,
                'message' => 'Teachers retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve teachers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get teachers by faculty
     *
     * @param int $facultyId
     * @return JsonResponse
     * @group Teachers
     */
    public function getByFaculty(int $facultyId): JsonResponse
    {
        try {
            $teachers = $this->teacherRepository->getTeachersByFaculty($facultyId);
            
            return response()->json([
                'success' => true,
                'data' => $teachers,
                'message' => 'Teachers retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve teachers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get teacher statistics
     *
     * @return JsonResponse
     * @group Teachers
    */
    public function getStatistics(): JsonResponse
    {
        try {
            $statistics = $this->teacherRepository->getTeacherStatistics();
            
            return response()->json([
                'success' => true,
                'data' => $statistics,
                'message' => 'Teacher statistics retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve teacher statistics: ' . $e->getMessage()
            ], 500);
        }
    }
} 