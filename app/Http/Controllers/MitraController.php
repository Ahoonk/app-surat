<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesCompanyId;
use App\Models\Mitra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class MitraController extends Controller
{
    use ResolvesCompanyId;

    public function index()
    {
        $companyId = $this->getCompanyIdOrRedirect();
        if (!is_int($companyId)) {
            return $companyId;
        }

        $mitras = Mitra::where('company_id', $companyId)
            ->latest()
            ->get();

        return view('mitra.index', compact('mitras'));
    }

    public function edit(Mitra $mitra)
    {
        $companyId = $this->getCompanyIdOrRedirect();
        if (!is_int($companyId)) {
            return $companyId;
        }

        abort_if($mitra->company_id !== $companyId, 403);

        return view('mitra.edit', compact('mitra'));
    }

    public function store(Request $request)
    {
        $companyId = $this->getCompanyIdOrRedirect();
        if (!is_int($companyId)) {
            return $companyId;
        }

        $this->ensurePdfSupport($request);

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'alamat' => ['nullable', 'string', 'max:500'],
            'nomor_penawaran' => ['nullable', 'string', 'max:150'],
            'nomor_invoice' => ['nullable', 'string', 'max:150'],
            'nomor_surat_jalan' => ['nullable', 'string', 'max:150'],
            'nomor_berita_acara' => ['nullable', 'string', 'max:150'],
            'template_penawaran' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'template_invoice' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'template_surat_jalan' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'template_berita_acara' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $mitra = Mitra::create([
            'company_id' => $companyId,
            'nama' => $validated['nama'],
            'email' => $validated['email'] ?? null,
            'alamat' => $validated['alamat'] ?? null,
            'nomor_penawaran' => $validated['nomor_penawaran'] ?? null,
            'nomor_invoice' => $validated['nomor_invoice'] ?? null,
            'nomor_surat_jalan' => $validated['nomor_surat_jalan'] ?? null,
            'nomor_berita_acara' => $validated['nomor_berita_acara'] ?? null,
        ]);

        $this->replaceTemplate($mitra, $request->file('template_penawaran'), 'template_penawaran_path', 'penawaran');
        $this->replaceTemplate($mitra, $request->file('template_invoice'), 'template_invoice_path', 'invoice');
        $this->replaceTemplate($mitra, $request->file('template_surat_jalan'), 'template_surat_jalan_path', 'surat-jalan');
        $this->replaceTemplate($mitra, $request->file('template_berita_acara'), 'template_berita_acara_path', 'berita-acara');

        return redirect()->route('mitra.index')
            ->with('success', 'Mitra berhasil ditambahkan.');
    }

    public function update(Request $request, Mitra $mitra)
    {
        $companyId = $this->getCompanyIdOrRedirect();
        if (!is_int($companyId)) {
            return $companyId;
        }

        abort_if($mitra->company_id !== $companyId, 403);

        $this->ensurePdfSupport($request);

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'alamat' => ['nullable', 'string', 'max:500'],
            'nomor_penawaran' => ['nullable', 'string', 'max:150'],
            'nomor_invoice' => ['nullable', 'string', 'max:150'],
            'nomor_surat_jalan' => ['nullable', 'string', 'max:150'],
            'nomor_berita_acara' => ['nullable', 'string', 'max:150'],
            'template_penawaran' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'template_invoice' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'template_surat_jalan' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'template_berita_acara' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $mitra->update([
            'nama' => $validated['nama'],
            'email' => $validated['email'] ?? null,
            'alamat' => $validated['alamat'] ?? null,
            'nomor_penawaran' => $validated['nomor_penawaran'] ?? null,
            'nomor_invoice' => $validated['nomor_invoice'] ?? null,
            'nomor_surat_jalan' => $validated['nomor_surat_jalan'] ?? null,
            'nomor_berita_acara' => $validated['nomor_berita_acara'] ?? null,
        ]);

        $this->replaceTemplate($mitra, $request->file('template_penawaran'), 'template_penawaran_path', 'penawaran');
        $this->replaceTemplate($mitra, $request->file('template_invoice'), 'template_invoice_path', 'invoice');
        $this->replaceTemplate($mitra, $request->file('template_surat_jalan'), 'template_surat_jalan_path', 'surat-jalan');
        $this->replaceTemplate($mitra, $request->file('template_berita_acara'), 'template_berita_acara_path', 'berita-acara');

        return redirect()->route('mitra.index')
            ->with('success', 'Mitra berhasil diperbarui.');
    }

    public function destroy(Mitra $mitra)
    {
        $companyId = $this->getCompanyIdOrRedirect();
        if (!is_int($companyId)) {
            return $companyId;
        }

        abort_if($mitra->company_id !== $companyId, 403);

        $this->deleteTemplate($mitra->template_penawaran_path);
        $this->deleteTemplate($mitra->template_invoice_path);
        $this->deleteTemplate($mitra->template_surat_jalan_path);
        $this->deleteTemplate($mitra->template_berita_acara_path);

        $mitra->delete();

        return back()->with('success', 'Mitra berhasil dihapus.');
    }

    private function replaceTemplate(Mitra $mitra, $file, string $field, string $label): void
    {
        if (!$file) {
            return;
        }

        $path = $this->storeTemplate($file, $mitra, $label);
        if (!$path) {
            return;
        }

        $this->deleteTemplate($mitra->{$field});
        $mitra->update([$field => $path]);
    }

    private function deleteTemplate(?string $path): void
    {
        if (!$path) {
            return;
        }

        Storage::disk('public')->delete($path);
    }

    private function storeTemplate($file, Mitra $mitra, string $label): ?string
    {
        if (!$file) {
            return null;
        }

        $ext = strtolower($file->getClientOriginalExtension());
        $directory = 'mitra-templates/' . $mitra->id;

        if ($ext === 'pdf') {
            $imagick = new \Imagick();
            $imagick->setResolution(150, 150);
            $imagick->readImage($file->getRealPath() . '[0]');
            $imagick->setImageFormat('png');
            $pngData = $imagick->getImagesBlob();
            $filename = $label . '-template-' . time() . '.png';
            Storage::disk('public')->put($directory . '/' . $filename, $pngData);

            return $directory . '/' . $filename;
        }

        $filename = $label . '-template-' . time() . '.' . $ext;

        return $file->storeAs($directory, $filename, 'public');
    }

    private function ensurePdfSupport(Request $request): void
    {
        if (class_exists(\Imagick::class)) {
            return;
        }

        $fields = [
            'template_penawaran',
            'template_invoice',
            'template_surat_jalan',
            'template_berita_acara',
        ];

        foreach ($fields as $field) {
            $file = $request->file($field);
            if (!$file) {
                continue;
            }

            $ext = strtolower($file->getClientOriginalExtension());
            if ($ext === 'pdf') {
                throw ValidationException::withMessages([
                    $field => 'Template PDF membutuhkan ekstensi php-imagick. Upload PNG/JPG atau aktifkan imagick di server.',
                ]);
            }
        }
    }
}
