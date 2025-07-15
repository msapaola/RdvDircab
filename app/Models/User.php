<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the options for logging activity.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name',
                'email',
                'role',
                'is_active'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('user');
    }

    // Rôles disponibles
    const ROLE_ADMIN = 'admin';
    const ROLE_ASSISTANT = 'assistant';

    /**
     * Get appointments processed by this user
     */
    public function processedAppointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'processed_by');
    }

    /**
     * Get blocked slots created by this user
     */
    public function blockedSlots(): HasMany
    {
        return $this->hasMany(BlockedSlot::class, 'blocked_by');
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Check if user is assistant
     */
    public function isAssistant(): bool
    {
        return $this->role === self::ROLE_ASSISTANT;
    }

    /**
     * Check if user has specific role
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if user has any of the given roles
     */
    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles);
    }

    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Get formatted role for display
     */
    public function getFormattedRoleAttribute(): string
    {
        $roles = [
            self::ROLE_ADMIN => 'Administrateur',
            self::ROLE_ASSISTANT => 'Assistant',
        ];

        return $roles[$this->role] ?? $this->role;
    }

    /**
     * Get user's statistics
     */
    public function getStatisticsAttribute(): array
    {
        $appointments = $this->processedAppointments();
        
        return [
            'total_processed' => $appointments->count(),
            'pending' => $appointments->where('status', Appointment::STATUS_PENDING)->count(),
            'accepted' => $appointments->where('status', Appointment::STATUS_ACCEPTED)->count(),
            'rejected' => $appointments->where('status', Appointment::STATUS_REJECTED)->count(),
            'canceled' => $appointments->where('status', Appointment::STATUS_CANCELED)->count(),
        ];
    }

    /**
     * Get recent activities
     */
    public function getRecentActivitiesAttribute()
    {
        return \Spatie\Activitylog\Models\Activity::where('causer_id', $this->id)
            ->where('causer_type', User::class)
            ->latest()
            ->take(10)
            ->get();
    }
}
