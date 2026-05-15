<?php

namespace App\Modules\Scheduling\Models;

use App\Modules\Tenancy\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobOccurrence extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = ['job_id', 'occurrence_at', 'status', 'rescheduled_to', 'completed_at'];

    protected $casts = [
        'occurrence_at'  => 'datetime',
        'rescheduled_to' => 'datetime',
        'completed_at'   => 'datetime',
    ];
}
