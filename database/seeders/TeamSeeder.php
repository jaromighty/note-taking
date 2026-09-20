<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create two users
        $user1 = User::create([
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'password' => Hash::make('password'),
        ]);

        $user2 = User::create([
            'name' => 'Bob',
            'email' => 'bob@example.com',
            'password' => Hash::make('password'),
        ]);

        // Create a team owned by Alice
        $team = Team::create([
            'name' => 'Marketing Team',
            'owner_id' => $user1->id,
        ]);

        // Add Alice to the team
        $team->users()->attach($user1->id);
        // Add Bob to the team
        $team->users()->attach($user2->id);
    }
}
