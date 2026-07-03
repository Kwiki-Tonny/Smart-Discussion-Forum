<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\InactivityWarningOne;
use App\Notifications\InactivityWarningTwo;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class MonitorInactiveUsers extends Command
{
    protected $signature = 'moderation:monitor';

    protected $description = 'Checks user inactivity and applies the 2-warning blacklist state machine';

    public function handle(): int
    {
        $this->info('Running inactivity monitor: ' . now());

        $users = User::whereIn('status', ['active', 'warned_once', 'warned_twice'])
            ->whereNotNull('last_communicated_at')
            ->get();

        foreach ($users as $user) {
            $daysInactive = Carbon::parse($user->last_communicated_at)->diffInDays(now());

            // Rule 3: 21+ days inactive and already on second warning -> blacklist
            if ($daysInactive >= 21 && $user->status === 'warned_twice') {
                $this->blacklistUser($user);
                continue;
            }

            // Rule 2: 14+ days inactive and already on first warning -> second warning
            if ($daysInactive >= 14 && $user->status === 'warned_once') {
                $user->status = 'warned_twice';
                $user->save();
                $user->notify(new InactivityWarningTwo());
                Log::info("User {$user->id} moved to warned_twice ({$daysInactive} days inactive).");
                $this->warn("User #{$user->id} ({$user->email}) -> warned_twice");
                continue;
            }

            // Rule 1: 7+ days inactive and still active -> first warning
            if ($daysInactive >= 7 && $user->status === 'active') {
                $user->status = 'warned_once';
                $user->save();
                $user->notify(new InactivityWarningOne());
                Log::info("User {$user->id} moved to warned_once ({$daysInactive} days inactive).");
                $this->warn("User #{$user->id} ({$user->email}) -> warned_once");
            }
        }

        $this->info('Inactivity monitor completed.');

        return self::SUCCESS;
    }

    protected function blacklistUser(User $user): void
    {
        $user->status = 'blacklisted';
        $user->blacklist_expires_at = now()->addDays(14);
        $user->save();

        // Revoke all Sanctum tokens to force logout
        if (method_exists($user, 'tokens')) {
            $user->tokens()->delete();
        }

        Log::warning("User {$user->id} ({$user->email}) BLACKLISTED until {$user->blacklist_expires_at}.");
        $this->error("User #{$user->id} ({$user->email}) -> BLACKLISTED until {$user->blacklist_expires_at}");
    }
}