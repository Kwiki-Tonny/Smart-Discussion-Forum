<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Facades\DB;

class StudentPerformanceExport implements FromCollection, WithHeadings, WithMapping
{
    protected $groupIds;

    public function __construct($groupIds)
    {
        $this->groupIds = $groupIds;
    }

    public function collection()
    {
        return User::where('role', 'student')
            ->whereHas('groups', function($query) {
                $query->whereIn('group_id', $this->groupIds);
            })
            ->withCount(['topics', 'posts'])
            ->get();
    }

    public function headings(): array
    {
        return [
            'Name',
            'Email',
            'Topics Created',
            'Posts Made',
            'Participation Score',
            'Quizzes Taken',
            'Average Quiz Score',
        ];
    }

    public function map($student): array
    {
        $quizSubmissions = \App\Models\QuizSubmission::where('user_id', $student->id)->get();
        $avgQuizScore = $quizSubmissions->avg('score') ?? 0;

        $participationScore = min(100,
            ($student->topics_count * 5) + ($student->posts_count * 2)
        );

        return [
            $student->name,
            $student->email,
            $student->topics_count,
            $student->posts_count,
            $participationScore . '%',
            $quizSubmissions->count(),
            round($avgQuizScore, 2) . '%',
        ];
    }
}