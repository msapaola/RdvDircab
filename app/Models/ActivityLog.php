<?php

namespace App\Models;

use Spatie\Activitylog\ActivityLogger;
use Spatie\Activitylog\Contracts\Activity as ActivityContract;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ActivityLog extends \Spatie\Activitylog\Models\Activity
{
    use LogsActivity;

    protected $table = 'activity_log';

    protected $fillable = [
        'log_name',
        'description',
        'subject_type',
        'subject_id',
        'causer_type',
        'causer_id',
        'properties',
        'event',
        'batch_uuid',
    ];

    protected $casts = [
        'properties' => 'collection',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the options for logging activity.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Scope to filter by appointment-related activities
     */
    public function scopeAppointmentActivities($query)
    {
        return $query->where('subject_type', Appointment::class);
    }

    /**
     * Scope to filter by user activities
     */
    public function scopeUserActivities($query)
    {
        return $query->where('subject_type', User::class);
    }

    /**
     * Scope to filter by specific event types
     */
    public function scopeByEvent($query, $event)
    {
        return $query->where('event', $event);
    }

    /**
     * Get the appointment associated with this activity
     */
    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'subject_id')
            ->where('subject_type', Appointment::class);
    }

    /**
     * Get the user who performed this activity
     */
    public function causer()
    {
        return $this->belongsTo(User::class, 'causer_id');
    }

    /**
     * Get the subject of this activity
     */
    public function subject()
    {
        return $this->morphTo();
    }

    /**
     * Get formatted description for display
     */
    public function getFormattedDescriptionAttribute()
    {
        $descriptions = [
            'created' => 'Créé',
            'updated' => 'Modifié',
            'deleted' => 'Supprimé',
            'accepted' => 'Accepté',
            'rejected' => 'Refusé',
            'canceled' => 'Annulé',
            'canceled_by_requester' => 'Annulé par le demandeur',
            'expired' => 'Expiré',
            'reminder_sent' => 'Rappel envoyé',
        ];

        return $descriptions[$this->event] ?? $this->description;
    }

    /**
     * Get the time elapsed since this activity
     */
    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }
} 