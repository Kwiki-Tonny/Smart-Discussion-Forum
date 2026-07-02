<?php

namespace App\Services;

use App\Models\Topic;
use App\Models\UserInteraction;
use Illuminate\Support\Facades\Cache;

class AffinityCalculator
{
    protected $weights = [
        'view'     => 1,
        'like'     => 2,
        'download' => 3,
        'comment'  => 5,
    ];

    public function getAffinity($userId): array
    {
        $cacheKey = "affinity_user_{$userId}";

        return Cache::remember($cacheKey, 1800, function () use ($userId) {
            $interactions = UserInteraction::where('user_id', $userId)
                ->join('topics', 'user_interactions.topic_id', '=', 'topics.id')
                ->select('topics.ml_category', 'user_interactions.action_type')
                ->get();

            if ($interactions->isEmpty()) {
                return ['General Discussion' => 100];
            }

            $scores = [];
            foreach ($interactions as $interaction) {
                $category = $interaction->ml_category ?? 'General Discussion';
                $weight   = $this->weights[$interaction->action_type] ?? 1;
                $scores[$category] = ($scores[$category] ?? 0) + $weight;
            }

            $total      = array_sum($scores);
            $normalized = [];
            foreach ($scores as $category => $score) {
                $normalized[$category] = round(($score / $total) * 100, 2);
            }

            arsort($normalized);
            return $normalized;
        });
    }

    public function getRecommendations($userId, $limit = 5)
    {
        $affinity       = $this->getAffinity($userId);
        $topCategories  = array_keys(array_slice($affinity, 0, 3));

        $interactedTopicIds = UserInteraction::where('user_id', $userId)
            ->pluck('topic_id')
            ->toArray();

        return Topic::whereIn('ml_category', $topCategories)
            ->whereNotIn('id', $interactedTopicIds)
            ->with('group', 'creator')
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function clearCache($userId): void
    {
        Cache::forget("affinity_user_{$userId}");
    }
}