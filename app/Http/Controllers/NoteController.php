<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNoteRequest;
use App\Http\Requests\UpdateNoteRequest;
use App\Models\Note;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NoteController extends Controller
{
    /**
     * List all notes for a specific team.
     */
    public function index(Request $request, Team $team): JsonResponse
    {
        // Authorization: Ensure user is a member of this team
        if (!$request->user()->teams()->where('teams.id', $team->id)->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $notes = $team->notes()->with('author')->latest()->get();

        return response()->json($notes);
    }

    /**
     * Store a newly created note.
     */
    public function store(StoreNoteRequest $request, Team $team): JsonResponse
    {
        // Authorization: Ensure user is a member of this team
        if (!$request->user()->teams()->where('teams.id', $team->id)->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $note = Note::create([
            'team_id' => $team->id,
            'user_id' => $request->user()->id,
            'title' => $request['title'],
            'body' => $request['body'] ?? '',
        ]);

        return response()->json($note->load('author'), 201);
    }

    /**
     * Display a specific note.
     */
    public function show(Request $request, Note $note): JsonResponse
    {
        // Authorization: Ensure user is a member of the note's team
        if (!$request->user()->teams()->where('teams.id', $note->team_id)->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($note->load('author'));
    }

    /**
     * Update a specific note.
     */
    public function update(UpdateNoteRequest $request, Note $note): JsonResponse
    {
        // Authorization: Ensure user is a member of the note's team
        if (!$request->user()->teams()->where('teams.id', $note->team_id)->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $note->update($request->validated());

        return response()->json($note->fresh()->load('author'));
    }

    /**
     * Delete a specific note.
     */
    public function destroy(Request $request, Note $note): JsonResponse
    {
        // Authorization: Ensure user is a member of the note's team
        if (!$request->user()->teams()->where('teams.id', $note->team_id)->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $note->delete();

        return response()->json(['message' => 'Note deleted successfully']);
    }
}
