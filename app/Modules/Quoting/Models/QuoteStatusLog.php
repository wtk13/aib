<?php

namespace App\Modules\Quoting\Models;

use App\Modules\Tenancy\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuoteStatusLog extends Model
{
    use BelongsToTenant, HasFactory;

    public $timestamps = false;

    protected $fillable = ['quote_id', 'from_status', 'to_status', 'transitioned_by_user_id', 'meta'];

    protected $casts = ['meta' => 'array'];
}
