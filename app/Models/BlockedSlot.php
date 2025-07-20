<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class BlockedSlot extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'date',
        'start_time',
        'end_time',
        'type',
        'reason',
        'description',
        'blocked_by',
        'is_recurring',
        'recurrence_type',
        'recurrence_end_date',
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'recurrence_end_date' => 'date',
        'is_recurring' => 'boolean',
    ];

    // Types de blocage
    const TYPE_MANUAL = 'manual';
    const TYPE_LUNCH = 'lunch';
    const TYPE_HOLIDAY = 'holiday';
    const TYPE_MAINTENANCE = 'maintenance';
    const TYPE_MEETING = 'meeting';
    const TYPE_OTHER = 'other';

    // Types de récurrence
    const RECURRENCE_DAILY = 'daily';
    const RECURRENCE_WEEKLY = 'weekly';
    const RECURRENCE_MONTHLY = 'monthly';

    /**
     * Get the options for logging activity.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'date',
                'start_time',
                'end_time',
                'type',
                'reason',
                'description',
                'is_recurring',
                'recurrence_type',
                'recurrence_end_date'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('blocked_slot');
    }

    /**
     * Get the user who blocked this slot
     */
    public function blockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocked_by');
    }

    /**
     * Scope to filter by date range
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope to filter by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter recurring slots
     */
    public function scopeRecurring($query)
    {
        return $query->where('is_recurring', true);
    }

    /**
     * Scope to filter non-recurring slots
     */
    public function scopeNonRecurring($query)
    {
        return $query->where('is_recurring', false);
    }

    /**
     * Scope to filter active slots (not expired)
     */
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->where('is_recurring', false)
              ->orWhere(function ($q2) {
                  $q2->where('is_recurring', true)
                     ->where(function ($q3) {
                         $q3->whereNull('recurrence_end_date')
                             ->orWhere('recurrence_end_date', '>=', now()->toDateString());
                     });
              });
        });
    }

    /**
     * Check if slot overlaps with given time
     */
    public function overlapsWith($date, $startTime, $endTime): bool
    {
        if ($this->date->format('Y-m-d') !== $date->format('Y-m-d')) {
            return false;
        }

        $slotStart = $this->start_time->format('H:i');
        $slotEnd = $this->end_time->format('H:i');
        $requestStart = $startTime->format('H:i');
        $requestEnd = $endTime->format('H:i');

        return !($slotEnd <= $requestStart || $slotStart >= $requestEnd);
    }

    /**
     * Check if slot is for lunch break
     */
    public function isLunchBreak(): bool
    {
        return $this->type === self::TYPE_LUNCH;
    }

    /**
     * Check if slot is recurring
     */
    public function isRecurring(): bool
    {
        return $this->is_recurring;
    }

    /**
     * Check if recurring slot is still active
     */
    public function isRecurringActive(): bool
    {
        if (!$this->is_recurring) {
            return false;
        }

        return $this->recurrence_end_date === null || 
               $this->recurrence_end_date->isAfter(now());
    }

    /**
     * Get formatted type for display
     */
    public function getFormattedTypeAttribute(): string
    {
        $types = [
            self::TYPE_MANUAL => 'Manuel',
            self::TYPE_LUNCH => 'Pause déjeuner',
            self::TYPE_HOLIDAY => 'Jour férié',
            self::TYPE_MAINTENANCE => 'Maintenance',
            self::TYPE_MEETING => 'Réunion',
            self::TYPE_OTHER => 'Autre',
        ];

        return $types[$this->type] ?? $this->type;
    }

    /**
     * Get formatted recurrence type for display
     */
    public function getFormattedRecurrenceTypeAttribute(): string
    {
        if (!$this->is_recurring) {
            return 'Non récurrent';
        }

        $types = [
            self::RECURRENCE_DAILY => 'Quotidien',
            self::RECURRENCE_WEEKLY => 'Hebdomadaire',
            self::RECURRENCE_MONTHLY => 'Mensuel',
        ];

        return $types[$this->recurrence_type] ?? $this->recurrence_type;
    }

    /**
     * Get duration in minutes
     */
    public function getDurationInMinutesAttribute(): int
    {
        return $this->start_time->diffInMinutes($this->end_time);
    }

    /**
     * Get formatted time range
     */
    public function getFormattedTimeRangeAttribute(): string
    {
        return $this->start_time->format('H:i') . ' - ' . $this->end_time->format('H:i');
    }

    /**
     * Generate recurring slots for a given date range
     */
    public static function generateRecurringSlots($startDate, $endDate): array
    {
        $slots = [];
        $recurringSlots = self::recurring()->active()->get();

        foreach ($recurringSlots as $slot) {
            $currentDate = $startDate->copy();

            while ($currentDate <= $endDate) {
                // Check if slot is applicable for this day
                if ($slot->isApplicableForDate($currentDate)) {
                    // Create slot instance for this date
                    $slots[] = [
                        'date' => $currentDate->format('Y-m-d'),
                        'start_time' => $slot->start_time->format('H:i'),
                        'end_time' => $slot->end_time->format('H:i'),
                        'type' => $slot->type,
                        'reason' => $slot->reason,
                        'description' => $slot->description,
                    ];
                }

                // Always move to next day for proper coverage
                $currentDate->addDay();
            }
        }

        return $slots;
    }

    /**
     * Check if slot is applicable for a specific date
     */
    public function isApplicableForDate($date): bool
    {
        if (!$this->is_recurring) {
            return $this->date->format('Y-m-d') === $date->format('Y-m-d');
        }

        // Check if date is within recurrence range
        if ($this->recurrence_end_date && $date->isAfter($this->recurrence_end_date)) {
            return false;
        }

        // Check if date is after the original slot date
        if ($date->isBefore($this->date)) {
            return false;
        }

        // For lunch breaks, apply only on weekdays
        if ($this->type === self::TYPE_LUNCH) {
            return $date->isWeekday();
        }

        // Check recurrence pattern
        switch ($this->recurrence_type) {
            case self::RECURRENCE_DAILY:
                return true; // Every day
                
            case self::RECURRENCE_WEEKLY:
                // Same day of week as original slot
                return $date->dayOfWeek === $this->date->dayOfWeek;
                
            case self::RECURRENCE_MONTHLY:
                // Same day of month as original slot
                return $date->day === $this->date->day;
                
            default:
                return true;
        }
    }
} 