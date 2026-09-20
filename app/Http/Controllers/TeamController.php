<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTeamRequest;
use App\Http\Requests\UpdateTeamRequest;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class TeamController extends Controller
{
    /**
     * Display a listing of teams the user belongs to.
     */
    public function index(Request $request): JsonResponse
    {
        $teams = $request->user()->teams()->with('owner')->get();

        return response()->json($teams);
    }

    /**
     * Store a newly created team in storage.
     */
    public function store(StoreTeamRequest $request): JsonResponse
    {
        $team = Team::create([
            'name' => $request['name'],
            'owner_id' => $request->user()->id,
        ]);

        // Automatically add the creator as a member
        $team->users()->attach($request->user()->id);

        return response()->json($team, 201);
    }

    /**
     * Display the specified team.
     */
    public function show(Request $request, Team $team): JsonResponse
    {
        Gate::authorize('view', $team);

        $team->load(['users', 'owner']);

        return response()->json($team);
    }

    /**
     * Update the specified team in storage.
     */
    public function update(UpdateTeamRequest $request, Team $team): JsonResponse
    {
        Gate::authorize('update', $team);

        $team->update($request->validated());

        return response()->json($team);
    }

    /**
     * Remove the specified team from storage.
     */
    public function destroy(Request $request, Team $team): JsonResponse
    {
        Gate::authorize('delete', $team);

        $team->delete();

        return response()->json(['message' => 'Team deleted successfully']);
    }
}
