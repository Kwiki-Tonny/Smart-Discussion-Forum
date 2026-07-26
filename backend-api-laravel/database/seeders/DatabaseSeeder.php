<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Group;
use App\Models\Topic;
use App\Models\Post;
use App\Models\PostLike;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizSubmission;
use App\Models\QuizAnswer;
use App\Models\BlacklistLog;
use App\Models\Setting;
use App\Models\CategoryTerm;
use App\Models\UserInteraction;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $faker = Faker::create();

        // =====================================================================
        // 1. USERS
        // =====================================================================

        // Admins
        $admin1 = User::firstOrCreate(
            ['email' => 'admin1@academic.edu'],
            ['name' => 'Admin One', 'password' => Hash::make('123@Jesus'), 'role' => 'admin', 'status' => 'active']
        );
        $admin2 = User::firstOrCreate(
            ['email' => 'admin2@academic.edu'],
            ['name' => 'Admin Two', 'password' => Hash::make('123@Jesus'), 'role' => 'admin', 'status' => 'active']
        );

        // Lecturers
        $lecturer1 = User::firstOrCreate(
            ['email' => 'supervisor@academic.edu'],
            ['name' => 'Dr. Mary Nsabagwa', 'password' => Hash::make('123@Jesus'), 'role' => 'lecturer', 'status' => 'active']
        );
        $lecturer2 = User::firstOrCreate(
            ['email' => 'john.doe@academic.edu'],
            ['name' => 'Prof. John Doe', 'password' => Hash::make('123@Jesus'), 'role' => 'lecturer', 'status' => 'active']
        );
        $lecturer3 = User::firstOrCreate(
            ['email' => 'jane.smith@academic.edu'],
            ['name' => 'Dr. Jane Smith', 'password' => Hash::make('123@Jesus'), 'role' => 'lecturer', 'status' => 'active']
        );

        // Students (20 unique)
        $students = collect();
        $studentEmails = [
            'student@academic.edu',
            'student1@academic.edu','student2@academic.edu','student3@academic.edu',
            'student4@academic.edu','student5@academic.edu','student6@academic.edu',
            'student7@academic.edu','student8@academic.edu','student9@academic.edu',
            'student10@academic.edu','student11@academic.edu','student12@academic.edu',
            'student13@academic.edu','student14@academic.edu','student15@academic.edu',
            'student16@academic.edu','student17@academic.edu','student18@academic.edu',
            'student19@academic.edu',
        ];
        foreach ($studentEmails as $email) {
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => ($email === 'student@academic.edu') ? 'Ssenkuba Tonny' : $faker->name,
                    'password' => Hash::make('123@Jesus'),
                    'role' => 'student',
                    'status' => 'active',
                ]
            );
            $students->push($user);
        }

        // Warned & blacklisted users
        $warnedUser = User::firstOrCreate(
            ['email' => 'warned@academic.edu'],
            ['name' => 'Warned Student', 'password' => Hash::make('123@Jesus'), 'role' => 'student', 'status' => 'warned_once']
        );
        $students->push($warnedUser);

        $blacklistedUser = User::firstOrCreate(
            ['email' => 'blacklisted@academic.edu'],
            [
                'name' => 'Blacklisted User',
                'password' => Hash::make('123@Jesus'),
                'role' => 'student',
                'status' => 'blacklisted',
                'blacklist_expires_at' => now()->addDays(14),
            ]
        );
        $students->push($blacklistedUser);

        // Blacklist log for blacklisted user
        if (!BlacklistLog::where('user_id', $blacklistedUser->id)->exists()) {
            BlacklistLog::create([
                'user_id' => $blacklistedUser->id,
                'reason' => 'Repeated rule violations',
                'action_type' => 'manual_blacklist',
                'expires_at' => $blacklistedUser->blacklist_expires_at,
            ]);
        }

        $allLecturers = collect([$lecturer1, $lecturer2, $lecturer3]);
        $allUsers = $allLecturers->merge($students)->merge([$admin1, $admin2]);

        // =====================================================================
        // 2. GROUPS
        // =====================================================================

        $groupsData = [
            ['name' => 'Software Engineering Year 1', 'description' => 'Foundational programming, OOP, and software design.', 'lecturer' => $lecturer1],
            ['name' => 'Data Science Year 2', 'description' => 'Machine learning, data analysis, and visualization.', 'lecturer' => $lecturer2],
            ['name' => 'Cybersecurity Year 3', 'description' => 'Network security, cryptography, and ethical hacking.', 'lecturer' => $lecturer3],
            ['name' => 'Cloud Computing Year 4', 'description' => 'Distributed systems, cloud architecture, and DevOps.', 'lecturer' => $lecturer1],
        ];

        $groups = collect();
        foreach ($groupsData as $gData) {
            $group = Group::firstOrCreate(
                ['name' => $gData['name']],
                ['description' => $gData['description'], 'created_by' => $gData['lecturer']->id]
            );
            $groups->push($group);
        }

        // Attach students to groups (each joins 2–3)
        $students->each(function ($student) use ($groups, $faker) {
            $selected = $groups->random($faker->numberBetween(2, 3));
            foreach ($selected as $group) {
                if (!$group->users()->where('user_id', $student->id)->exists()) {
                    $group->users()->attach($student->id, ['has_agreed_rules' => true]);
                }
            }
        });

        // =====================================================================
        // 3. TOPICS (per group)
        // =====================================================================

        $topics = collect();
        $groups->each(function ($group) use ($faker, $allLecturers, &$topics) {
            for ($i = 0; $i < $faker->numberBetween(5, 8); $i++) {
                $topic = Topic::create([
                    'group_id'    => $group->id,
                    'title'       => $faker->sentence(6),
                    'description' => $faker->paragraph(2),
                    'creator_id'  => $allLecturers->random()->id,
                    'ml_category' => $faker->randomElement(['General', 'Discussion', 'Assignment', 'Project', 'Exam Prep']),
                    'created_at'  => $faker->dateTimeBetween('-3 months', 'now'),
                ]);
                $topics->push($topic);
            }
        });

        // =====================================================================
        // 4. POSTS (with replies, likes, attachments)
        // =====================================================================

        $topics->each(function ($topic) use ($faker, $students, $allLecturers) {
            $postIds = [];
            for ($i = 0; $i < $faker->numberBetween(10, 20); $i++) {
                $author = $faker->boolean(70) ? $students->random() : $allLecturers->random();
                $post = Post::create([
                    'topic_id'    => $topic->id,
                    'user_id'     => $author->id,
                    'content'     => $faker->paragraph(3),
                    'is_private'  => $faker->boolean(10),
                    'is_pinned'   => $i < 2,
                    'parent_id'   => null,
                    'attachments' => $faker->boolean(20) ? [$faker->word . '.jpg'] : [],
                    'created_at'  => $faker->dateTimeBetween($topic->created_at, 'now'),
                ]);
                $postIds[] = $post->id;

                // Likes
                foreach ($students->random($faker->numberBetween(0, 10)) as $liker) {
                    if (!$post->likes()->where('user_id', $liker->id)->exists()) {
                        PostLike::create(['post_id' => $post->id, 'user_id' => $liker->id]);
                    }
                }

                // Reply
                if ($faker->boolean(30) && count($postIds) > 1) {
                    $replyAuthor = $faker->boolean(70) ? $students->random() : $allLecturers->random();
                    Post::create([
                        'topic_id'    => $topic->id,
                        'user_id'     => $replyAuthor->id,
                        'content'     => $faker->paragraph(2),
                        'is_private'  => false,
                        'is_pinned'   => false,
                        'parent_id'   => $faker->randomElement($postIds),
                        'attachments' => [],
                        'created_at'  => $faker->dateTimeBetween($topic->created_at, 'now'),
                    ]);
                }
            }
        });

        // =====================================================================
        // 5. QUIZZES (per group)
        // =====================================================================

        $groups->each(function ($group) use ($faker, $allLecturers, $students) {
            for ($q = 0; $q < $faker->numberBetween(2, 3); $q++) {
                $startsAt = $faker->dateTimeBetween('-1 month', '+2 weeks');
                $endsAt = (clone $startsAt)->modify('+1 week');

                $quiz = Quiz::create([
                    'group_id'          => $group->id,
                    'created_by'        => $allLecturers->random()->id,
                    'title'             => $faker->sentence(5) . ' Quiz',
                    'duration'          => $faker->numberBetween(15, 60),
                    'allowed_categories'=> $faker->randomElements(['General', 'Discussion', 'Assignment'], 2),
                    'starts_at'         => $startsAt,
                    'ends_at'           => $endsAt,
                    'is_active'         => true,
                ]);

                // Questions
                for ($qi = 0; $qi < $faker->numberBetween(5, 10); $qi++) {
                    $correct = $faker->randomElement(['A', 'B', 'C', 'D']);
                    QuizQuestion::create([
                        'quiz_id'        => $quiz->id,
                        'question'       => $faker->sentence(10) . '?',
                        'type'           => 'single',
                        'options'        => ['A' => $faker->sentence(3), 'B' => $faker->sentence(3), 'C' => $faker->sentence(3), 'D' => $faker->sentence(3)],
                        'correct_answers'=> [$correct],
                        'points'         => $faker->numberBetween(1, 5),
                        'order'          => $qi + 1,
                    ]);
                }

                // Submissions
                $groupStudents = $group->users()->where('role', 'student')->get();
                $selected = $groupStudents->random($faker->numberBetween(5, $groupStudents->count()));
                foreach ($selected as $student) {
                    $questions = $quiz->questions;
                    $answers = [];
                    $score = 0;
                    foreach ($questions as $question) {
                        $chosen = $faker->randomElement(['A', 'B', 'C', 'D']);
                        $answers[$question->id] = $chosen;
                        if (in_array($chosen, $question->correct_answers)) {
                            $score += $question->points;
                        }
                    }
                    $total = $questions->sum('points');
                    $scorePercent = $total > 0 ? round(($score / $total) * 100, 2) : 0;

                    $submission = QuizSubmission::create([
                        'quiz_id'           => $quiz->id,
                        'user_id'           => $student->id,
                        'score'             => $scorePercent,
                        'answers_payload'   => $answers,
                        'is_auto_submitted' => $faker->boolean(20),
                        'created_at'        => $faker->dateTimeBetween($quiz->starts_at, $quiz->ends_at),
                    ]);

                    foreach ($answers as $questionId => $answer) {
                        $question = $questions->where('id', $questionId)->first();
                        $isCorrect = in_array($answer, $question->correct_answers);
                        QuizAnswer::create([
                            'submission_id' => $submission->id,
                            'question_id'   => $questionId,
                            'answer'        => $answer,
                            'is_correct'    => $isCorrect,
                            'points_earned' => $isCorrect ? $question->points : 0,
                        ]);
                    }
                }
            }
        });

        // =====================================================================
        // 6. SETTINGS
        // =====================================================================

        $defaults = [
            ['key' => 'inactivity_warning_1', 'value' => '7'],
            ['key' => 'inactivity_warning_2', 'value' => '14'],
            ['key' => 'inactivity_blacklist', 'value' => '21'],
            ['key' => 'blacklist_duration', 'value' => '14'],
            ['key' => 'max_login_attempts', 'value' => '5'],
        ];
        foreach ($defaults as $s) {
            Setting::firstOrCreate(['key' => $s['key']], ['value' => $s['value']]);
        }

    // =====================================================================
    // 7. CATEGORY TERMS
    // =====================================================================

    $categories = ['General', 'Discussion', 'Assignment', 'Project', 'Exam Prep', 'Announcement'];
    $groups->each(function ($group) use ($faker, $categories) {
        for ($i = 0; $i < $faker->numberBetween(3, 6); $i++) {
            CategoryTerm::updateOrCreate(
                ['term' => $faker->word, 'group_id' => $group->id],
                [
                    'category'  => $faker->randomElement($categories),
                    'frequency' => $faker->numberBetween(1, 20),
                ]
            );
        }
    });
        // =====================================================================
        // 8. USER INTERACTIONS – FIXED: disable timestamps
        // =====================================================================

        $topics->each(function ($topic) use ($faker, $students) {
            for ($i = 0; $i < $faker->numberBetween(5, 20); $i++) {
                // Create interaction without updated_at
                $interaction = new UserInteraction();
                $interaction->timestamps = false; // Prevent auto-updated_at
                $interaction->fill([
                    'user_id'     => $students->random()->id,
                    'topic_id'    => $topic->id,
                    'action_type' => $faker->randomElement(['view', 'like', 'download', 'comment']),
                    'created_at'  => $faker->dateTimeBetween($topic->created_at, 'now'),
                ]);
                $interaction->save();
            }
        });

        // =====================================================================
        // 9. FINAL MESSAGE
        // =====================================================================

        $this->command->info('✅ Database seeded with realistic demo data!');
        $this->command->info("Users: {$allUsers->count()} (2 admins, 3 lecturers, " . $students->count() . " students)");
        $this->command->info("Groups: {$groups->count()}");
        $this->command->info("Topics: {$topics->count()}");
        $this->command->info("Posts: " . Post::count());
        $this->command->info("Quizzes: " . Quiz::count());
        $this->command->info("Submissions: " . QuizSubmission::count());
        $this->command->info("Settings: " . Setting::count());
        $this->command->info("Category Terms: " . CategoryTerm::count());
        $this->command->info("User Interactions: " . UserInteraction::count());
    }
}