<?php

namespace App\Modules\Pricing\Models;

use App\Modules\Tenancy\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PricingSuggestionFeedback extends Model
{
    use BelongsToTenant, HasFactory;

    public $timestamps = false;

    protected $fillable = ['suggestion_id', 'decision', 'final_total', 'diff_pct'];
}
