<?php

namespace App\Modules\AI\Models;

use App\Modules\Tenancy\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AIUsageLog extends Model
{
    use BelongsToTenant, HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id', 'feature', 'provider', 'model', 'prompt_version',
        'input_tokens', 'output_tokens', 'cost_pln', 'latency_ms', 'status', 'error_message',
    ];
}
