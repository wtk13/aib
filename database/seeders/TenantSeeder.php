<?php

namespace Database\Seeders;

use App\Modules\Crm\Models\Client;
use App\Modules\Presets\Models\VerticalPreset;
use App\Modules\Tenancy\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        $preset = VerticalPreset::where('slug', 'cleaning')->first();

        $ania = Tenant::updateOrCreate(
            ['slug' => 'ania'],
            [
                'ulid'       => (string) Str::ulid(),
                'firma_name' => 'Cleaning by Ania',
                'preset_id'  => $preset?->id,
            ]
        );

        \DB::table('users')->updateOrInsert(
            ['email' => 'ania@wyceny.app', 'tenant_id' => $ania->id],
            ['name' => 'Ania', 'password' => Hash::make('password'), 'role' => 'owner']
        );

        Tenant::setCurrent($ania);
        Client::factory()->count(3)->create();
        Tenant::clear();

        $test = Tenant::updateOrCreate(
            ['slug' => 'test'],
            [
                'ulid'       => (string) Str::ulid(),
                'firma_name' => 'Test Company',
                'preset_id'  => $preset?->id,
            ]
        );

        \DB::table('users')->updateOrInsert(
            ['email' => 'test@wyceny.app', 'tenant_id' => $test->id],
            ['name' => 'Test', 'password' => Hash::make('password'), 'role' => 'owner']
        );
    }
}
