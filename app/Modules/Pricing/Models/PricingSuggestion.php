<?php

namespace App\Modules\Pricing\Models;

use App\Modules\AI\Models\AIUsageLog;
use App\Modules\Quoting\Models\Quote;
use App\Modules\Tenancy\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PricingSuggestion extends Model
{
    use BelongsToTenant, HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'quote_id', 'suggested_total', 'breakdown', 'reasoning',
        'confidence', 'prompt_version', 'ai_usage_log_id',
    ];

    protected $casts = [
        'breakdown' => 'array',
        'suggested_total' => 'decimal:2',
    ];

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function aiUsageLog(): BelongsTo
    {
        return $this->belongsTo(AIUsageLog::class);
    }

    public function feedback(): HasOne
    {
        return $this->hasOne(PricingSuggestionFeedback::class, 'suggestion_id');
    }
}
