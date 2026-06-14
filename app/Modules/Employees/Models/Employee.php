<?php

namespace App\Modules\Employees\Models;

use App\Modules\Tenancy\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use BelongsToTenant;

    protected $fillable = ['name', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    /** @return HasMany<JobEmployee, $this> */
    public function jobEmployees(): HasMany
    {
        return $this->hasMany(JobEmployee::class);
    }
}
