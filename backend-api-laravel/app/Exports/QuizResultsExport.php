<?php

namespace App\Exports;

use App\Models\QuizSubmission;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class QuizResultsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $quizId;

    public function __construct($quizId)
    {
        $this->quizId = $quizId;
    }

    public function collection()
    {
        return QuizSubmission::where('quiz_id', $this->quizId)
            ->with('user')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Student Name',
            'Student Email',
            'Score',
            'Auto-Submitted',
            'Submitted At',
        ];
    }

    public function map($submission): array
    {
        return [
            $submission->user->name ?? 'Unknown',
            $submission->user->email ?? 'Unknown',
            $submission->score ?? 0 . '%',
            $submission->is_auto_submitted ? 'Yes' : 'No',
            $submission->created_at->format('M d, Y h:i A'),
        ];
    }
}