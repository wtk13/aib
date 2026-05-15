<?php

namespace App\Modules\Presets;

final class Preset
{
    public function __construct(
        private readonly string $slug,
        private readonly string $version,
        private readonly array  $vocabulary,
        private readonly array  $customFieldsSchema,
        private readonly array  $serviceTypes,
        private readonly array  $quoteTemplate,
        private readonly array  $aiHints,
        private readonly string $pdfTemplateKey,
    ) {}

    public static function fromModel(\App\Modules\Presets\Models\VerticalPreset $model): self
    {
        return new self(
            slug:               $model->slug,
            version:            $model->version,
            vocabulary:         $model->vocabulary ?? [],
            customFieldsSchema: $model->custom_fields_schema ?? [],
            serviceTypes:       $model->service_types ?? [],
            quoteTemplate:      $model->quote_template ?? [],
            aiHints:            $model->ai_hints ?? [],
            pdfTemplateKey:     $model->pdf_template_key,
        );
    }

    public function slug(): string              { return $this->slug; }
    public function version(): string           { return $this->version; }
    public function vocabulary(): array         { return $this->vocabulary; }
    public function customFieldsSchema(): array { return $this->customFieldsSchema; }
    public function serviceTypes(): array       { return $this->serviceTypes; }
    public function quoteTemplate(): array      { return $this->quoteTemplate; }
    public function aiHints(): array            { return $this->aiHints; }
    public function pdfTemplateKey(): string    { return $this->pdfTemplateKey; }

    public function clientFields(): array
    {
        return $this->customFieldsSchema['client'] ?? [];
    }

    public function jobFields(): array
    {
        return $this->customFieldsSchema['job'] ?? [];
    }
}
