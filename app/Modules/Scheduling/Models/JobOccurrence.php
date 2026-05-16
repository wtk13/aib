<?php

namespace App\Modules\Scheduling\Models;

use App\Modules\Tenancy\Concerns\BelongsToTenant;
use Database\Factories\JobOccurrenceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobOccurrence extends Model
{
    /** @use HasFactory<JobOccurrenceFactory> */
    use BelongsToTenant, HasFactory;

    protected static function newFactory(): JobOccurrenceFactory
    {
        return JobOccurrenceFactory::new();
    }

    protected $fillable = ['job_id', 'occurrence_at', 'status', 'rescheduled_to', 'completed_at'];

    protected $casts = [
        'occurrence_at' => 'datetime',
        'rescheduled_to' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /** @return BelongsTo<Job, $this> */
    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }
}
