<?php

namespace App\Modules\Employees\Models;

use App\Modules\Scheduling\Models\Job;
use App\Modules\Tenancy\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobEmployee extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'job_id', 'employee_id', 'hours_worked', 'payout_pln'];

    protected $casts = [
        'hours_worked' => 'decimal:2',
        'payout_pln' => 'decimal:2',
    ];

    /** @return BelongsTo<Job, $this> */
    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    /** @return BelongsTo<Employee, $this> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
