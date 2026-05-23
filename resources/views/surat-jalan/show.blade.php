@extends('layouts.app')

@section('content')
@php
    $toDataUri = static function (string $path): ?string {
        if (!file_exists($path)) {
            return null;
        }

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if ($ext === 'pdf') {
            if (!class_exists(\Imagick::class)) {
                return null;
            }

            try {
                $imagick = new \Imagick();
                $imagick->setResolution(300, 300);
                $imagick->readImage($path . '[0]');
                $imagick->setImageFormat('png');

                return 'data:image/png;base64,' . base64_encode($imagick->getImageBlob());
            } catch (\Throwable $e) {
                report($e);
            }

            $gsBinary = trim((string) shell_exec('command -v gs 2>/dev/null'));
            if ($gsBinary !== '') {
                $tmpDir = storage_path('app/template-previews');
                if (!is_dir($tmpDir)) {
                    @mkdir($tmpDir, 0775, true);
                }

                $prefix = tempnam($tmpDir, 'pdf-');
                if ($prefix !== false) {
                    $pngPath = $prefix . '.png';
                    @unlink($prefix);

                    $cmd = escapeshellarg($gsBinary)
                        . ' -dSAFER -dBATCH -dNOPAUSE -sDEVICE=pngalpha -r300 -dFirstPage=1 -dLastPage=1 -sOutputFile='
                        . escapeshellarg($pngPath) . ' ' . escapeshellarg($path) . ' 2>&1';

                    $output = [];
                    $exitCode = 0;
                    @exec($cmd, $output, $exitCode);

                    if ($exitCode === 0 && file_exists($pngPath)) {
                        $binary = file_get_contents($pngPath);
                        @unlink($pngPath);

                        if ($binary !== false) {
                            return 'data:image/png;base64,' . base64_encode($binary);
                        }
                    }

                    @unlink($pngPath);
                }
            }

            return null;
        }

        $mime = match ($ext) {
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            default => null,
        };

        if (!$mime) {
            return null;
        }

        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
    };
    $snapshot = $suratJalan->snapshot_data ?? [];
    $invoice = $suratJalan->invoice;
    $penawaran = $invoice->penawaran;
    $mitra = $penawaran->mitra;
    $mitraTemplatePath = $mitra?->template_surat_jalan_path
        ? public_path('storage/' . $mitra->template_surat_jalan_path)
        : null;
    $mitraTemplateAsset = $mitraTemplatePath ? $toDataUri($mitraTemplatePath) : null;
    $companyTemplatePath = app(\App\Services\DocumentTemplateResolver::class)->resolveTemplatePath($penawaran->company_id, 'surat_jalan');
    $companyTemplateAsset = $companyTemplatePath
        ? $toDataUri(public_path('storage/' . $companyTemplatePath))
        : null;
    $documentTemplateAsset = $mitraTemplateAsset ?: $companyTemplateAsset;
    $kopAtasAsset = file_exists(public_path('storage/logos/kopatas.png')) ? $toDataUri(public_path('storage/logos/kopatas.png')) : null;
    $kopBawahAsset = file_exists(public_path('storage/logos/kopbawah.png')) ? $toDataUri(public_path('storage/logos/kopbawah.png')) : null;
    $bgPrimaryPath = public_path('storage/logos/backgroud-template.png');
    $bgFallbackPath = public_path('storage/logos/background-template.png');
    $bgAsset = null;
    if (file_exists($bgPrimaryPath)) {
        $bgAsset = $toDataUri($bgPrimaryPath);
    } elseif (file_exists($bgFallbackPath)) {
        $bgAsset = $toDataUri($bgFallbackPath);
    }
    $tanggalCetakSource = data_get($snapshot, 'city_date_manual') ?: $suratJalan->kota_tanggal_manual ?: $suratJalan->tanggal;
    $tanggalCetak = $tanggalCetakSource
        ? \Illuminate\Support\Carbon::parse($tanggalCetakSource)->translatedFormat('d F Y')
        : '-';
    $previewPaperStyle = 'width:100%;max-width:794px;min-height:1123px;';
    $previewContentStyle = 'padding:170px 15mm 110px 15mm;position:relative;z-index:2;';
