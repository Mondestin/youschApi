<?php

namespace App\Http\Controllers\Api\AdminAcademics;

use App\Http\Controllers\Controller;
use App\Models\AdminAcademics\Venue;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class VenueController extends Controller
{
    /**
     * Display a listing of venues.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Venue::query();

        // Filter by active status
        if ($request->has('active_only') && $request->boolean('active_only')) {
            $query->active();
        }

        // Filter by type
        if ($request->has('type')) {
            $query->byType($request->type);
        }

        // Search by name or description
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        // Sort by field
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // Get all venues without pagination
        $venues = $query->get();

        return response()->json([
            'success' => true,
            'data' => $venues,
            'message' => 'Lieux récupérés avec succès'
        ]);
    }

    /**
     * Store a newly created venue.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:venues,name',
            'description' => 'nullable|string',
            'capacity' => 'nullable|integer|min:1',
            'location' => 'nullable|string|max:255',
            'type' => ['required', Rule::in(['classroom', 'laboratory', 'auditorium', 'meeting_room', 'gymnasium', 'library', 'other'])],
            'is_active' => 'boolean',
        ]);

        $venue = Venue::create($validated);

        return response()->json([
            'success' => true,
            'data' => $venue,
            'message' => 'Lieu créé avec succès'
        ], 201);
    }

    /**
     * Display the specified venue.
     */
    public function show(Venue $venue): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $venue->load('timetables'),
            'message' => 'Lieu récupéré avec succès'
        ]);
    }

    /**
     * Update the specified venue.
     */
    public function update(Request $request, Venue $venue): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:venues,name,' . $venue->id,
            'description' => 'nullable|string',
            'capacity' => 'nullable|integer|min:1',
            'location' => 'nullable|string|max:255',
            'type' => ['required', Rule::in(['classroom', 'laboratory', 'auditorium', 'meeting_room', 'gymnasium', 'library', 'other'])],
            'is_active' => 'boolean',
        ]);

        $venue->update($validated);

        return response()->json([
            'success' => true,
            'data' => $venue,
            'message' => 'Lieu mis à jour avec succès'
        ]);
    }

    /**
     * Remove the specified venue.
     */
    public function destroy(Venue $venue): JsonResponse
    {
        // Check if venue has any timetables
        if ($venue->timetables()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer un lieu qui a des emplois du temps associés'
            ], 422);
        }

        $venue->delete();

        return response()->json([
            'success' => true,
            'message' => 'Lieu supprimé avec succès'
        ]);
    }

    /**
     * Get available venues for a specific time slot.
     */
    public function getAvailableVenues(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'type' => 'nullable|string|in:classroom,laboratory,auditorium,meeting_room,gymnasium,library,other',
        ]);

        $date = $validated['date'];
        $startTime = $validated['start_time'];
        $endTime = $validated['end_time'];

        // Get venues that don't have conflicting timetables
        $query = Venue::active()
            ->whereDoesntHave('timetables', function ($q) use ($date, $startTime, $endTime) {
                $q->where('date', $date)
                  ->where(function ($timeQuery) use ($startTime, $endTime) {
                      $timeQuery->whereBetween('start_time', [$startTime, $endTime])
                               ->orWhereBetween('end_time', [$startTime, $endTime])
                               ->orWhere(function ($overlapQuery) use ($startTime, $endTime) {
                                   $overlapQuery->where('start_time', '<=', $startTime)
                                              ->where('end_time', '>=', $endTime);
                               });
                  });
            });

        // Filter by type if specified
        if (isset($validated['type'])) {
            $query->byType($validated['type']);
        }

        $availableVenues = $query->get();

        return response()->json([
            'success' => true,
            'data' => $availableVenues,
            'message' => 'Lieux disponibles récupérés avec succès'
        ]);
    }

    /**
     * Toggle venue active status.
     */
    public function toggleStatus(Venue $venue): JsonResponse
    {
        $venue->update(['is_active' => !$venue->is_active]);

        return response()->json([
            'success' => true,
            'data' => $venue,
            'message' => 'Statut du lieu mis à jour avec succès'
        ]);
    }

    /**
     * Get venue statistics.
     */
    public function getStats(): JsonResponse
    {
        $stats = [
            'total_venues' => Venue::count(),
            'active_venues' => Venue::active()->count(),
            'inactive_venues' => Venue::where('is_active', false)->count(),
            'by_type' => Venue::selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type'),
            'total_capacity' => Venue::sum('capacity'),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'Statistiques des lieux récupérées avec succès'
        ]);
    }
}