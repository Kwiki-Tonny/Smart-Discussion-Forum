<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Topic;
use App\Models\Quiz;
use App\Models\Post;
use App\Models\PostLike;
use App\Models\QuizSubmission;
use App\Models\BlacklistLog;    
use App\Models\UserInteraction;  
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class StudentController extends Controller
{
    /**
     * Student Dashboard
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get groups the user belongs to
        $groups = $user->groups()
            ->withCount('topics')
            ->with(['topics' => function($query) {
                $query->latest()->limit(1);
            }])
            ->get();
        
        $groupIds = $user->groups()->pluck('groups.id')->toArray();
        
        // Get recent topics from user's groups
        $recentTopics = Topic::whereIn('group_id', $groupIds)
            ->with(['group', 'creator'])
            ->withCount('posts')
            ->latest()
            ->limit(10)
            ->get();
        
        // Affinity Calculator for recommendations
        $affinityCalculator = app(\App\Services\AffinityCalculator::class);
        $recommendations = $affinityCalculator->getRecommendations($user->id, 5);
        
        // ✅ FIXED: Only show quizzes that are active or upcoming (not ended)
        try {
            $upcomingQuizzes = Quiz::whereIn('group_id', $groupIds)
                ->where('is_active', true)
                ->where(function($query) {
                    $query->where('starts_at', '>', now())
                        ->orWhere(function($q) {
                            $q->where('starts_at', '<=', now())
                                ->where('ends_at', '>=', now());
                        });
                })
                ->with('group')
                ->orderBy('starts_at', 'asc')
                ->limit(5)
                ->get();
        } catch (\Exception $e) {
            $upcomingQuizzes = collect([]);
        }
        
        // Get available groups
        $availableGroups = Group::whereNotIn('id', $groupIds)
            ->withCount(['topics', 'users'])
            ->orderBy('name')
            ->get();
        
        // Stats
        $totalTopics = Topic::whereIn('group_id', $groupIds)->where('creator_id', $user->id)->count();
        $totalPosts = Post::where('user_id', $user->id)->count();
        $totalLikes = PostLike::where('user_id', $user->id)->count();
        $totalQuizzesTaken = QuizSubmission::where('user_id', $user->id)->count();
        
        // Affinity scores
        $affinityScores = $affinityCalculator->getAffinity($user->id);
        
        return view('student.dashboard', compact(
            'groups', 
            'availableGroups',
            'recentTopics', 
            'recommendations', 
            'upcomingQuizzes',
            'totalTopics',
            'totalPosts',
            'totalLikes',
            'totalQuizzesTaken',
            'affinityScores'
        ));
    }

        /**
     * Clear user's affinity cache (useful after interactions)
     */
    public function clearAffinityCache()
    {
        app(\App\Services\AffinityCalculator::class)->clearCache(Auth::id());
        return redirect()->back()->with('success', 'Recommendations refreshed.');
    }


    /**
     * Show all recommendations for the user
     */
    public function recommendations()
    {
        $user = Auth::user();
        $affinityCalculator = app(\App\Services\AffinityCalculator::class);
        
        $recommendations = $affinityCalculator->getRecommendations($user->id, 20);
        $affinityScores = $affinityCalculator->getAffinity($user->id);
        
        return view('student.recommendations', compact('recommendations', 'affinityScores'));
    }

    /**
     * Show student profile page
     */
    public function profile()
    {
        $user = Auth::user();

        // Basic stats
        $totalTopics = Topic::where('creator_id', $user->id)->count();
        $totalPosts = Post::where('user_id', $user->id)->count();
        $totalLikes = PostLike::where('user_id', $user->id)->count();
        $totalQuizzesTaken = QuizSubmission::where('user_id', $user->id)->count();

        // Recent activity (topics, posts, likes)
        $recentActivity = collect();

        // Get recent topics
        $topics = Topic::where('creator_id', $user->id)
            ->with(['group'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(function($item) {
                return (object) [
                    'type' => 'topic',
                    'title' => $item->title,
                    'topic_id' => $item->id,
                    'group_id' => $item->group_id,
                    'content' => null,
                    'post_id' => null,
                    'created_at' => $item->created_at,
                ];
            });

        // Get recent posts
        $posts = Post::where('user_id', $user->id)
            ->with(['topic.group'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(function($item) {
                return (object) [
                    'type' => 'post',
                    'title' => $item->topic->title ?? 'Deleted Topic',
                    'topic_id' => $item->topic_id,
                    'group_id' => $item->topic->group_id ?? null,
                    'content' => $item->content,
                    'post_id' => $item->id,
                    'created_at' => $item->created_at,
                ];
            });

        // Get recent likes
        $likes = PostLike::where('user_id', $user->id)
            ->with(['post.topic.group'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(function($item) {
                return (object) [
                    'type' => 'like',
                    'title' => $item->post->topic->title ?? 'Deleted Topic',
                    'topic_id' => $item->post->topic_id ?? null,
                    'group_id' => $item->post->topic->group_id ?? null,
                    'content' => $item->post->content ?? 'Deleted Post',
                    'post_id' => $item->post_id,
                    'created_at' => $item->created_at,
                ];
            });

        // Merge and sort
        $recentActivity = $topics->concat($posts)->concat($likes)
            ->sortByDesc('created_at')
            ->take(10);

        // Quiz submissions
        $quizSubmissions = QuizSubmission::where('user_id', $user->id)
            ->with(['quiz.group'])
            ->latest()
            ->get();

        // Warning logs
        $warningLogs = BlacklistLog::where('user_id', $user->id)
            ->latest()
            ->get();

        // ML Affinity & Recommendations
        $affinityCalculator = app(\App\Services\AffinityCalculator::class);
        $affinityScores = $affinityCalculator->getAffinity($user->id);
        $recommendations = $affinityCalculator->getRecommendations($user->id, 5);

        // Interaction counts
        $interactionCounts = \App\Models\UserInteraction::where('user_id', $user->id)
            ->select('action_type', \DB::raw('count(*) as count'))
            ->groupBy('action_type')
            ->pluck('count', 'action_type')
            ->toArray();

        // Ensure all keys exist
        $interactionCounts = array_merge([
            'views' => 0,
            'likes' => 0,
            'comments' => 0,
            'downloads' => 0,
        ], $interactionCounts);

        return view('student.profile', compact(
            'totalTopics',
            'totalPosts',
            'totalLikes',
            'totalQuizzesTaken',
            'recentActivity',
            'quizSubmissions',
            'warningLogs',
            'affinityScores',
            'recommendations',
            'interactionCounts'
        ));
    }
    /**
     * List All Groups (Index) - Shows ALL groups with membership status
     */
    public function groups()
    {
        $user = Auth::user();
        
        // Get IDs of groups the user is already in
        $userGroupIds = $user->groups()->pluck('groups.id')->toArray();
        
        // Fetch ALL groups with counts
        $groups = Group::withCount(['topics', 'users'])
            ->orderBy('name')
            ->get();
        
        // Add a flag to each group indicating if the user is a member
        $groups->each(function ($group) use ($userGroupIds) {
            $group->isMember = in_array($group->id, $userGroupIds);
        });
        
        return view('groups.index', compact('groups'));
    }

    /**
     * Show Group Guidelines (Rules Gate)
     */
    public function guidelines($groupId)
    {
        $user = Auth::user();
        $group = Group::withCount(['topics', 'users'])->findOrFail($groupId);

        // Check if user is a member of this group
        $isMember = $user->groups()->where('group_id', $groupId)->exists();
        if (!$isMember) {
            return redirect()->route('groups.index')
                ->with('error', 'You must join the group first before viewing the guidelines.');
        }

        // Check if user already agreed
        $hasAgreed = $user->groups()
            ->where('group_id', $groupId)
            ->wherePivot('has_agreed_rules', true)
            ->exists();

        if ($hasAgreed) {
            return redirect()->route('groups.topics', $groupId);
        }

        return view('groups.guidelines', compact('group'));
    }

    /**
     * Accept Group Rules
     */
    public function agreeRules($groupId)
    {
        $user = Auth::user();
        $group = Group::findOrFail($groupId);

        // Ensure the user is a member (pivot record exists)
        if (!$user->groups()->where('group_id', $groupId)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not a member of this group.'
            ], 403);
        }

        // Update pivot table
        $user->groups()->updateExistingPivot($groupId, [
            'has_agreed_rules' => true
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Rules accepted successfully.'
        ]);
    }

    /**
     * Decline Group Rules
     */
    public function declineRules($groupId)
    {
        $user = Auth::user();
        $group = Group::findOrFail($groupId);
        
        // Remove user from group (detach)
        $user->groups()->detach($groupId);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Group access declined.'
        ]);
    }

    /**
     * Join a Group
     */
    public function joinGroup($groupId)
    {
        $user = Auth::user();
        $group = Group::findOrFail($groupId);
        
        // Check if already a member
        if ($user->groups()->where('group_id', $groupId)->exists()) {
            return redirect()->route('groups.topics', $groupId)
                ->with('info', 'You are already a member of this group.');
        }
        
        // Add user to group with has_agreed_rules = false
        $user->groups()->attach($groupId, ['has_agreed_rules' => false]);
        
        // Redirect to guidelines to accept rules
        return redirect()->route('groups.guidelines', $groupId)
            ->with('success', 'You have joined the group. Please review and accept the guidelines.');
    }

    /**
     * List Topics in a Group
     */
    public function topics($groupId)
    {
        $user = Auth::user();
        $group = Group::withCount('topics')->findOrFail($groupId);
        
        // Check if user has agreed to rules
        $hasAgreed = $user->groups()
            ->where('group_id', $groupId)
            ->wherePivot('has_agreed_rules', true)
            ->exists();
        
        if (!$hasAgreed) {
            return redirect()->route('groups.guidelines', $groupId);
        }
        
        $topics = Topic::where('group_id', $groupId)
            ->with(['creator', 'posts'])
            ->withCount('posts')
            ->latest()
            ->get();
        
        return view('groups.topics', compact('group', 'topics'));
    }

    /**
     * Show create topic form
     */
    public function createTopic()
    {
        // Get groups the user belongs to (for dropdown)
        $groups = Auth::user()->groups()->get();
        
        return view('topics.create', compact('groups'));
    }

    /**
     * Store a new topic
     */
    public function storeTopic(Request $request)
    {
        $validated = $request->validate([
            'group_id' => 'required|exists:groups,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $groupId = $validated['group_id'];

        // ML Text Classifier
        $mlClassifier = app(\App\Services\MLTextClassifier::class);
        $mlCategory = $mlClassifier->classify(
            $validated['title'],
            $validated['description'] ?? '',
            $groupId
        );

        // ✅ Create the topic
        $topic = Topic::create([
            'group_id' => $groupId,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'creator_id' => Auth::id(),
            'ml_category' => $mlCategory,
        ]);

        // ✅ Check if topic was created successfully
        if (!$topic) {
            return redirect()->back()
                ->with('error', 'Failed to create topic. Please try again.')
                ->withInput();
        }

        Auth::user()->update(['last_communicated_at' => now()]);

        return redirect()->route('groups.topics', $groupId)
            ->with('success', "Topic created successfully! Category: {$mlCategory}");
    }

    /**
     * Show a single topic with its posts
     */
    public function showTopic($groupId, $topicId)
    {
        // Topic must belong to the group
        $topic = Topic::where('group_id', $groupId)
            ->with(['creator', 'group'])
            ->findOrFail($topicId);
        
        $posts = Post::where('topic_id', $topicId)
            ->whereNull('parent_id')
            ->with([
                'author',
                'children' => function($query) {
                    $query->with(['author', 'children.author'])->orderBy('created_at', 'asc');
                },
                'likes'
            ])
            ->withCount('likes')
            ->orderBy('created_at', 'asc')
            ->get();
        
        return view('topics.show', compact('topic', 'posts'));
    }

    /**
     * Store a new post (AJAX)
     */
    public function storePost(Request $request)
    {
        $validated = $request->validate([
            'topic_id' => 'required|exists:topics,id',
            'content' => 'required|string|min:3',
            'is_private' => 'boolean',
            'excluded_user_ids' => 'nullable|array',
        ]);

        $post = Post::create([
            'topic_id' => $validated['topic_id'],
            'user_id' => Auth::id(),
            'content' => $validated['content'],
            'is_private' => $validated['is_private'] ?? false,
        ]);

        if ($post->is_private && !empty($validated['excluded_user_ids'])) {
            $post->excludedUsers()->attach($validated['excluded_user_ids']);
        }

        Auth::user()->update(['last_communicated_at' => now()]);

        $post->load('author');

        return response()->json([
            'success' => true,
            'message' => 'Post added successfully.',
            'post' => [
                'id' => $post->id,
                'content' => $post->content,
                'created_at' => $post->created_at->diffForHumans(),
                'is_private' => $post->is_private,
                'author' => [
                    'name' => $post->author->name ?? 'Unknown',
                    'id' => $post->author->id ?? null,
                ],
                'likes_count' => 0,
                'is_liked' => false,
            ]
        ]);
    }

    /**
     * Server-Sent Events stream for topic updates
     */
/*     public function sseStream($topicId)
    {
        $response = response()->stream(function () use ($topicId) {
            // Set headers for SSE
            header('Content-Type: text/event-stream');
            header('Cache-Control: no-cache');
            header('Connection: keep-alive');
            header('X-Accel-Buffering: no');

            $lastCheck = now()->subSeconds(5);
            $lastPostId = 0;

            while (true) {
                // Check for new posts
                $newPosts = Post::where('topic_id', $topicId)
                    ->where('created_at', '>', $lastCheck)
                    ->where('id', '>', $lastPostId)
                    ->with('author')
                    ->orderBy('id', 'asc')
                    ->get();

                if ($newPosts->isNotEmpty()) {
                    foreach ($newPosts as $post) {
                        // Skip if post is by current user
                        if ($post->user_id == Auth::id()) {
                            continue;
                        }
                        
                        $data = [
                            'id' => $post->id,
                            'content' => $post->content,
                            'author' => $post->author->name ?? 'Unknown',
                            'author_id' => $post->user_id,
                            'created_at' => $post->created_at->diffForHumans(),
                            'total' => Post::where('topic_id', $topicId)->count(),
                        ];

                        echo "event: new_post\n";
                        echo "data: " . json_encode($data) . "\n\n";
                        ob_flush();
                        flush();

                        $lastPostId = $post->id;
                    }
                }

                $lastCheck = now();
                
                // Keep connection alive
                echo ": heartbeat\n\n";
                ob_flush();
                flush();

                // Sleep for 2 seconds
                sleep(2);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);

        return $response;
    } */


    /**
     * Long polling - waits for new posts
     */
    public function longPoll($topicId)
    {
        // Get user ID before releasing session
        $userId = Auth::id();
        
        // RELEASE SESSION LOCK - This fixes the posting delay!
        session_write_close();

        $lastPostId = request('last_post_id', 0);
        $timeout = 20; // seconds to wait
        $start = time();

        while (time() - $start < $timeout) {
            // Check for new posts (excluding current user's own posts)
            $post = Post::where('topic_id', $topicId)
                ->where('id', '>', $lastPostId)
                ->where('user_id', '!=', $userId)
                ->with('author')
                ->orderBy('id', 'asc')
                ->first();

            if ($post) {
                // New post found! Return it immediately
                return response()->json([
                    'has_updates' => true,
                    'post' => [
                        'id' => $post->id,
                        'content' => $post->content,
                        'author' => $post->author->name ?? 'Unknown',
                        'author_id' => $post->user_id,
                        'created_at' => $post->created_at->diffForHumans(),
                    ],
                    'total' => Post::where('topic_id', $topicId)->count(),
                ]);
            }

            // Sleep for 1 second before checking again
            sleep(1);
        }

        // Timeout - no new posts
        return response()->json([
            'has_updates' => false,
        ]);
    }

    /**
     * Store a reply to a specific post (threaded reply) - AJAX
     */
    public function storeReply(Request $request)
    {
        $validated = $request->validate([
            'topic_id' => 'required|exists:topics,id',
            'parent_id' => 'required|exists:posts,id',
            'content' => 'required|string|min:3',
            'is_private' => 'boolean',
        ]);

        $post = Post::create([
            'topic_id' => $validated['topic_id'],
            'parent_id' => $validated['parent_id'],
            'user_id' => Auth::id(),
            'content' => $validated['content'],
            'is_private' => $validated['is_private'] ?? false,
        ]);

        Auth::user()->update(['last_communicated_at' => now()]);

        // Load the author relationship
        $post->load('author');

        return response()->json([
            'success' => true,
            'message' => 'Reply posted successfully.',
            'post' => [
                'id' => $post->id,
                'content' => $post->content,
                'created_at' => $post->created_at->diffForHumans(),
                'is_private' => $post->is_private,
                'author' => [
                    'name' => $post->author->name ?? 'Unknown',
                    'id' => $post->author->id ?? null,
                ],
                'likes_count' => 0,
                'is_liked' => false,
                'children' => [],
            ]
        ]);
    }

    /**
     * Toggle like on a post (AJAX)
     */
    public function toggleLike($postId)
    {
        $user = Auth::user();
        $post = Post::findOrFail($postId);

        $existingLike = PostLike::where('post_id', $postId)
                                ->where('user_id', $user->id)
                                ->first();

        if ($existingLike) {
            $existingLike->delete();
            $liked = false;
        } else {
            PostLike::create([
                'post_id' => $postId,
                'user_id' => $user->id,
            ]);
            $liked = true;
        }

        $likeCount = PostLike::where('post_id', $postId)->count();

        return response()->json([
            'success' => true,
            'liked' => $liked,
            'count' => $likeCount,
            'message' => $liked ? 'Post liked!' : 'Like removed.'
        ]);
    }

    /**
     * Show available quizzes for the student
     */
    public function quizIndex()
    {
        $user = Auth::user();
        
        // Get group IDs the user is in
        $groupIds = $user->groups()->pluck('groups.id')->toArray();
        
        // Get quizzes that are active AND (not yet started OR currently active)
        $quizzes = Quiz::whereIn('group_id', $groupIds)
            ->where('is_active', true)
            ->where('ends_at', '>', now()) // Must not have ended
            ->where(function($query) {
                $query->where('starts_at', '<=', now())   // Already started
                    ->orWhere('starts_at', '>', now()); // Or upcoming
            })
            ->with(['group', 'submissions' => function($query) use ($user) {
                $query->where('user_id', $user->id);
            }])
            ->orderBy('starts_at', 'asc')
            ->get();
        
        // Add flag for taken quizzes
        $quizzes->each(function ($quiz) use ($user) {
            $quiz->has_taken = $quiz->submissions->isNotEmpty();
        });
        
        return view('student.quizzes', compact('quizzes'));
    }

    /**
     * Take a quiz (attempt page)
     */
    public function takeQuiz($quizId)
    {
        $user = Auth::user();
        $quiz = Quiz::with(['group'])->findOrFail($quizId);
        
        // Check if quiz is available
        if ($quiz->starts_at > now()) {
            return redirect()->route('student.quizzes')
                ->with('error', 'This quiz has not started yet.');
        }
        
        if ($quiz->ends_at < now()) {
            return redirect()->route('student.quizzes')
                ->with('error', 'This quiz has already ended.');
        }
        
        // Check if user already submitted
        $existing = QuizSubmission::where('quiz_id', $quizId)
            ->where('user_id', $user->id)
            ->first();
        
        if ($existing) {
            return redirect()->route('student.quizzes')
                ->with('info', 'You have already taken this quiz.');
        }
        
        // Check if user's status is allowed
        $allowedCategories = $quiz->allowed_categories ?? ['active'];
        if (!in_array($user->status, $allowedCategories)) {
            return redirect()->route('student.quizzes')
                ->with('error', 'You are not eligible to take this quiz.');
        }
        
        // Calculate remaining time
        $remainingSeconds = now()->diffInSeconds($quiz->ends_at, false);
        if ($remainingSeconds <= 0) {
            return redirect()->route('student.quizzes')
                ->with('error', 'This quiz has ended.');
        }
        
        return view('student.quiz-take', compact('quiz', 'remainingSeconds'));
    }

    /**
     * Submit quiz (AJAX)
     */
    public function submitQuiz(Request $request, $quizId)
    {
        $user = Auth::user();
        $quiz = Quiz::findOrFail($quizId);

        $validated = $request->validate([
            'answers' => 'required|array',
            'time_spent' => 'nullable|integer',
            'auto_submitted' => 'nullable|boolean',
        ]);

        // Check if already submitted
        $existing = QuizSubmission::where('quiz_id', $quizId)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'You have already submitted this quiz.'
            ], 400);
        }

        // Auto-mark answers
        $totalPoints = 0;
        $earnedPoints = 0;
        $answerDetails = [];

        foreach ($quiz->questions as $question) {
            $totalPoints += $question->points;
            $userAnswer = $validated['answers']['q' . $question->id] ?? '';

            $isCorrect = false;
            $pointsEarned = 0;

            if ($question->type === 'text') {
                $correctAnswers = $question->correct_answers ?? [];
                $isCorrect = in_array(strtolower(trim($userAnswer)), array_map('strtolower', $correctAnswers));
                $pointsEarned = $isCorrect ? $question->points : 0;
            } else {
                $correctAnswers = $question->correct_answers ?? [];
                $userAnswers = is_array($userAnswer) ? $userAnswer : [$userAnswer];

                if ($question->type === 'single') {
                    $isCorrect = in_array($userAnswers[0] ?? '', $correctAnswers);
                    $pointsEarned = $isCorrect ? $question->points : 0;
                } else {
                    $correctSet = array_map('strval', $correctAnswers);
                    $userSet = array_map('strval', $userAnswers);
                    sort($correctSet);
                    sort($userSet);
                    $isCorrect = $correctSet === $userSet;
                    $pointsEarned = $isCorrect ? $question->points : 0;
                }
            }

            $earnedPoints += $pointsEarned;

            $answerDetails[] = [
                'question_id' => $question->id,
                'answer' => is_array($userAnswer) ? json_encode($userAnswer) : $userAnswer,
                'is_correct' => $isCorrect,
                'points_earned' => $pointsEarned,
            ];
        }

        $score = $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 100, 2) : 0;

        // Create submission
        $submission = QuizSubmission::create([
            'quiz_id' => $quizId,
            'user_id' => $user->id,
            'score' => $score,
            'answers_payload' => json_encode($validated['answers']),
            'is_auto_submitted' => $request->input('auto_submitted', false),
        ]);

        // Save individual answers (if you have the QuizAnswer model)
        // foreach ($answerDetails as $detail) {
        //     QuizAnswer::create([
        //         'submission_id' => $submission->id,
        //         'question_id' => $detail['question_id'],
        //         'answer' => $detail['answer'],
        //         'is_correct' => $detail['is_correct'],
        //         'points_earned' => $detail['points_earned'],
        //     ]);
        // }

        return response()->json([
            'success' => true,
            'message' => 'Quiz submitted successfully!',
            'score' => $score,
            'total_questions' => $quiz->questions->count(),
            'earned_points' => $earnedPoints,
            'total_points' => $totalPoints,
        ]);
    }

    /**
     * Export topic to PDF (Placeholder - will implement with DomPDF later)
     */
    public function exportPdf($topicId)
    {
        $topic = Topic::with(['group', 'creator'])->findOrFail($topicId);

        // ✅ Fetch posts with nested children (threaded replies)
        $posts = Post::where('topic_id', $topicId)
            ->whereNull('parent_id')
            ->with(['author', 'children.author', 'children.children.author'])
            ->orderBy('created_at', 'asc')
            ->get();

        $data = [
            'topic' => $topic,
            'posts' => $posts,
            'group' => $topic->group,
            'author' => $topic->creator,
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.topic', $data);
        $pdf->setPaper('A4', 'portrait');

        $filename = Str::slug($topic->title) . '_history.pdf';
        return $pdf->download($filename);
    }
    /**
     * Show performance report for a quiz
     */
    public function performanceReport($quizId)
    {
        $user = Auth::user();
        $quiz = Quiz::with(['questions', 'group'])->findOrFail($quizId);

        // Check if quiz has ended
        if (!$quiz->hasEnded()) {
            return redirect()->route('student.quizzes')
                ->with('error', 'Results are locked until the evaluation session finishes.');
        }

        // Get the user's submission
        $submission = QuizSubmission::where('quiz_id', $quizId)
            ->where('user_id', $user->id)
            ->with(['answers'])
            ->first();

        if (!$submission) {
            return redirect()->route('student.quizzes')
                ->with('info', 'You have not taken this quiz.');
        }

        // Get all submissions for this quiz (for comparison)
        $allSubmissions = QuizSubmission::where('quiz_id', $quizId)
            ->with('user')
            ->get();

        // Calculate statistics
        $scores = $allSubmissions->pluck('score')->filter();
        $averageScore = $scores->avg() ?? 0;
        $highestScore = $scores->max() ?? 0;
        $lowestScore = $scores->min() ?? 0;
        $passCount = $scores->filter(fn($s) => $s >= 50)->count();
        $passRate = $allSubmissions->count() > 0 ? round(($passCount / $allSubmissions->count()) * 100, 1) : 0;

        // Get user's rank
        $rank = $scores->sortDesc()->search($submission->score) + 1;
        $totalStudents = $scores->count();

        // Prepare answer breakdown
        $questionDetails = [];
        foreach ($quiz->questions as $index => $question) {
            $userAnswer = $submission->answers->where('question_id', $question->id)->first();
            $isCorrect = $userAnswer ? $userAnswer->is_correct : false;
            $pointsEarned = $userAnswer ? $userAnswer->points_earned : 0;

            $questionDetails[] = [
                'number' => $index + 1,
                'question' => $question->question,
                'type' => $question->type,
                'user_answer' => $userAnswer ? $userAnswer->answer : 'Not answered',
                'correct_answer' => is_array($question->correct_answers) 
                    ? implode(', ', $question->correct_answers) 
                    : $question->correct_answers,
                'is_correct' => $isCorrect,
                'points' => $question->points,
                'points_earned' => $pointsEarned,
                'options' => $question->options ?? [],
            ];
        }

        return view('student.performance-report', compact(
            'quiz',
            'submission',
            'allSubmissions',
            'averageScore',
            'highestScore',
            'lowestScore',
            'passRate',
            'rank',
            'totalStudents',
            'questionDetails'
        ));
    }
}