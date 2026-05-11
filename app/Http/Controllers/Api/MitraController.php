<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ResolvesCompanyId;
use App\Models\Mitra;
use Illuminate\Http\JsonResponse;

class MitraController extends Controller
{
    use ResolvesCompanyId;

    public function index(): JsonResponse
    {
        $companyId = $this->getCompanyIdOrRedirect();

        $mitras = Mitra::where('company_id', $companyId)
            ->orderBy('nama')
            ->get([
                'id',
                'nama',
                'email',
                'alamat',
                'nomor_penawaran',
                'nomor_invoice',
                'nomor_surat_jalan',
                'nomor_berita_acara',
            ]);

        return response()->json([
            'data' => $mitras,
        ]);
    }
}
