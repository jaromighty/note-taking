<?php

use App\Models\Note;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

//uses(RefreshDatabase::class)->in('Feature');

describe('Note Authorization', function () {

    beforeEach(function () {
        // Create Users
        $this->userA = User::factory()->create(['name' => 'Alice']);
        $this->userB = User::factory()->create(['name' => 'Bob']);
        $this->userC = User::factory()->create(['name' => 'Charlie']); // Outsider

        // Create Teams
        $this->team1 = Team::create(['name' => 'Team Alpha', 'owner_id' => $this->userA->id]);
        $this->team2 = Team::create(['name' => 'Team Beta', 'owner_id' => $this->userB->id]);

        // Add Users to Teams
        $this->team1->users()->attach($this->userA->id);
        $this->team1->users()->attach($this->userB->id);

        // User C is NOT attached to any team

        // Create Notes
        $this->note1 = Note::create([
            'team_id' => $this->team1->id,
            'user_id' => $this->userA->id,
            'title' => 'Secret Alpha Note',
            'body' => 'Only Team Alpha should see this.',
        ]);
    });

    it('allows authenticated users in the team to view notes', function () {
        Sanctum::actingAs($this->userA);

        $response = $this->getJson("/api/teams/{$this->team1->id}/notes");

        $response->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['title' => 'Secret Alpha Note']);
    });

    it('blocks authenticated users outside the team from viewing notes', function () {
        Sanctum::actingAs($this->userC);

        $response = $this->getJson("/api/teams/{$this->team1->id}/notes");

        $response->assertStatus(403)
            ->assertJson(['message' => 'This action is unauthorized.']);
    });

    it('allows authenticated users to create notes in their team', function () {
        Sanctum::actingAs($this->userB);

        $payload = [
            'title' => 'New Bob Note',
            'body' => 'Bob is adding a note.',
        ];

        $response = $this->postJson("/api/teams/{$this->team1->id}/notes", $payload);

        $response->assertStatus(201)
            ->assertJsonFragment(['title' => 'New Bob Note']);

        expect(Note::where('title', 'New Bob Note')->exists())->toBeTrue();
    });

    it('blocks authenticated users from creating notes in other teams', function () {
        Sanctum::actingAs($this->userC);

        $payload = [
            'title' => 'Intruder Note',
            'body' => 'Trying to hack Team Alpha.',
        ];

        $response = $this->postJson("/api/teams/{$this->team1->id}/notes", $payload);

        $response->assertStatus(403);
    });

    it('allows users to update notes within the same team', function () {
        Sanctum::actingAs($this->userB);

        $response = $this->putJson("/api/notes/{$this->note1->id}", [
            'title' => 'Updated Title by Bob',
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'Updated Title by Bob']);
    });

    it('blocks users from updating notes from unauthorized teams', function () {
        Sanctum::actingAs($this->userC);

        $response = $this->putJson("/api/notes/{$this->note1->id}", [
            'title' => 'Hacked Title',
        ]);

        $response->assertStatus(403);
    });

    it('allows users to delete notes within their team', function () {
        Sanctum::actingAs($this->userA);

        $response = $this->deleteJson("/api/notes/{$this->note1->id}");

        $response->assertStatus(200);
        expect(Note::find($this->note1->id))->toBeNull();
    });

    it('blocks users from deleting notes from unauthorized teams', function () {
        Sanctum::actingAs($this->userC);

        $response = $this->deleteJson("/api/notes/{$this->note1->id}");

        $response->assertStatus(403);
        expect(Note::find($this->note1->id))->not->toBeNull();
    });

    it('blocks direct access to notes if the user is not in the team', function () {
        // Even if the user guesses the Note ID, they should be blocked
        Sanctum::actingAs($this->userC);

        $response = $this->getJson("/api/notes/{$this->note1->id}");

        $response->assertStatus(403);
    });
});
