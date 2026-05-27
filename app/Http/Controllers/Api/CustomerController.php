<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ResolvesCompanyId;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;

class CustomerController extends Controller
{
    use ResolvesCompanyId;

    public function index(): JsonResponse
    {
        $companyId = $this->getCompanyIdOrRedirect();

        $customers = Customer::where('company_id', $companyId)
            ->orderBy('nama')
            ->get(['id', 'nama', 'alamat', 'no_hp', 'email']);

        return response()->json([
            'data' => $customers,
        ]);
    }
}
