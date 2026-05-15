<?php

namespace App\Modules\Quoting\Models;

use App\Modules\Tenancy\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuoteItem extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'quote_id', 'position', 'description', 'unit',
        'quantity', 'rate', 'discount_pct', 'vat_pct', 'line_total',
        'service_type_key', 'source',
    ];
}
