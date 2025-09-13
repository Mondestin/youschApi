<?php

namespace App\Http\Controllers\Api\AdminAcademics;

use App\Http\Controllers\Controller;
use App\Models\AdminAcademics\ClassRoom;
use App\Models\AdminAcademics\Campus;
use App\Models\AdminAcademics\Course;
use App\Models\AdminAcademics\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class ClassController extends Controller
{
    /**
     * Display a listing of classes.
     * @group Admin Academics
    */
    public function index(Request $request): JsonResponse
    {
        $query = ClassRoom::with(['campus.school', 'course.department.faculty', 'subjects', 'teachers']);

        // Filter by campus if provided
        if ($request->has('campus_id')) {
            $query->where('campus_id', $request->campus_id);
        }

        // Filter by course if provided
        if ($request->has('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        // Filter by school if provided
        if ($request->has('school_id')) {
            $query->whereHas('campus', function($q) use ($request) {
                $q->where('school_id', $request->school_id);
            });
        }

        $classes = $query->get();

        return response()->json([
            'success' => true,
            'data' => $classes,
            'message' => 'Classes récupérées avec succès'
        ]);
    }

    /**
     * Store a newly created class.
     * @group Admin Academics
    */
    public function store(Request $request): JsonResponse
    {
        try {
            // Set locale to French for validation messages
            app()->setLocale('fr');
            
            $validated = $request->validate([
                'campus_id' => 'required|exists:campuses,id',
                'course_id' => 'required|exists:courses,id',
                'name' => 'required|string|max:255',
                'capacity' => 'required|integer|min:1',
            ]);

            $class = ClassRoom::create($validated);

            return response()->json([
                'success' => true,
                'data' => $class->load(['campus.school', 'course.department.faculty', 'subjects', 'teachers']),
                'message' => 'Classe créée avec succès'
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Échec de la validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de créer la classe',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified class.
     * @group Admin Academics
    */
    public function show(ClassRoom $class): JsonResponse
    {
        $class->load([
            'campus.school',
            'course.department.faculty',
            'subjects.coordinator',
            'teachers',
            'timetables.subject',
            'exams',
            'studentEnrollments.student',
            'teacherAssignments.teacher'
        ]);

        return response()->json([
            'success' => true,
            'data' => $class,
            'message' => 'Classe récupérée avec succès'
        ]);
    }

    /**
     * Update the specified class.
     * @group Admin Academics
    */
    public function update(Request $request, ClassRoom $class): JsonResponse
    {
        try {
            // Set locale to French for validation messages
            app()->setLocale('fr');
            
            $validated = $request->validate([
                'campus_id' => 'sometimes|required|exists:campuses,id',
                'course_id' => 'sometimes|required|exists:courses,id',
                'name' => 'sometimes|required|string|max:255',
                'capacity' => 'sometimes|required|integer|min:1',
            ]);

            $class->update($validated);

            return response()->json([
                'success' => true,
                'data' => $class->fresh()->load(['campus.school', 'course.department.faculty', 'subjects', 'teachers']),
                'message' => 'Classe mise à jour avec succès'
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Échec de la validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de mettre à jour la classe',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified class.
     * @group Admin Academics
    */
    public function destroy(ClassRoom $class): JsonResponse
    {
        try {
            // Check if class has any related data
            if ($class->timetables()->exists() || $class->exams()->exists() || 
                $class->studentEnrollments()->exists() || $class->teacherAssignments()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer la classe avec des données liées. Veuillez d\'abord supprimer les enregistrements liés.'
                ], 422);
            }

            $class->delete();

            return response()->json([
                'success' => true,
                'message' => 'Classe supprimée avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer la classe',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign a subject to a class.
     * @group Admin Academics
    */
    public function assignSubject(Request $request, ClassRoom $class): JsonResponse
    {
        try {
            // Set locale to French for validation messages
            app()->setLocale('fr');
            
            $validated = $request->validate([
                'subject_id' => 'required|exists:subjects,id',
                'teacher_id' => 'nullable|exists:users,id',
            ]);

            // Check if subject is already assigned
            if ($class->subjects()->where('subject_id', $validated['subject_id'])->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette matière est déjà assignée à cette classe'
                ], 422);
            }

            $class->subjects()->attach($validated['subject_id'], [
                'teacher_id' => $validated['teacher_id'] ?? null
            ]);

            return response()->json([
                'success' => true,
                'data' => $class->fresh()->load(['subjects.coordinator']),
                'message' => 'Matière assignée à la classe avec succès'
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Échec de la validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible d\'assigner la matière à la classe',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove a subject from a class.
     * @group Admin Academics
    */
    public function removeSubject(ClassRoom $class, Subject $subject): JsonResponse
    {
        try {
            $class->subjects()->detach($subject->id);

            return response()->json([
                'success' => true,
                'message' => 'Matière supprimée de la classe avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer la matière de la classe',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign a teacher to a class.
     * @group Admin Academics
    */
    public function assignTeacher(Request $request, ClassRoom $class): JsonResponse
    {
        try {
            // Set locale to French for validation messages
            app()->setLocale('fr');
            
            $validated = $request->validate([
                'subject_id' => 'required|exists:subjects,id',
                'teacher_id' => 'required|exists:users,id',
            ]);

            // Check if subject is assigned to class
            if (!$class->subjects()->where('subject_id', $validated['subject_id'])->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Subject is not assigned to this class'
                ], 422);
            }

            // Update the pivot table
            $class->subjects()->updateExistingPivot($validated['subject_id'], [
                'teacher_id' => $validated['teacher_id']
            ]);

            return response()->json([
                'success' => true,
                'data' => $class->fresh()->load(['subjects.coordinator']),
                'message' => 'Enseignant assigné à la classe avec succès'
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Échec de la validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible d\'assigner l\'enseignant à la classe',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get class statistics.
     * @group Admin Academics
    */
    public function statistics(ClassRoom $class): JsonResponse
    {
        $stats = [
            'total_subjects' => $class->subjects()->count(),
            'total_teachers' => $class->teachers()->count(),
            'total_students' => $class->studentEnrollments()->count(),
            'total_timetables' => $class->timetables()->count(),
            'total_exams' => $class->exams()->count(),
            'enrollment_rate' => $class->studentEnrollments()->count() / $class->capacity * 100,
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'Statistiques de classe récupérées avec succès'
        ]);
    }
} 