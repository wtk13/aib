<?php

namespace App\Modules\AI\Models;

use App\Modules\Pricing\Models\PricingSuggestion;
use App\Modules\Tenancy\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AIUsageLog extends Model
{
    use BelongsToTenant, HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id', 'feature', 'provider', 'model', 'prompt_version',
        'input_tokens', 'output_tokens', 'cost_pln', 'latency_ms', 'status', 'error_message',
    ];

    public function pricingSuggestions(): HasMany
    {
        return $this->hasMany(PricingSuggestion::class);
    }
}
