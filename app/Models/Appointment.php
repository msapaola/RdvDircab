<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Appointment extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'preferred_date',
        'preferred_time',
        'priority',
        'status',
        'secure_token',
        'canceled_by_requester',
        'processed_by',
        'processed_at',
        'attachments',
        'admin_notes',
        'rejection_reason',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'preferred_date' => 'date',
        'preferred_time' => 'datetime:H:i',
        'processed_at' => 'datetime',
        'attachments' => 'array',
        'canceled_by_requester' => 'boolean',
    ];

    // Statuts disponibles
    const STATUS_PENDING = 'pending';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';
    const STATUS_CANCELED = 'canceled';
    const STATUS_CANCELED_BY_REQUESTER = 'canceled_by_requester';
    const STATUS_EXPIRED = 'expired';
    const STATUS_COMPLETED = 'completed';

    // Priorités disponibles
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_URGENT = 'urgent';
    const PRIORITY_OFFICIAL = 'official';

    /**
     * Get the options for logging activity.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'status',
                'priority',
                'admin_notes',
                'rejection_reason',
                'processed_by',
                'processed_at',
                'canceled_by_requester'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('appointment');
    }

    /**
     * Boot the model and generate secure token on creation
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($appointment) {
            if (empty($appointment->secure_token)) {
                $appointment->secure_token = Str::uuid();
            }
        });
    }

    /**
     * Get the user who processed this appointment
     */
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Scope to filter by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by priority
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('preferred_date', [$startDate, $endDate]);
    }

    /**
     * Scope to filter pending appointments
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope to filter accepted appointments
     */
    public function scopeAccepted($query)
    {
        return $query->where('status', self::STATUS_ACCEPTED);
    }

    /**
     * Scope to filter urgent and official appointments
     */
    public function scopeHighPriority($query)
    {
        return $query->whereIn('priority', [self::PRIORITY_URGENT, self::PRIORITY_OFFICIAL]);
    }

    /**
     * Scope to filter expired appointments
     */
    public function scopeExpired($query)
    {
        return $query->where('preferred_date', '<', now()->toDateString())
            ->where('status', self::STATUS_PENDING);
    }

    /**
     * Check if appointment is expired
     */
    public function isExpired(): bool
    {
        $appointmentDateTime = $this->preferred_date->setTimeFrom($this->preferred_time);
        return now()->isAfter($appointmentDateTime);
    }

    /**
     * Check if appointment can be canceled by requester
     */
    public function canBeCanceledByRequester(): bool
    {
        if ($this->status === self::STATUS_ACCEPTED) {
            $appointmentDateTime = $this->preferred_date->setTimeFrom($this->preferred_time);
            return now()->isBefore($appointmentDateTime->subDay());
        }
        
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_REJECTED]);
    }

    /**
     * Check if appointment is high priority
     */
    public function isHighPriority(): bool
    {
        return in_array($this->priority, [self::PRIORITY_URGENT, self::PRIORITY_OFFICIAL]);
    }

    /**
     * Get formatted status for display
     */
    public function getFormattedStatusAttribute(): string
    {
        $statuses = [
            self::STATUS_PENDING => 'En attente',
            self::STATUS_ACCEPTED => 'Accepté',
            self::STATUS_REJECTED => 'Refusé',
            self::STATUS_CANCELED => 'Annulé',
            self::STATUS_CANCELED_BY_REQUESTER => 'Annulé par le demandeur',
            self::STATUS_EXPIRED => 'Expiré',
            self::STATUS_COMPLETED => 'Terminé',
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    /**
     * Get formatted priority for display
     */
    public function getFormattedPriorityAttribute(): string
    {
        $priorities = [
            self::PRIORITY_NORMAL => 'Normale',
            self::PRIORITY_URGENT => 'Urgente',
            self::PRIORITY_OFFICIAL => 'Officielle',
        ];

        return $priorities[$this->priority] ?? $this->priority;
    }

    /**
     * Get appointment date and time as Carbon instance
     */
    public function getAppointmentDateTimeAttribute()
    {
        return $this->preferred_date->setTimeFrom($this->preferred_time);
    }

    /**
     * Accept the appointment
     */
    public function accept(User $user): bool
    {
        if ($this->status !== self::STATUS_PENDING) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_ACCEPTED,
            'processed_by' => $user->id,
            'processed_at' => now(),
        ]);

        return true;
    }

    /**
     * Reject the appointment
     */
    public function reject(User $user, string $reason = null): bool
    {
        if ($this->status !== self::STATUS_PENDING) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_REJECTED,
            'processed_by' => $user->id,
            'processed_at' => now(),
            'rejection_reason' => $reason,
        ]);

        return true;
    }

    /**
     * Cancel the appointment (by admin)
     */
    public function cancel(User $user, string $reason = null): bool
    {
        if (!in_array($this->status, [self::STATUS_PENDING, self::STATUS_ACCEPTED])) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_CANCELED,
            'processed_by' => $user->id,
            'processed_at' => now(),
            'admin_notes' => $reason,
        ]);

        return true;
    }

    /**
     * Cancel the appointment by requester
     */
    public function cancelByRequester(): bool
    {
        if (!$this->canBeCanceledByRequester()) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_CANCELED_BY_REQUESTER,
            'canceled_by_requester' => true,
        ]);

        return true;
    }

    /**
     * Mark appointment as expired
     */
    public function markAsExpired(): bool
    {
        if ($this->status !== self::STATUS_PENDING) {
            return false;
        }

        $this->update(['status' => self::STATUS_EXPIRED]);
        return true;
    }

    /**
     * Mark appointment as completed
     */
    public function markAsCompleted(User $user): bool
    {
        if ($this->status !== self::STATUS_ACCEPTED) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_COMPLETED,
            'processed_by' => $user->id,
            'processed_at' => now(),
        ]);

        return true;
    }

    /**
     * Get the secure tracking URL
     */
    public function getTrackingUrlAttribute(): string
    {
        return route('appointments.tracking', $this->secure_token);
    }

    /**
     * Check if appointment has attachments
     */
    public function hasAttachments(): bool
    {
        return !empty($this->attachments) && is_array($this->attachments);
    }

    /**
     * Get attachments count
     */
    public function getAttachmentsCountAttribute(): int
    {
        return $this->hasAttachments() ? count($this->attachments) : 0;
    }
} 