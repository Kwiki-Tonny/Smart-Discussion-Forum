<?php

namespace App\Services;

use App\Models\CategoryTerm;
use App\Models\Topic;
use Illuminate\Support\Facades\Cache;

class MLTextClassifier
{
    protected $categories = [
        'Database Systems' => [
            'sql', 'mysql', 'database', 'query', 'join', 'index',
            'normalization', 'transaction', 'acid', 'foreign key'
        ],
        'Java Programming' => [
            'java', 'class', 'object', 'inheritance', 'polymorphism',
            'jvm', 'jdk', 'spring', 'hibernate', 'maven'
        ],
        'Web Development' => [
            'html', 'css', 'javascript', 'react', 'vue', 'angular',
            'php', 'laravel', 'nodejs', 'api', 'rest', 'ajax'
        ],
        'Network Security' => [
            'firewall', 'encryption', 'ssl', 'tls', 'vpn', 'router',
            'packet', 'cisco', 'ip', 'tcp', 'udp', 'dns'
        ],
        'Algorithms & Data Structures' => [
            'algorithm', 'sort', 'search', 'recursion', 'complexity',
            'binary', 'tree', 'graph', 'hash', 'heap', 'queue', 'stack'
        ],
        'Operating Systems' => [
            'linux', 'windows', 'process', 'thread', 'scheduler',
            'memory', 'file system', 'kernel', 'shell', 'bash'
        ],
        'Software Engineering' => [
            'agile', 'scrum', 'testing', 'unit test', 'debug',
            'refactoring', 'design patterns', 'solid', 'tdd'
        ],
        'General Discussion' => [],
    ];

    public function classify($title, $body, $groupId): string
    {
        $text = strtolower($title . ' ' . $body);
        $text = preg_replace('/[^a-z0-9 ]/', ' ', $text);
        $words = array_count_values(array_filter(explode(' ', $text)));

        $scores = [];

        foreach ($this->categories as $category => $keywords) {
            $score = 0;
            if (empty($keywords)) continue;

            foreach ($keywords as $keyword) {
                if (isset($words[$keyword])) {
                    $importance = $this->getGlobalImportance($keyword, $groupId);
                    $score += ($words[$keyword] * $importance);
                }
            }
            $scores[$category] = $score;
        }

        if (empty($scores) || max($scores) === 0) {
            return 'General Discussion';
        }

        arsort($scores);
        $topCategory = key($scores);
        $this->updateTermFrequencies($words, $groupId, $topCategory);

        return $topCategory;
    }

    protected function getGlobalImportance($term, $groupId): float
    {
        $cacheKey = "term_importance_{$groupId}_{$term}";

        return Cache::remember($cacheKey, 3600, function () use ($term, $groupId) {
            $totalTopics = Topic::where('group_id', $groupId)->count();

            if ($totalTopics === 0) return 1.0;

            $termCount = CategoryTerm::where('group_id', $groupId)
                ->where('term', $term)
                ->sum('frequency');

            $importance = 1 + log($totalTopics / max(1, $termCount));
            return round($importance, 2);
        });
    }

    protected function updateTermFrequencies($words, $groupId, $category): void
    {
        foreach ($words as $term => $frequency) {
            if (strlen($term) < 3) continue;

            CategoryTerm::updateOrCreate(
                ['term' => $term, 'group_id' => $groupId],
                [
                    'category'  => $category,
                    'frequency' => \DB::raw('frequency + ' . $frequency),
                ]
            );
        }
    }

    public static function recalculateImportance($groupId): void
    {
        $totalTopics = Topic::where('group_id', $groupId)->count();
        if ($totalTopics === 0) return;

        $terms = CategoryTerm::where('group_id', $groupId)->get();
        foreach ($terms as $term) {
            $cacheKey = "term_importance_{$groupId}_{$term->term}";
            $importance = 1 + log($totalTopics / max(1, $term->frequency));
            Cache::put($cacheKey, round($importance, 2), 3600);
        }
    }
}