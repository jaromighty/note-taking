<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTeamMemberRequest;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TeamMemberController extends Controller
{
    /**
     * Add a user to a team.
     */
    public function store(StoreTeamMemberRequest $request, Team $team): JsonResponse
    {
        if (!$request->user()->teams()->where('teams.id', $team->id)->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Prevent adding self or existing members
        if ($team->users()->where('user_id', $request['user_id'])->exists()) {
            return response()->json(['message' => 'User is already a member.'], 409);
        }

        $team->users()->attach($request['user_id']);

        return response()->json(['message' => 'User added to team successfully']);
    }

    /**
     * Remove a user from a team.
     */
    public function destroy(Request $request, Team $team, User $user): JsonResponse
    {
        // Authorization: Can anyone remove, or only owner?
        // Let's allow owner or the user themselves to leave.
        $currentUser = $request->user();

        if ($team->owner_id !== $currentUser->id && $currentUser->id !== $user->id) {
            return response()->json(['message' => 'Only the owner or the user themselves can remove a member.'], 403);
        }

        $team->users()->detach($user->id);

        return response()->json(['message' => 'User removed from team successfully']);
    }
}
