<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\BlacklistLog;
use App\Notifications\InactivityWarningOne;
use App\Notifications\InactivityWarningTwo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class ModerationMonitor extends Command
{
    protected $signature = 'moderation:monitor';
    protected $description = 'Monitor user inactivity and enforce warnings/blacklisting';

    public function handle()
    {
        $this->info('Running inactivity monitor...');

        $users = User::where('status', '!=', 'blacklisted')->get();

        foreach ($users as $user) {
            $daysInactive = now()->diffInDays($user->last_communicated_at ?? $user->created_at);

            $this->line("User: {$user->email} | Status: {$user->status} | Days inactive: {$daysInactive}");

            // State machine logic
            if ($daysInactive >= 21 && $user->status === 'warned_twice') {
                // Blacklist
                $user->status = 'blacklisted';
                $user->blacklist_expires_at = now()->addDays(14);
                $user->save();

                BlacklistLog::create([
                    'user_id' => $user->id,
                    'reason' => 'Inactivity breach: 21+ days without communication',
                    'action_type' => 'hard_blacklist',
                    'expires_at' => $user->blacklist_expires_at,
                ]);

                $this->warn("User {$user->email} has been BLACKLISTED.");

                // 🔄 INTEGRATED: Send notification
                $user->notify(new \App\Notifications\InactivityWarningTwo());

            } elseif ($daysInactive >= 14 && $user->status === 'warned_once') {
                // Second warning
                $user->status = 'warned_twice';
                $user->save();

                BlacklistLog::create([
                    'user_id' => $user->id,
                    'reason' => 'Inactivity breach: 14+ days without communication',
                    'action_type' => 'issue_warning_2',
                ]);

                $this->info("User {$user->email} is now WARNED_TWICE.");

                // 🔄 INTEGRATED: Send notification
                $user->notify(new \App\Notifications\InactivityWarningTwo());

            } elseif ($daysInactive >= 7 && $user->status === 'active') {
                // First warning
                $user->status = 'warned_once';
                $user->save();

                BlacklistLog::create([
                    'user_id' => $user->id,
                    'reason' => 'Inactivity breach: 7+ days without communication',
                    'action_type' => 'issue_warning_1',
                ]);

                $this->info("User {$user->email} is now WARNED_ONCE.");

                // 🔄 INTEGRATED: Send notification
                $user->notify(new \App\Notifications\InactivityWarningOne());
            }
        }

        $this->info('Inactivity monitor completed.');
        return Command::SUCCESS;
    }
}