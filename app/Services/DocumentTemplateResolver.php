<?php

namespace App\Services;

use App\Models\DocumentTemplate;
use Illuminate\Support\Facades\View;

class DocumentTemplateResolver
{
    public function resolveTemplatePath(?int $companyId, string $documentType): ?string
    {
        if (! $companyId) {
            return null;
        }

        $templates = DocumentTemplate::query()
            ->where('company_id', $companyId)
            ->where('document_type', $documentType)
            ->orderByDesc('is_default')
            ->orderByDesc('updated_at')
            ->get();

        foreach ($templates as $template) {
            if (empty($template->file_path)) {
                continue;
            }

            $relativePath = ltrim($template->file_path, '/\\');
            $absolutePath = public_path('storage/' . $relativePath);

            if (file_exists($absolutePath)) {
                return $relativePath;
            }
        }

        return null;
    }

    public function resolveView(?int $companyId, string $documentType, string $defaultView): string
    {
        if (! $companyId) {
            return $defaultView;
        }

        $template = DocumentTemplate::query()
            ->where('company_id', $companyId)
            ->where('document_type', $documentType)
            ->where('is_default', true)
            ->first();

        if (! $template || empty($template->file_path)) {
            return $defaultView;
        }

        $candidate = $this->normalizeViewName($template->file_path);

        return View::exists($candidate) ? $candidate : $defaultView;
    }

    private function normalizeViewName(string $value): string
    {
        $value = trim(str_replace(['\\', '/'], '.', $value));

        $value = preg_replace('/\.blade(\.php)?$/', '', $value) ?? $value;
        $value = preg_replace('/^resources\.views\./', '', $value) ?? $value;
        $value = preg_replace('/^views\./', '', $value) ?? $value;

        return $value;
    }
}
