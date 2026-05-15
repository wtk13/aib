<?php

namespace App\Modules\Presets\Validators;

use App\Modules\Presets\Preset;
use Illuminate\Validation\ValidationException;

class CustomFieldsSchemaValidator
{
    public static function validate(array $fields, Preset $preset, string $entity = 'client'): void
    {
        $schema = $entity === 'client' ? $preset->clientFields() : $preset->jobFields();

        foreach ($schema as $field) {
            $key      = $field['key'];
            $required = $field['required'] ?? false;
            $type     = $field['type'];

            if ($required && empty($fields[$key])) {
                throw ValidationException::withMessages([
                    "custom_fields.{$key}" => "The {$key} field is required.",
                ]);
            }

            if (!empty($fields[$key]) && $type === 'select') {
                $allowed = $field['options'] ?? [];
                if (!in_array($fields[$key], $allowed, true)) {
                    throw ValidationException::withMessages([
                        "custom_fields.{$key}" => "Invalid value for {$key}.",
                    ]);
                }
            }

            if (!empty($fields[$key]) && $type === 'number') {
                if (!is_numeric($fields[$key])) {
                    throw ValidationException::withMessages([
                        "custom_fields.{$key}" => "The {$key} must be a number.",
                    ]);
                }
                if (isset($field['min']) && $fields[$key] < $field['min']) {
                    throw ValidationException::withMessages([
                        "custom_fields.{$key}" => "The {$key} must be at least {$field['min']}.",
                    ]);
                }
            }
        }
    }
}
