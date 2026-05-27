<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesCompanyId;
use App\Models\DocumentTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentTemplateController extends Controller
{
    use ResolvesCompanyId;

    private const DOCUMENT_TYPES = [
        'penawaran' => [
            'label' => 'Surat Penawaran',
            'field' => 'template_penawaran',
        ],
        'invoice' => [
            'label' => 'Invoice',
            'field' => 'template_invoice',
        ],
        'surat_jalan' => [
            'label' => 'Surat Jalan',
            'field' => 'template_surat_jalan',
        ],
        'berita_acara' => [
            'label' => 'Berita Acara',
            'field' => 'template_berita_acara',
        ],
    ];

    public function index()
    {
        $companyId = $this->getCompanyIdOrRedirect();
        if (!is_int($companyId)) {
            return $companyId;
        }

        $templatePaths = collect(self::DOCUMENT_TYPES)->mapWithKeys(function (array $meta, string $documentType) use ($companyId) {
            return [$documentType => app(\App\Services\DocumentTemplateResolver::class)->resolveTemplatePath($companyId, $documentType)];
        });

        return view('document-templates.index', [
            'templatePaths' => $templatePaths,
            'documentTypes' => self::DOCUMENT_TYPES,
        ]);
    }

    public function store(Request $request)
    {
        $companyId = $this->getCompanyIdOrRedirect();
        if (!is_int($companyId)) {
            return $companyId;
        }

        $this->validatePayload($request);
        $uploaded = false;

        foreach (self::DOCUMENT_TYPES as $documentType => $meta) {
            $field = $meta['field'];
            $file = $request->file($field);

            if (!$file) {
                continue;
            }

            $uploaded = true;
            $this->storeTemplate($companyId, $documentType, $meta['label'], $file);
        }

        if (!$uploaded) {
            return back()
                ->withErrors(['template_penawaran' => 'Pilih minimal satu file template untuk diunggah.'])
                ->withInput();
        }

        return back()->with('success', 'Template dokumen berhasil diperbarui.');
    }

    public function update(Request $request, DocumentTemplate $documentTemplate)
    {
        $companyId = $this->getCompanyIdOrRedirect();
        if (!is_int($companyId)) {
            return $companyId;
        }

        abort_if($documentTemplate->company_id !== $companyId, 403);

        $field = self::DOCUMENT_TYPES[$documentTemplate->document_type]['field'] ?? 'template';
        $validated = $request->validate([
            $field => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $this->storeTemplate(
            $companyId,
            $documentTemplate->document_type,
            self::DOCUMENT_TYPES[$documentTemplate->document_type]['label'] ?? $documentTemplate->name,
            $request->file($field),
            $documentTemplate
        );

        return back()->with('success', 'Template dokumen berhasil diperbarui.');
    }

    public function destroy(DocumentTemplate $documentTemplate)
    {
        $companyId = $this->getCompanyIdOrRedirect();
        if (!is_int($companyId)) {
            return $companyId;
        }

        abort_if($documentTemplate->company_id !== $companyId, 403);

        $this->deleteTemplate($documentTemplate->file_path);
        $documentTemplate->delete();

        return back()->with('success', 'Template dokumen berhasil dihapus.');
    }

    private function validatePayload(Request $request): array
    {
        $rules = [];

        foreach (self::DOCUMENT_TYPES as $meta) {
            $rules[$meta['field']] = ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'];
        }

        return $request->validate($rules);
    }

    private function storeTemplate(int $companyId, string $documentType, string $label, $file, ?DocumentTemplate $existingTemplate = null): ?DocumentTemplate
    {
        if (!$file) {
            return null;
        }

        $template = $existingTemplate ?: DocumentTemplate::query()
            ->where('company_id', $companyId)
            ->where('document_type', $documentType)
            ->where('is_default', true)
            ->first();

        if (!$template) {
            $template = new DocumentTemplate();
            $template->company_id = $companyId;
            $template->document_type = $documentType;
        }

        $oldPath = $template->file_path;
        $path = $this->storeUploadedFile($file, $companyId, $documentType);

        $template->fill([
            'name' => $label,
            'file_path' => $path,
            'is_default' => true,
        ]);
        $template->save();

        if ($oldPath && $oldPath !== $path) {
            $this->deleteTemplate($oldPath);
        }

        return $template;
    }

    private function storeUploadedFile($file, int $companyId, string $documentType): string
    {
        $ext = strtolower($file->getClientOriginalExtension());
        $directory = 'document-templates/' . $companyId . '/' . $documentType;
        $filename = $documentType . '-template-' . uniqid() . '.' . $ext;

        return $file->storeAs($directory, $filename, 'public');
    }

    private function deleteTemplate(?string $path): void
    {
        if (!$path) {
            return;
        }

        Storage::disk('public')->delete($path);
    }
}
