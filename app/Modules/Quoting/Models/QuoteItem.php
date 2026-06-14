<?php

namespace App\Modules\Quoting\Models;

use App\Modules\Tenancy\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteItem extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'quote_id', 'position', 'description', 'unit',
        'quantity', 'rate', 'discount_pct', 'vat_pct', 'line_total',
        'service_type_key', 'source',
    ];

    protected $casts = [
        'quantity'     => 'decimal:2',
        'rate'         => 'decimal:2',
        'discount_pct' => 'decimal:2',
        'vat_pct'      => 'integer',
        'line_total'   => 'decimal:2',
        'position'     => 'integer',
    ];

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }
}
