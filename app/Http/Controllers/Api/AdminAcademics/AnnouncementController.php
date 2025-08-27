<?php

namespace App\Http\Controllers\Api\AdminAcademics;

use App\Http\Controllers\Controller;
use App\Models\AdminAcademics\Announcement;
use App\Models\AdminAcademics\School;
use App\Models\AdminAcademics\Campus;
use App\Models\AdminAcademics\ClassRoom;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of announcements.
     * @group Admin Academics
     */
    public function index(Request $request): JsonResponse
    {
        $query = Announcement::with([
            'school',
            'campus',
            'classRoom.course.department.faculty',
            'createdBy'
        ]);

        // Filter by school if provided
        if ($request->has('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        // Filter by campus if provided
        if ($request->has('campus_id')) {
            $query->where('campus_id', $request->campus_id);
        }

        // Filter by class if provided
        if ($request->has('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        // Filter by scope if provided
        if ($request->has('scope')) {
            $query->where('scope', $request->scope);
        }

        // Filter by priority if provided
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filter by active status if provided
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Filter by date range if provided
        if ($request->has('start_date')) {
            $query->where('publish_date', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->where('publish_date', '<=', $request->end_date);
        }

        // Filter by created by if provided
        if ($request->has('created_by')) {
            $query->where('created_by', $request->created_by);
        }

        $announcements = $query->orderBy('publish_date', 'desc')
                              ->orderBy('priority', 'desc')
                              ->get();

        return response()->json([
            'success' => true,
            'data' => $announcements,
            'message' => 'Announcements retrieved successfully'
        ]);
    }

    /**
     * Store a newly created announcement.
     * @group Admin Academics
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'school_id' => 'required|exists:schools,id',
                'campus_id' => 'nullable|exists:campuses,id',
                'class_id' => 'nullable|exists:classes,id',
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'priority' => 'required|in:low,normal,high,urgent',
                'scope' => 'required|in:school_wide,campus_wide,class_specific,department_specific',
                'publish_date' => 'required|date',
                'expiry_date' => 'nullable|date|after:publish_date',
                'is_active' => 'boolean',
            ]);

            // Validate scope consistency
            if ($validated['scope'] === 'campus_wide' && !$validated['campus_id']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Campus ID is required for campus-wide announcements'
                ], 422);
            }

            if ($validated['scope'] === 'class_specific' && !$validated['class_id']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Class ID is required for class-specific announcements'
                ], 422);
            }

            // Set created_by to current authenticated user
            $validated['created_by'] = Request()->user()->id;

            $announcement = Announcement::create($validated);

            return response()->json([
                'success' => true,
                'data' => $announcement->load([
                    'school',
                    'campus',
                    'classRoom.course.department.faculty',
                    'createdBy'
                ]),
                'message' => 'Announcement created successfully'
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create announcement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified announcement.
     * @group Admin Academics
     */
    public function show(Announcement $announcement): JsonResponse
    {
        $announcement->load([
            'school',
            'campus',
            'classRoom.course.department.faculty',
            'createdBy'
        ]);

        return response()->json([
            'success' => true,
            'data' => $announcement,
            'message' => 'Announcement retrieved successfully'
        ]);
    }

    /**
     * Update the specified announcement.
     * @group Admin Academics
     */
    public function update(Request $request, Announcement $announcement): JsonResponse
    {
        try {
            $validated = $request->validate([
                'school_id' => 'sometimes|required|exists:schools,id',
                'campus_id' => 'nullable|exists:campuses,id',
                'class_id' => 'nullable|exists:classes,id',
                'title' => 'sometimes|required|string|max:255',
                'content' => 'sometimes|required|string',
                'priority' => 'sometimes|required|in:low,normal,high,urgent',
                'scope' => 'sometimes|required|in:school_wide,campus_wide,class_specific,department_specific',
                'publish_date' => 'sometimes|required|date',
                'expiry_date' => 'nullable|date|after:publish_date',
                'is_active' => 'sometimes|boolean',
            ]);

            // Validate scope consistency if scope is being changed
            if (isset($validated['scope'])) {
                $scope = $validated['scope'];
                $campusId = $validated['campus_id'] ?? $announcement->campus_id;
                $classId = $validated['class_id'] ?? $announcement->class_id;

                if ($scope === 'campus_wide' && !$campusId) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Campus ID is required for campus-wide announcements'
                    ], 422);
                }

                if ($scope === 'class_specific' && !$classId) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Class ID is required for class-specific announcements'
                    ], 422);
                }
            }

            $announcement->update($validated);

            return response()->json([
                'success' => true,
                'data' => $announcement->fresh()->load([
                    'school',
                    'campus',
                    'classRoom.course.department.faculty',
                    'createdBy'
                ]),
                'message' => 'Announcement updated successfully'
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update announcement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified announcement.
     * @group Admin Academics
     */
    public function destroy(Announcement $announcement): JsonResponse
    {
        try {
            $announcement->delete();

            return response()->json([
                'success' => true,
                'message' => 'Announcement deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete announcement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Activate/deactivate an announcement.
     * @group Admin Academics
     */
    public function toggleStatus(Announcement $announcement): JsonResponse
    {
        try {
            $announcement->update(['is_active' => !$announcement->is_active]);

            $status = $announcement->is_active ? 'activated' : 'deactivated';

            return response()->json([
                'success' => true,
                'data' => $announcement->fresh()->load([
                    'school',
                    'campus',
                    'classRoom.course.department.faculty',
                    'createdBy'
                ]),
                'message' => "Announcement {$status} successfully"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle announcement status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get announcements by school.
     * @group Admin Academics
     */
    public function bySchool(School $school, Request $request): JsonResponse
    {
        $query = $school->announcements()
                       ->with([
                           'campus',
                           'classRoom.course.department.faculty',
                           'createdBy'
                       ]);

        // Filter by scope if provided
        if ($request->has('scope')) {
            $query->where('scope', $request->scope);
        }

        // Filter by priority if provided
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filter by active status if provided
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $announcements = $query->where('is_active', true)
                              ->where('publish_date', '<=', now()->toDateString())
                              ->where(function($q) {
                                  $q->whereNull('expiry_date')
                                    ->orWhere('expiry_date', '>=', now()->toDateString());
                              })
                              ->orderBy('publish_date', 'desc')
                              ->orderBy('priority', 'desc')
                              ->get();

        return response()->json([
            'success' => true,
            'data' => $announcements,
            'message' => 'School announcements retrieved successfully'
        ]);
    }

    /**
     * Get announcements by campus.
     * @group Admin Academics
     */
    public function byCampus(Campus $campus, Request $request): JsonResponse
    {
        $query = $campus->announcements()
                       ->with([
                           'school',
                           'classRoom.course.department.faculty',
                           'createdBy'
                       ]);

        // Filter by scope if provided
        if ($request->has('scope')) {
            $query->where('scope', $request->scope);
        }

        // Filter by priority if provided
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        $announcements = $query->where('is_active', true)
                              ->where('publish_date', '<=', now()->toDateString())
                              ->where(function($q) {
                                  $q->whereNull('expiry_date')
                                    ->orWhere('expiry_date', '>=', now()->toDateString());
                              })
                              ->orderBy('publish_date', 'desc')
                              ->orderBy('priority', 'desc')
                              ->get();

        return response()->json([
            'success' => true,
            'data' => $announcements,
            'message' => 'Campus announcements retrieved successfully'
        ]);
    }

    /**
     * Get announcements by class.
     * @group Admin Academics
     */
    public function byClass(ClassRoom $class, Request $request): JsonResponse
    {
        $query = $class->announcements()
                      ->with([
                          'school',
                          'campus',
                          'createdBy'
                      ]);

        // Filter by priority if provided
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        $announcements = $query->where('is_active', true)
                              ->where('publish_date', '<=', now()->toDateString())
                              ->where(function($q) {
                                  $q->whereNull('expiry_date')
                                    ->orWhere('expiry_date', '>=', now()->toDateString());
                              })
                              ->orderBy('publish_date', 'desc')
                              ->orderBy('priority', 'desc')
                              ->get();

        return response()->json([
            'success' => true,
            'data' => $announcements,
            'message' => 'Class announcements retrieved successfully'
        ]);
    }

    /**
     * Get urgent announcements.
     * @group Admin Academics
     */
    public function urgent(Request $request): JsonResponse
    {
        $query = Announcement::with([
            'school',
            'campus',
            'classRoom.course.department.faculty',
            'createdBy'
        ])
        ->where('priority', 'urgent')
        ->where('is_active', true)
        ->where('publish_date', '<=', now()->toDateString())
        ->where(function($q) {
            $q->whereNull('expiry_date')
              ->orWhere('expiry_date', '>=', now()->toDateString());
        });

        // Filter by school if provided
        if ($request->has('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        // Filter by campus if provided
        if ($request->has('campus_id')) {
            $query->where('campus_id', $request->campus_id);
        }

        $announcements = $query->orderBy('publish_date', 'desc')
                              ->limit(10)
                              ->get();

        return response()->json([
            'success' => true,
            'data' => $announcements,
            'message' => 'Urgent announcements retrieved successfully'
        ]);
    }

    /**
     * Get announcement statistics.
     * @group Admin Academics
    */
    public function statistics(Request $request): JsonResponse
    {
        $query = Announcement::query();

        // Filter by school if provided
        if ($request->has('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        // Filter by campus if provided
        if ($request->has('campus_id')) {
            $query->where('campus_id', $request->campus_id);
        }

        $stats = [
            'total_announcements' => $query->count(),
            'active_announcements' => $query->where('is_active', true)->count(),
            'urgent_announcements' => $query->where('priority', 'urgent')->where('is_active', true)->count(),
            'high_priority_announcements' => $query->where('priority', 'high')->where('is_active', true)->count(),
            'announcements_by_scope' => $query->selectRaw('scope, COUNT(*) as count')
                                           ->groupBy('scope')
                                           ->pluck('count', 'scope'),
            'announcements_by_priority' => $query->selectRaw('priority, COUNT(*) as count')
                                              ->groupBy('priority')
                                              ->pluck('count', 'priority'),
            'recent_announcements' => $query->where('publish_date', '>=', now()->subDays(7))
                                          ->where('is_active', true)
                                          ->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'Announcement statistics retrieved successfully'
        ]);
    }
} 