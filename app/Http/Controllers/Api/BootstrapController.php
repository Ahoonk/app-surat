<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ResolvesCompanyId;
use App\Models\Customer;
use App\Models\Mitra;
use App\Services\DashboardDataService;
use Illuminate\Http\JsonResponse;

class BootstrapController extends Controller
{
    use ResolvesCompanyId;

    public function show(DashboardDataService $dashboardDataService): JsonResponse
    {
        $companyId = $this->getCompanyIdOrRedirect();

        $user = auth()->user()->load('company');
        $dashboard = $dashboardDataService->forCompany($companyId);

        $customers = Customer::where('company_id', $companyId)
            ->orderBy('nama')
            ->get(['id', 'nama', 'alamat', 'no_hp', 'email']);

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
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'company' => [
                'id' => $user->company?->id,
                'name' => $user->company?->name,
                'address' => $user->company?->address,
                'logo' => $user->company?->logo,
            ],
            'dashboard' => $dashboard,
            'lookups' => [
                'customers' => $customers,
                'mitras' => $mitras,
            ],
        ]);
    }
}
