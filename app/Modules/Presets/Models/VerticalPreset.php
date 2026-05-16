<?php

namespace App\Modules\Presets\Models;

use Illuminate\Database\Eloquent\Model;

class VerticalPreset extends Model
{
    protected $fillable = [
        'slug', 'name', 'version', 'vocabulary',
        'custom_fields_schema', 'service_types',
        'quote_template', 'ai_hints', 'pdf_template_key', 'is_active',
    ];

    protected $casts = [
        'vocabulary' => 'array',
        'custom_fields_schema' => 'array',
        'service_types' => 'array',
        'quote_template' => 'array',
        'ai_hints' => 'array',
        'is_active' => 'boolean',
    ];
}
