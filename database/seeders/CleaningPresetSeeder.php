<?php

namespace Database\Seeders;

use App\Modules\Presets\Models\VerticalPreset;
use Illuminate\Database\Seeder;

class CleaningPresetSeeder extends Seeder
{
    public function run(): void
    {
        VerticalPreset::updateOrCreate(
            ['slug' => 'cleaning'],
            [
                'name' => 'Cleaning',
                'version' => '1',
                'vocabulary' => [
                    'client_singular' => 'presets.cleaning.vocab.client_singular',
                    'client_plural' => 'presets.cleaning.vocab.client_plural',
                    'job_singular' => 'presets.cleaning.vocab.job_singular',
                    'job_plural' => 'presets.cleaning.vocab.job_plural',
                ],
                'custom_fields_schema' => [
                    'client' => [
                        ['key' => 'area_m2',       'label_key' => 'presets.cleaning.fields.area_m2',       'type' => 'number',         'min' => 1, 'max' => 1000],
                        ['key' => 'property_type', 'label_key' => 'presets.cleaning.fields.property_type', 'type' => 'select',         'options' => ['apartment', 'house', 'office', 'retail'], 'required' => true],
                        ['key' => 'access_keys',   'label_key' => 'presets.cleaning.fields.access_keys',   'type' => 'encrypted_text'],
                        ['key' => 'preferences',   'label_key' => 'presets.cleaning.fields.preferences',   'type' => 'textarea'],
                        ['key' => 'allergies',     'label_key' => 'presets.cleaning.fields.allergies',     'type' => 'tags'],
                        ['key' => 'access_notes',  'label_key' => 'presets.cleaning.fields.access_notes',  'type' => 'text'],
                    ],
                    'job' => [
                        ['key' => 'difficulty', 'label_key' => 'presets.cleaning.fields.difficulty', 'type' => 'select', 'options' => ['standard', 'hard']],
                    ],
                ],
                'service_types' => [
                    ['key' => 'basic',           'label_key' => 'presets.cleaning.services.basic',           'default_unit' => 'm2',    'default_rate' => 4.0,  'default_duration_min' => 120],
                    ['key' => 'deep',            'label_key' => 'presets.cleaning.services.deep',            'default_unit' => 'm2',    'default_rate' => 6.5,  'default_duration_min' => 240],
                    ['key' => 'post_renovation', 'label_key' => 'presets.cleaning.services.post_renovation', 'default_unit' => 'm2',    'default_rate' => 9.0,  'default_duration_min' => 360],
                    ['key' => 'windows',         'label_key' => 'presets.cleaning.services.windows',         'default_unit' => 'piece', 'default_rate' => 25.0, 'default_duration_min' => 60],
                    ['key' => 'upholstery',      'label_key' => 'presets.cleaning.services.upholstery',      'default_unit' => 'piece', 'default_rate' => 80.0, 'default_duration_min' => 90],
                ],
                'quote_template' => [
                    'default_items' => [
                        ['service_type_key' => 'basic', 'unit' => 'm2', 'qty_from' => 'client.custom_fields.area_m2'],
                    ],
                    'auto_lines' => ['commute'],
                    'vat_default' => 8,
                    'rate_modifier_rules' => [
                        ['if' => "client.custom_fields.property_type == 'office'", 'rate_multiplier' => 1.15],
                    ],
                ],
                'ai_hints' => [
                    'pricing_factors' => ['area_m2', 'property_type', 'service_type_key', 'commute_km'],
                    'cold_start_note' => 'Use preset default rates when client history is thin.',
                ],
                'pdf_template_key' => 'cleaning_v1',
                'is_active' => true,
            ]
        );
    }
}