@endphp
<div class="space-y-6">
    @if (session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-3">
        <h1 class="text-2xl font-semibold text-gray-800">Preview Surat Jalan</h1>
        <div class="flex gap-2">
            <a href="{{ route('surat-jalan.edit', $suratJalan) }}" class="px-4 py-2 bg-emerald-600 text-white rounded-lg">Ubah Data</a>
            <a href="{{ route('surat-jalan.pdf', ['suratJalan' => $suratJalan, 'download' => 1]) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg">Export PDF</a>
            <a href="{{ route('surat-jalan.index') }}" class="px-4 py-2 bg-gray-200 rounded-lg">Kembali</a>
        </div>
    </div>

    <div class="mx-auto bg-white rounded-2xl shadow-xl relative overflow-hidden" style="{{ $previewPaperStyle }}">
        @if ($documentTemplateAsset)
            <div style="position:absolute;inset:0;background-image:url('{{ $documentTemplateAsset }}');background-repeat:no-repeat;background-position:top center;background-size:100% 100%;z-index:0;"></div>
        @else
            @if ($bgAsset)
                <div style="position:absolute;inset:0;background-image:url('{{ $bgAsset }}');background-repeat:no-repeat;background-position:center 36%;background-size:50% auto;z-index:0;"></div>
            @endif
            @if ($kopAtasAsset)
                <img src="{{ $kopAtasAsset }}" alt="Kop Atas" style="position:absolute;top:-8mm;left:0;right:0;width:106%;margin-left:-1%;height:auto;display:block;z-index:1;">
            @endif

            @if ($kopBawahAsset)
                <img src="{{ $kopBawahAsset }}" alt="Kop Bawah" style="position:absolute;bottom:0;left:0;right:0;width:100%;height:auto;display:block;z-index:1;">
            @endif
        @endif

        <div style="{{ $previewContentStyle }}">
            <div class="text-center mb-6">
                <h2 class="text-xl font-bold uppercase">Surat Jalan</h2>
                <p>No: {{ data_get($snapshot, 'nomor', $suratJalan->nomor) }}</p>
            </div>

            <div class="mb-4 text-sm">
                <p><strong>No Invoice:</strong> {{ data_get($snapshot, 'invoice_number', $invoice->nomor) }}</p>
                <p><strong>Customer:</strong> {{ data_get($snapshot, 'customer_name', $penawaran->to_company ?? $penawaran->customer_nama) }}</p>
                <p><strong>Alamat:</strong> {{ data_get($snapshot, 'customer_address', $penawaran->to_address ?? '-') }}</p>
            </div>

            <div class="space-y-4 text-sm">
                <div>
                    <p>Bersama ini, saya yang bertanda tangan dibawah:</p>
                    <table class="mt-2">
                        <tr>
                            <td class="w-40 align-top">Nama</td>
                            <td class="w-3 align-top">:</td>
                            <td class="align-top">{{ data_get($snapshot, 'sender_name', $suratJalan->pemberi_nama ?? 'Bayu Suderajat') }}</td>
                        </tr>
                        <tr>
                            <td class="w-40 align-top">Jabatan</td>
                            <td class="w-3 align-top">:</td>
                            <td class="align-top">{{ data_get($snapshot, 'sender_title', $suratJalan->pemberi_jabatan ?? 'Direktur') }}</td>
                        </tr>
                        <tr>
                            <td class="w-40 align-top">Alamat</td>
                            <td class="w-3 align-top">:</td>
                            <td class="align-top">{{ data_get($snapshot, 'sender_address', $suratJalan->pemberi_alamat ?? 'Perum Bukit Cilegon Asri, Blok BK No.09, Rt/Rw. 014/006, Kelurahan Bagendung, Kecamatan Cilegon') }}</td>
                        </tr>
                    </table>
                </div>

                <div>
                    <p>Memberikan kuasa kepada sebagai berikut:</p>
                    <table class="mt-2">
                        <tr>
                            <td class="w-40 align-top">Nama</td>
                            <td class="w-3 align-top">:</td>
                            <td class="align-top">{{ data_get($snapshot, 'receiver_name', $suratJalan->penerima_nama ?? '-') }}</td>
                        </tr>
                        <tr>
                            <td class="w-40 align-top">No. Handphone</td>
                            <td class="w-3 align-top">:</td>
                            <td class="align-top">{{ data_get($snapshot, 'receiver_phone', $suratJalan->penerima_hp ?? '-') }}</td>
                        </tr>
                    </table>
                </div>

                <p>Untuk membawa barang milik "{{ data_get($snapshot, 'customer_name', $penawaran->to_company ?? $penawaran->customer_nama) }}", dengan rincian:</p>
            </div>

            <div class="overflow-x-auto mt-4">
                <table class="w-full border-collapse text-sm">
                    <thead>
                        <tr>
                            <th class="border px-3 py-2 text-center w-[8%]">No</th>
                            <th class="border px-3 py-2 text-center">Description</th>
                            <th class="border px-3 py-2 text-center w-[15%]">Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach (data_get($snapshot, 'items', $penawaran->items) as $item)
                            @php
                                $rincianLines = preg_split('/\r\n|\r|\n/', trim((string) data_get($item, 'rincian')));
                                $rincianLines = array_values(array_filter(array_map('trim', $rincianLines ?: []), fn ($line) => $line !== ''));
                            @endphp
                            <tr>
                                <td class="border px-3 py-2 text-center align-top">{{ $loop->iteration }}</td>
                                <td class="border px-3 py-2 align-top">
                                    <div class="font-medium text-gray-900">{{ data_get($item, 'nama') }}</div>
                                    @if (!empty($rincianLines))
                                        <div class="mt-2 space-y-1 text-[11px] leading-5 text-gray-600">
                                            @foreach ($rincianLines as $line)
                                                <div class="flex gap-2">
                                                    <span class="shrink-0">&#8226;</span>
                                                    <span>{{ $line }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="border px-3 py-2 text-center align-top">{{ rtrim(rtrim(number_format((float) data_get($item, 'qty', 0), 2, '.', ''), '0'), '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-8 text-sm">
                <p>Demikian, surat jalan ini dibuat agar dilaksanakan sebaik-baiknya dan sebagaimana mestinya.</p>
                <div class="mt-8 flex justify-end">
                    <div class="text-center">
                        <p>Kota Cilegon, {{ $tanggalCetak }}</p>
                        <p>Direktur</p>
                        <div class="h-16"></div>
                        <p class="font-semibold">{{ data_get($snapshot, 'sender_name', $suratJalan->pemberi_nama ?? 'Bayu Suderajat') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
