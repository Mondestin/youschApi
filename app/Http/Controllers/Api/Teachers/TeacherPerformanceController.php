<?php

namespace App\Http\Controllers\Api\Teachers;

use App\Http\Controllers\Controller;
use App\Models\Teachers\TeacherPerformance;
use App\Repositories\Teachers\TeacherPerformanceRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class TeacherPerformanceController extends Controller
{
    protected $performanceRepository;

    public function __construct(TeacherPerformanceRepositoryInterface $performanceRepository)
    {
        $this->performanceRepository = $performanceRepository;
    }

    /**
     * Display a paginated list of teacher performances
     *
     * @param Request $request
     * @return JsonResponse
     * @group Teachers
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['teacher_id', 'evaluation_period', 'evaluator_id', 'rating_min', 'rating_max', 'per_page']);
            $performances = $this->performanceRepository->getPaginatedPerformances($filters);
            
            return response()->json([
                'success' => true,
                'data' => $performances,
                'message' => 'Teacher performances retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve teacher performances: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created performance evaluation
     *
     * @param Request $request
     * @return JsonResponse
     * @group Teachers
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'teacher_id' => 'required|exists:teachers,id',
                'evaluation_period' => 'required|string|max:100',
                'evaluation_date' => 'required|date',
                'evaluator_id' => 'required|exists:users,id',
                'teaching_effectiveness' => 'required|integer|min:1|max:5',
                'classroom_management' => 'required|integer|min:1|max:5',
                'subject_knowledge' => 'required|integer|min:1|max:5',
                'communication_skills' => 'required|integer|min:1|max:5',
                'professional_development' => 'required|integer|min:1|max:5',
                'student_engagement' => 'required|integer|min:1|max:5',
                'assessment_quality' => 'required|integer|min:1|max:5',
                'overall_rating' => 'required|numeric|min:1|max:5',
                'strengths' => 'nullable|string|max:1000',
                'areas_for_improvement' => 'nullable|string|max:1000',
                'recommendations' => 'nullable|string|max:1000',
                'comments' => 'nullable|string|max:2000',
                'status' => 'in:draft,published,archived'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check if evaluation already exists for the same teacher and period
            $existingEvaluation = $this->performanceRepository->getPerformanceByTeacherAndPeriod(
                $request->teacher_id,
                $request->evaluation_period
            );

            if ($existingEvaluation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Performance evaluation already exists for this teacher and period'
                ], 422);
            }

            $performance = $this->performanceRepository->createPerformance($request->all());
            
            return response()->json([
                'success' => true,
                'data' => $performance,
                'message' => 'Performance evaluation created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create performance evaluation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified performance evaluation
     *
     * @param int $id
     * @return JsonResponse
     * @group Teachers
     */
    public function show(int $id): JsonResponse
    {
        try {
            $performance = $this->performanceRepository->getPerformanceById($id);
            
            if (!$performance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Performance evaluation not found'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'data' => $performance,
                'message' => 'Performance evaluation retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve performance evaluation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified performance evaluation
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     * @group Teachers
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $performance = $this->performanceRepository->getPerformanceById($id);
            
            if (!$performance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Performance evaluation not found'
                ], 404);
            }

            // Only allow updates if performance is in draft status
            if ($performance->status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot update performance evaluation that is not in draft status'
                ], 422);
            }

            $validator = Validator::make($request->all(), [
                'teaching_effectiveness' => 'sometimes|required|integer|min:1|max:5',
                'classroom_management' => 'sometimes|required|integer|min:1|max:5',
                'subject_knowledge' => 'sometimes|required|integer|min:1|max:5',
                'communication_skills' => 'sometimes|required|integer|min:1|max:5',
                'professional_development' => 'sometimes|required|integer|min:1|max:5',
                'student_engagement' => 'sometimes|required|integer|min:1|max:5',
                'assessment_quality' => 'sometimes|required|integer|min:1|max:5',
                'overall_rating' => 'sometimes|required|numeric|min:1|max:5',
                'strengths' => 'nullable|string|max:1000',
                'areas_for_improvement' => 'nullable|string|max:1000',
                'recommendations' => 'nullable|string|max:1000',
                'comments' => 'nullable|string|max:2000',
                'status' => 'in:draft,published,archived'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $updated = $this->performanceRepository->updatePerformance($performance, $request->all());
            
            if ($updated) {
                $performance->refresh();
                return response()->json([
                    'success' => true,
                    'data' => $performance,
                    'message' => 'Performance evaluation updated successfully'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update performance evaluation'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update performance evaluation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified performance evaluation
     *
     * @param int $id
     * @return JsonResponse
     * @group Teachers
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $performance = $this->performanceRepository->getPerformanceById($id);
            
            if (!$performance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Performance evaluation not found'
                ], 404);
            }

            // Only allow deletion if performance is in draft status
            if ($performance->status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete performance evaluation that is not in draft status'
                ], 422);
            }

            $deleted = $this->performanceRepository->deletePerformance($performance);
            
            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Performance evaluation deleted successfully'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete performance evaluation'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete performance evaluation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Publish a performance evaluation
     *
     * @param int $id
     * @return JsonResponse
     * @group Teachers
     */
    public function publish(int $id): JsonResponse
    {
        try {
            $performance = $this->performanceRepository->getPerformanceById($id);
            
            if (!$performance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Performance evaluation not found'
                ], 404);
            }

            if ($performance->status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'message' => 'Performance evaluation is not in draft status'
                ], 422);
            }

            $updated = $this->performanceRepository->updatePerformance($performance, ['status' => 'published']);
            
            if ($updated) {
                $performance->refresh();
                return response()->json([
                    'success' => true,
                    'data' => $performance,
                    'message' => 'Performance evaluation published successfully'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to publish performance evaluation'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to publish performance evaluation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Archive a performance evaluation
     *
     * @param int $id
     * @return JsonResponse
     * @group Teachers
     */
    public function archive(int $id): JsonResponse
    {
        try {
            $performance = $this->performanceRepository->getPerformanceById($id);
            
            if (!$performance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Performance evaluation not found'
                ], 404);
            }

            if ($performance->status === 'archived') {
                return response()->json([
                    'success' => false,
                    'message' => 'Performance evaluation is already archived'
                ], 422);
            }

            $updated = $this->performanceRepository->updatePerformance($performance, ['status' => 'archived']);
            
            if ($updated) {
                $performance->refresh();
                return response()->json([
                    'success' => true,
                    'data' => $performance,
                    'message' => 'Performance evaluation archived successfully'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to archive performance evaluation'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to archive performance evaluation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get performances by teacher
     *
     * @param int $teacherId
     * @return JsonResponse
     * @group Teachers
     */
    public function getByTeacher(int $teacherId): JsonResponse
    {
        try {
            $performances = $this->performanceRepository->getPerformancesByTeacher($teacherId);
            
            return response()->json([
                'success' => true,
                'data' => $performances,
                'message' => 'Teacher performances retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve teacher performances: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get performances by evaluator
     *
     * @param int $evaluatorId
     * @return JsonResponse
     * @group Teachers
     */
    public function getByEvaluator(int $evaluatorId): JsonResponse
    {
        try {
            $performances = $this->performanceRepository->getPerformancesByEvaluator($evaluatorId);
            
            return response()->json([
                'success' => true,
                'data' => $performances,
                'message' => 'Evaluator performances retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve evaluator performances: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get performances by period
     *
     * @param string $period
     * @return JsonResponse
     * @group Teachers
     */
    public function getByPeriod(string $period): JsonResponse
    {
        try {
            $performances = $this->performanceRepository->getPerformancesByPeriod($period);
            
            return response()->json([
                'success' => true,
                'data' => $performances,
                'message' => 'Period performances retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve period performances: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get performances by rating range
     *
     * @param Request $request
     * @return JsonResponse
     * @group Teachers
     */
    public function getByRatingRange(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'min_rating' => 'required|numeric|min:1|max:5',
                'max_rating' => 'required|numeric|min:1|max:5|gte:min_rating'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $performances = $this->performanceRepository->getPerformancesByRatingRange(
                $request->min_rating,
                $request->max_rating
            );
            
            return response()->json([
                'success' => true,
                'data' => $performances,
                'message' => 'Rating range performances retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve rating range performances: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get performance statistics
     *
     * @return JsonResponse
     * @group Teachers
    */
    public function getStatistics(): JsonResponse
    {
        try {
            $statistics = $this->performanceRepository->getPerformanceStatistics();
            
            return response()->json([
                'success' => true,
                'data' => $statistics,
                'message' => 'Performance statistics retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve performance statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get teacher performance trends
     *
     * @param int $teacherId
     * @return JsonResponse
     * @group Teachers
    */
    public function getPerformanceTrends(int $teacherId): JsonResponse
    {
        try {
            $trends = $this->performanceRepository->getTeacherPerformanceTrends($teacherId);
            
            return response()->json([
                'success' => true,
                'data' => $trends,
                'message' => 'Performance trends retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve performance trends: ' . $e->getMessage()
            ], 500);
        }
    }
} 