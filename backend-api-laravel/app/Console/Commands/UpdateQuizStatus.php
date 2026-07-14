<?php

namespace App\Console\Commands;

use App\Models\Quiz;
use Illuminate\Console\Command;

class UpdateQuizStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'quiz:update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update quiz statuses based on start and end times';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating quiz statuses...');

        // Count quizzes before update
        $activeBefore = Quiz::where('is_active', true)->count();
        $this->line("Active quizzes before: {$activeBefore}");

        // Deactivate expired quizzes (ended)
        $expired = Quiz::where('ends_at', '<', now())
            ->where('is_active', true)
            ->update(['is_active' => false]);

        $this->info("{$expired} expired quiz(zes) deactivated.");

        // (Optional) Deactivate quizzes that haven't started yet but are marked active
        // This prevents accidental early activation
        $notStarted = Quiz::where('starts_at', '>', now())
            ->where('is_active', true)
            ->update(['is_active' => false]);

        if ($notStarted > 0) {
            $this->info("{$notStarted} upcoming quiz(zes) deactivated (not started yet).");
        }

        // (Optional) Reactivate quizzes that are within time window
        // Usually we don't auto-reactivate to prevent accidental activations
        // You could uncomment this if you want auto-reactivation:
        /*
        $withinWindow = Quiz::where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->where('is_active', false)
            ->update(['is_active' => true]);

        if ($withinWindow > 0) {
            $this->info("{$withinWindow} quiz(zes) reactivated (within active window).");
        }
        */

        $activeAfter = Quiz::where('is_active', true)->count();
        $this->line("Active quizzes after: {$activeAfter}");

        $this->info(' Quiz status update completed.');
        return Command::SUCCESS;
    }
}