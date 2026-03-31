<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Company;

trait ResolvesCompanyId
{
    protected function getCompanyIdOrRedirect()
    {
        $user = auth()->user();
        $companyId = $user->company_id;

        if (!$companyId) {
            $company = Company::firstOrCreate(
                ['name' => 'PT Aldera Saddatech Karya'],
                ['address' => '-', 'logo' => null]
            );
            $user->update(['company_id' => $company->id]);
            $companyId = $company->id;
        }

        return $companyId;
    }
}
