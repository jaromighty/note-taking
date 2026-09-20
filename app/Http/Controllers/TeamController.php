<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTeamRequest;
use App\Http\Requests\UpdateTeamRequest;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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
        // Authorization Check: Ensure user is part of this team
        if (!$request->user()->teams()->where('teams.id', $team->id)->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $team->load(['users', 'owner']);

        return response()->json($team);
    }

    /**
     * Update the specified team in storage.
     */
    public function update(UpdateTeamRequest $request, Team $team): JsonResponse
    {
        // Authorization Check
        if (!$request->user()->teams()->where('teams.id', $team->id)->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $team->update($request->validated());

        return response()->json($team);
    }

    /**
     * Remove the specified team from storage.
     */
    public function destroy(Request $request, Team $team): JsonResponse
    {
        if ($team->owner_id !== $request->user()->id) {
            return response()->json(['message' => 'Only the team owner can delete this team.'], 403);
        }

        $team->delete();

        return response()->json(['message' => 'Team deleted successfully']);
    }
}
