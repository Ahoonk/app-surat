<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesCompanyId;
use App\Models\DocumentTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\Rule;

class DocumentTemplateController extends Controller
{
    use ResolvesCompanyId;

    private const DOCUMENT_TYPES = [
        'penawaran' => 'Surat Penawaran',
        'invoice' => 'Invoice',
        'surat_jalan' => 'Surat Jalan',
        'berita_acara' => 'Berita Acara',
        'nota_toko' => 'Nota Toko',
    ];

    private const DEFAULT_VIEWS = [
        'penawaran' => 'penawaran.pdf',
        'invoice' => 'invoice.pdf',
        'surat_jalan' => 'surat-jalan.pdf',
        'berita_acara' => 'berita-acara.pdf',
        'nota_toko' => 'nota-toko.pdf',
    ];

    public function index()
    {
        $companyId = $this->getCompanyIdOrRedirect();
        if (!is_int($companyId)) {
            return $companyId;
        }

        $templates = DocumentTemplate::where('company_id', $companyId)
            ->orderBy('document_type')
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        return view('document-templates.index', [
            'templates' => $templates,
            'documentTypes' => self::DOCUMENT_TYPES,
            'defaultViews' => self::DEFAULT_VIEWS,
            'availableViews' => $this->availableViews(),
        ]);
    }

    public function store(Request $request)
    {
        $companyId = $this->getCompanyIdOrRedirect();
        if (!is_int($companyId)) {
            return $companyId;
        }

        $validated = $this->validatePayload($request);

        $template = DocumentTemplate::create([
            'company_id' => $companyId,
            'document_type' => $validated['document_type'],
            'name' => $validated['name'],
            'file_path' => $validated['file_path'],
            'is_default' => (bool) ($validated['is_default'] ?? false),
        ]);

        $this->syncDefault($template);

        return back()->with('success', 'Template dokumen berhasil ditambahkan.');
    }

    public function update(Request $request, DocumentTemplate $documentTemplate)
    {
        $companyId = $this->getCompanyIdOrRedirect();
        if (!is_int($companyId)) {
            return $companyId;
        }

        abort_if($documentTemplate->company_id !== $companyId, 403);

        $validated = $this->validatePayload($request);

        $documentTemplate->update([
            'document_type' => $validated['document_type'],
            'name' => $validated['name'],
            'file_path' => $validated['file_path'],
            'is_default' => (bool) ($validated['is_default'] ?? false),
        ]);

        $this->syncDefault($documentTemplate);

        return back()->with('success', 'Template dokumen berhasil diperbarui.');
    }

    public function destroy(DocumentTemplate $documentTemplate)
    {
        $companyId = $this->getCompanyIdOrRedirect();
        if (!is_int($companyId)) {
            return $companyId;
        }

        abort_if($documentTemplate->company_id !== $companyId, 403);

        $documentTemplate->delete();

        return back()->with('success', 'Template dokumen berhasil dihapus.');
    }

    private function validatePayload(Request $request): array
    {
        $documentTypes = array_keys(self::DOCUMENT_TYPES);

        return $request->validate([
            'document_type' => ['required', Rule::in($documentTypes)],
            'name' => ['required', 'string', 'max:255'],
            'file_path' => ['required', 'string', 'max:255'],
            'is_default' => ['nullable', 'boolean'],
        ]);
    }

    private function syncDefault(DocumentTemplate $template): void
    {
        if (! $template->is_default) {
            return;
        }

        DocumentTemplate::where('company_id', $template->company_id)
            ->where('document_type', $template->document_type)
            ->where('id', '!=', $template->id)
            ->update(['is_default' => false]);
    }

    private function availableViews(): array
    {
        $candidates = array_values(self::DEFAULT_VIEWS);

        return array_values(array_filter($candidates, fn (string $view) => View::exists($view)));
    }
}
