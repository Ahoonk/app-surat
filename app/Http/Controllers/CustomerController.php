<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesCompanyId;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    use ResolvesCompanyId;

    public function index()
    {
        $companyId = $this->getCompanyIdOrRedirect();
        if (!is_int($companyId)) {
            return $companyId;
        }

        $customers = Customer::where('company_id', $companyId)
            ->orderByDesc('id')
            ->get();

        return view('customers.index', compact('customers'));
    }

    public function store(Request $request)
    {
        $companyId = $this->getCompanyIdOrRedirect();
        if (!is_int($companyId)) {
            return $companyId;
        }

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'alamat' => ['required', 'string', 'max:500'],
            'no_hp' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255'],
        ]);

        Customer::create([
            'company_id' => $companyId,
            'nama' => $validated['nama'],
            'alamat' => $validated['alamat'],
            'no_hp' => $validated['no_hp'],
            'email' => $validated['email'],
        ]);

        return back()->with('success', 'Customer berhasil ditambahkan.');
    }

    public function update(Request $request, Customer $customer)
    {
        $companyId = $this->getCompanyIdOrRedirect();
        if (!is_int($companyId)) {
            return $companyId;
        }

        abort_if($customer->company_id !== $companyId, 403);

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'alamat' => ['required', 'string', 'max:500'],
            'no_hp' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255'],
        ]);

        $customer->update($validated);

        return back()->with('success', 'Customer berhasil diperbarui.');
    }

    public function destroy(Customer $customer)
    {
        $companyId = $this->getCompanyIdOrRedirect();
        if (!is_int($companyId)) {
            return $companyId;
        }

        abort_if($customer->company_id !== $companyId, 403);

        $customer->delete();

        return back()->with('success', 'Customer berhasil dihapus.');
    }
}
