<?php

/**
 * Namespace: App\Models
 * 
 * This namespace contains the core Eloquent ORM models for the Laravel application.
 * Classes in this namespace represent the business entities and database tables,
 * encapsulating data access logic, relationships, mutators, accessors, and 
 * mass-assignment protections.
 */
namespace App\Models;

/**
 * Base Eloquent Model class providing the foundation for database interactions,
 * including query building, relationship management, and event dispatching.
 */
use Illuminate\Database\Eloquent\Model;

/**
 * Blacklist Log Model
 * 
 * Represents an audit and moderation record for users who have been restricted, 
 * suspended, or banned within the application. This model serves as a critical 
 * component of the platform's security and moderation infrastructure.
 * 
 * Key Architectural Roles:
 * 1. Audit Trail: Provides a historical, immutable(ish) record of moderation 
 *    actions taken against a user, including the specific reason and the type 
 *    of restriction applied.
 * 2. Automated Expiration: Utilizes Laravel's date casting to seamlessly 
 *    integrate with Carbon instances, allowing the application to easily query 
 *    for and automatically lift temporary restrictions when the `expires_at` 
 *    timestamp is in the past.
 * 3. Security Boundary: Strictly defines mass-assignable attributes via the 
 *    `$fillable` property to prevent Mass Assignment Vulnerabilities (e.g., 
 *    preventing a malicious user from forging a request to alter their own 
 *    blacklist status or expiration date).
 * 
 * @property int $id The unique primary key for the blacklist log entry.
 * @property int $user_id The foreign key referencing the restricted user.
 * @property string $reason The detailed justification or context for the blacklist action.
 * @property string $action_type The category of restriction (e.g., 'suspend', 'ban', 'mute').
 * @property \Illuminate\Support\Carbon|null $expires_at The timestamp when the restriction automatically lifts.
 * @property \Illuminate\Support\Carbon $created_at Timestamp of record creation.
 * @property \Illuminate\Support\Carbon $updated_at Timestamp of last record modification.
 */
class BlacklistLog extends Model
{
    /**
     * The attributes that are mass assignable.
     * 
     * Security Note: By explicitly defining `$fillable`, we adopt a "whitelist" 
     * approach to mass assignment. This ensures that when creating or updating 
     * a BlacklistLog model using methods like `create()` or `update()`, only 
     * these specific, safe attributes can be populated from an incoming request 
     * array. This prevents attackers from injecting unauthorized fields (such 
     * as `id` or `created_at`) into the database.
     * 
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'reason',
        'action_type',
        'expires_at',
    ];
    
    /**
     * The attributes that should be cast to native PHP types.
     * 
     * Architectural Note: Casting `expires_at` to 'datetime' ensures that whenever 
     * this attribute is accessed, Laravel automatically hydrates it as a Carbon 
     * instance. This provides immediate access to powerful date manipulation and 
     * comparison methods (e.g., `$log->expires_at->isPast()`, `$log->expires_at->diffForHumans()`) 
     * without requiring manual parsing or instantiation in the application logic.
     * 
     * @var array<string, string>
     */
    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * Get the user that owns this blacklist log entry.
     * 
     * Defines an inverse One-to-Many (BelongsTo) relationship. This allows the 
     * application to easily eager-load the associated user's details (e.g., 
     * `BlacklistLog::with('user')->get()`) to display moderator dashboards or 
     * to check a user's current restriction status without writing manual SQL joins.
     * 
     * By default, Eloquent will use `user_id` as the foreign key and `id` as 
     * the owner key on the `users` table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}