<?php

namespace App\Modules\ClientChat\Models;

use App\Modules\Tenancy\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    use BelongsToTenant, HasFactory;

    public $timestamps = false;

    protected $fillable = ['tenant_id', 'session_id', 'role', 'content', 'citations', 'ai_usage_log_id'];

    protected $casts = ['citations' => 'array'];
}
