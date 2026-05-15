<?php

namespace App\Modules\Notes\Models;

use App\Modules\Tenancy\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Note extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id', 'body', 'body_cleaned', 'audio_path',
        'audio_duration_seconds', 'status', 'source', 'created_by_user_id',
    ];
}
