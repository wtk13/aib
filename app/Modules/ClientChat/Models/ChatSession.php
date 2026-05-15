<?php

namespace App\Modules\ClientChat\Models;

use App\Modules\Tenancy\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatSession extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = ['client_id', 'user_id', 'title'];
}
