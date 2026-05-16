<?php

namespace App\Modules\Tenancy\Models;

use App\Modules\Tenancy\Concerns\BelongsToTenant;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use BelongsToTenant, HasFactory;

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    protected $fillable = ['tenant_id', 'name', 'email', 'password', 'role'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = ['password' => 'hashed'];

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }
}
