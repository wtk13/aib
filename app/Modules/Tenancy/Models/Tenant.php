<?php

namespace App\Modules\Tenancy\Models;

use App\Modules\Presets\Preset;
use App\Modules\Presets\PresetRegistry;
use Database\Factories\TenantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    /** @use HasFactory<TenantFactory> */
    use HasFactory;

    protected $fillable = [
        'ulid', 'slug', 'company_name', 'nip', 'regon', 'preset_id',
    ];

    protected static ?int $currentId = null;

    protected static bool $bypassed = false;

    protected static function newFactory(): TenantFactory
    {
        return TenantFactory::new();
    }

    public static function current(): ?self
    {
        if (static::$currentId === null) {
            return null;
        }

        return static::withoutGlobalScopes()->find(static::$currentId);
    }

    public static function currentId(): ?int
    {
        return static::$currentId;
    }

    public static function setCurrent(self $tenant): void
    {
        static::$currentId = $tenant->id;
    }

    public static function switchByUlid(string $ulid): void
    {
        $tenant = static::withoutGlobalScopes()->where('ulid', $ulid)->firstOrFail();
        static::setCurrent($tenant);
    }

    public static function clear(): void
    {
        static::$currentId = null;
    }

    public static function bypass(callable $callback): mixed
    {
        $previous = static::$bypassed;
        static::$bypassed = true;
        try {
            return $callback();
        } finally {
            static::$bypassed = $previous;
        }
    }

    public static function isBypassed(): bool
    {
        return static::$bypassed;
    }

    public function preset(): Preset
    {
        return PresetRegistry::for($this);
    }
}
