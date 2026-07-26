<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Group;
use App\Models\Topic;
use App\Models\Post;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizSubmission;
use App\Models\QuizAnswer;
use App\Models\BlacklistLog;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database with a rich demo dataset.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // =====================================================================
        // 1. USERS
        // =====================================================================

        // Existing accounts
        $lecturer = User::create([
            'name' => 'Dr. Mary Nsabagwa',
            'email' => 'supervisor@academic.edu',
            'password' => Hash::make('123@Jesus'),
            'role' => 'lecturer',
            'status' => 'active',
        ]);

        $student = User::create([
            'name' => 'Ssenkuba Tonny',
            'email' => 'student@academic.edu',
            'password' => Hash::make('123@Jesus'),
            'role' => 'student',
            'status' => 'active',
        ]);

        // 2 Admins
        $admin1 = User::create([
            'name' => 'Admin One',
            'email' => 'admin1@academic.edu',
            'password' => Hash::make('123@Jesus'),
            'role' => 'admin',
            'status' => 'active',
        ]);
        $admin2 = User::create([
            'name' => 'Admin Two',
            'email' => 'admin2@academic.edu',
            'password' => Hash::make('123@Jesus'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        // 2 more lecturers
        $lecturer2 = User::create([
            'name' => 'Prof. John Doe',
            'email' => 'john.doe@academic.edu',
            'password' => Hash::make('123@Jesus'),
            'role' => 'lecturer',
            'status' => 'active',
        ]);
        $lecturer3 = User::create([
            'name' => 'Dr. Jane Smith',
            'email' => 'jane.smith@academic.edu',
            'password' => Hash::make('123@Jesus'),
            'role' => 'lecturer',
            'status' => 'active',
        ]);

        // 20 students (including existing, so we add 19 more)
        $students = collect([$student]);
        for ($i = 0; $i < 19; $i++) {
            $students->push(User::create([
                'name' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                'password' => Hash::make('123@Jesus'),
                'role' => 'student',
                'status' => 'active',
            ]));
        }

        // Add a few warned/blacklisted users for realism
        $warnedUser = User::create([
            'name' => 'Warned Student',
            'email' => 'warned@academic.edu',
            'password' => Hash::make('123@Jesus'),
            'role' => 'student',
            'status' => 'warned_once',
        ]);
        $students->push($warnedUser);

        $blacklistedUser = User::create([
            'name' => 'Blacklisted User',
            'email' => 'blacklisted@academic.edu',
            'password' => Hash::make('123@Jesus'),
            'role' => 'student',
            'status' => 'blacklisted',
            'blacklist_expires_at' => now()->addDays(14),
        ]);
        $students->push($blacklistedUser);
        // Add a blacklist log
        BlacklistLog::create([
            'user_id' => $blacklistedUser->id,
            'reason' => 'Repeated rule violations',
            'action_type' => 'manual_blacklist',
            'expires_at' => $blacklistedUser->blacklist_expires_at,
        ]);

        $allLecturers = collect([$lecturer, $lecturer2, $lecturer3]);
        $allUsers = $allLecturers->merge($students)->merge([$admin1, $admin2]);

        // =====================================================================
        // 2. GROUPS
        // =====================================================================

        $groupsData = [
            [
                'name' => 'Software Engineering Year 1',
                'description' => 'Foundational programming, OOP, and software design.',
                'lecturer' => $lecturer,
            ],
            [
                'name' => 'Data Science Year 2',
                'description' => 'Machine learning, data analysis, and visualization.',
                'lecturer' => $lecturer2,
            ],
            [
                'name' => 'Cybersecurity Year 3',
                'description' => 'Network security, cryptography, and ethical hacking.',
                'lecturer' => $lecturer3,
            ],
            [
                'name' => 'Cloud Computing Year 4',
                'description' => 'Distributed systems, cloud architecture, and DevOps.',
                'lecturer' => $lecturer,
            ],
        ];

        $groups = collect();
        foreach ($groupsData as $gData) {
            $group = Group::create([
                'name' => $gData['name'],
                'description' => $gData['description'],
                'created_by' => $gData['lecturer']->id,
            ]);
            $groups->push($group);
        }

        // Attach students to groups (each student joins 2-3 groups)
        $students->each(function ($student) use ($groups, $faker) {
            $numGroups = $faker->numberBetween(2, 3);
            $selectedGroups = $groups->random($numGroups);
            foreach ($selectedGroups as $group) {
                $group->users()->attach($student->id, [
                    'has_agreed_rules' => true,
                ]);
            }
        });

        // =====================================================================
        // 3. TOPICS (per group)
        // =====================================================================

        $topics = collect();
        $groups->each(function ($group) use ($faker, $allLecturers, $students, &$topics) {
            $numTopics = $faker->numberBetween(5, 8);
            $lecturer = $allLecturers->random();
            for ($i = 0; $i < $numTopics; $i++) {
                $topic = Topic::create([
                    'group_id' => $group->id,
                    'title' => $faker->sentence(6),
                    'description' => $faker->paragraph(2),
                    'creator_id' => $lecturer->id,
                    'ml_category' => $faker->randomElement(['General', 'Discussion', 'Assignment', 'Project', 'Exam Prep']),
                    'created_at' => $faker->dateTimeBetween('-3 months', 'now'),
                ]);
                $topics->push($topic);
            }
        });

        // =====================================================================
        // 4. POSTS (with nested replies)
        // =====================================================================

        $topics->each(function ($topic) use ($faker, $students, $allLecturers) {
            $numPosts = $faker->numberBetween(10, 20);
            $postIds = [];
            for ($i = 0; $i < $numPosts; $i++) {
                $author = $faker->boolean(70) ? $students->random() : $allLecturers->random();
                $post = Post::create([
                    'topic_id' => $topic->id,
                    'user_id' => $author->id,
                    'content' => $faker->paragraph(3),
                    'is_private' => $faker->boolean(10),
                    'is_pinned' => $i < 2 ? true : false,
                    'parent_id' => null,
                    'created_at' => $faker->dateTimeBetween($topic->created_at, 'now'),
                ]);
                $postIds[] = $post->id;

                // Occasionally add a reply (child)
                if ($faker->boolean(30) && count($postIds) > 1) {
                    $parentId = $faker->randomElement($postIds);
                    $replyAuthor = $faker->boolean(70) ? $students->random() : $allLecturers->random();
                    Post::create([
                        'topic_id' => $topic->id,
                        'user_id' => $replyAuthor->id,
                        'content' => $faker->paragraph(2),
                        'is_private' => false,
                        'is_pinned' => false,
                        'parent_id' => $parentId,
                        'created_at' => $faker->dateTimeBetween($topic->created_at, 'now'),
                    ]);
                }
            }
        });

        // =====================================================================
        // 5. QUIZZES (per group)
        // =====================================================================

        $groups->each(function ($group) use ($faker, $allLecturers, $students) {
            $numQuizzes = $faker->numberBetween(2, 3);
            $lecturer = $allLecturers->random();
            for ($i = 0; $i < $numQuizzes; $i++) {
                $quiz = Quiz::create([
                    'group_id' => $group->id,
                    'title' => $faker->sentence(5) . ' Quiz',
                    'description' => $faker->paragraph(2),
                    'creator_id' => $lecturer->id,
                    'is_active' => true,
                    'created_at' => $faker->dateTimeBetween('-2 months', 'now'),
                ]);

                // Add 5-10 questions
                $numQuestions = $faker->numberBetween(5, 10);
                for ($q = 0; $q < $numQuestions; $q++) {
                    $correctAnswer = $faker->randomElement(['A', 'B', 'C', 'D']);
                    QuizQuestion::create([
                        'quiz_id' => $quiz->id,
                        'question' => $faker->sentence(10) . '?',
                        'option_a' => $faker->sentence(3),
                        'option_b' => $faker->sentence(3),
                        'option_c' => $faker->sentence(3),
                        'option_d' => $faker->sentence(3),
                        'correct_answer' => $correctAnswer,
                        'created_at' => $faker->dateTimeBetween($quiz->created_at, 'now'),
                    ]);
                }

                // Create submissions for many students in this group
                $groupStudents = $group->users()->where('role', 'student')->get();
                $numSubmissions = $faker->numberBetween(5, $groupStudents->count());
                $selectedStudents = $groupStudents->random($numSubmissions);
                foreach ($selectedStudents as $student) {
                    $answersPayload = [];
                    $questions = $quiz->questions;
                    $score = 0;
                    foreach ($questions as $question) {
                        $chosen = $faker->randomElement(['A', 'B', 'C', 'D']);
                        $answersPayload[$question->id] = $chosen;
                        if ($chosen === $question->correct_answer) {
                            $score++;
                        }
                    }
                    $total = $questions->count();
                    $scorePercent = $total > 0 ? round(($score / $total) * 100, 2) : 0;

                    $submission = QuizSubmission::create([
                        'quiz_id' => $quiz->id,
                        'user_id' => $student->id,
                        'score' => $scorePercent,
                        'answers_payload' => $answersPayload,
                        'is_auto_submitted' => $faker->boolean(20),
                        'created_at' => $faker->dateTimeBetween($quiz->created_at, 'now'),
                    ]);

                    // Also create individual QuizAnswer records
                    foreach ($answersPayload as $questionId => $answer) {
                        QuizAnswer::create([
                            'submission_id' => $submission->id,
                            'question_id' => $questionId,
                            'selected_answer' => $answer,
                            'is_correct' => $answer === $questions->where('id', $questionId)->first()->correct_answer,
                        ]);
                    }
                }
            }
        });

        // =====================================================================
        // 6. FINAL MESSAGE
        // =====================================================================

        $this->command->info('✅ Database seeded with realistic demo data!');
        $this->command->info("Users: {$allUsers->count()} (2 admins, 3 lecturers, 20 students + extras)");
        $this->command->info("Groups: {$groups->count()}");
        $this->command->info("Topics: {$topics->count()}");
        $this->command->info("Posts: " . Post::count());
        $this->command->info("Quizzes: " . Quiz::count());
        $this->command->info("Submissions: " . QuizSubmission::count());
    }
}