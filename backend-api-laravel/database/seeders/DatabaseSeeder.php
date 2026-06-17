<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Group;
use App\Models\Topic;
use App\Models\Post;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database with core matrix entries.
     */
    public function run(): void
    {
        // =========================================================================
        // STEP 1: INJECT AUTHENTICATION ACCOUNTS (Parent Layer)
        // =========================================================================
        
        // 1. Create a Lecturer Account
        $lecturer = User::create([
            'name' => 'Dr. Mary Nsabagwa',
            'email' => 'supervisor@academic.edu',
            'password' => Hash::make('123@Jesus'), // Cryptographically salts and hashes the password string
            'role' => 'lecturer',
            'status' => 'active',
        ]);

        // 2. Create a Student Account
        $student = User::create([
            'name' => 'Ssenkuba Tonny',
            'email' => 'student@academic.edu',
            'password' => Hash::make('123@Jesus'),
            'role' => 'student',
            'status' => 'active',
        ]);


        // =========================================================================
        // STEP 2: INJECT COHORT WORKSPACES (Parent Layer)
        // =========================================================================
        
        $seGroup = Group::create([
            'name' => 'Software Engineering Year 1',
            'description' => 'Official hub for academic conversations, project architecture reviews, and algorithmic boards.',
        ]);


        // =========================================================================
        // STEP 3: BRIDGE COHORTS AND USERS (Onboarding Gate Layer)
        // =========================================================================
        
        // This targets the 'group_user' bridge table. 
        // It hooks the student to the group and sets the required onboarding rule flag to true.
        $seGroup->users()->attach($student->id, [
            'has_agreed_rules' => true
        ]);


        // =========================================================================
        // STEP 4: MOUNT SPECIFIC DISCUSSION CHANNELS (Child Layer)
        // =========================================================================
        
        // This record links directly to groups.id and users.id via foreign keys
        $topic = Topic::create([
            'group_id' => $seGroup->id,
            'title' => 'Understanding Object Polymorphism and Java Constructors',
            'creator_id' => $lecturer->id,
            'ml_category' => 'Java Fundamentals',
        ]);


        // =========================================================================
        // STEP 5: FIRE THE INTERACTIVE DISCUSSION POST FEED (Grandchild Layer)
        // =========================================================================
        
        // The first text response pinning the thread alive
        Post::create([
            'topic_id' => $topic->id,
            'user_id' => $lecturer->id,
            'content' => 'Welcome to the platform team. Please use this thread to post your sample class code fragments for active structural evaluation blocks.',
            'is_private' => false,
        ]);
    }
}