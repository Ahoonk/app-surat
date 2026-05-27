<?php

namespace Tests\Unit;

use App\Models\Company;
use App\Models\Penawaran;
use App\Models\PenawaranItem;
use App\Models\User;
use App\Services\DocumentSnapshotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentSnapshotServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_builds_penawaran_snapshot(): void
    {
        $company = Company::create([
            'name' => 'PT Contoh',
            'address' => 'Jakarta',
            'logo' => null,
        ]);

        $user = User::factory()->create();

        $penawaran = Penawaran::create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'nomor' => 'PNW/0001/V/2026',
            'tanggal' => '2026-05-21',
            'customer_nama' => 'PT Pelanggan',
            'to_company' => 'PT Pelanggan',
            'to_address' => 'Bandung',
            'jenis_kontrak' => 'kontrak',
            'signature_role' => 'Direktur',
            'keterangan' => 'Catatan',
            'subtotal' => 100000,
            'tax_percent' => 11,
            'tax_amount' => 11000,
            'total' => 111000,
            'status' => 'draft',
        ]);

        $penawaran->items()->createMany([
            [
                'nama' => 'Jasa A',
                'rincian' => 'Rincian A',
                'qty' => 2,
                'satuan' => 'pcs',
                'unit_price' => 50000,
                'amount' => 100000,
            ],
        ]);

        $penawaran->load('company', 'items', 'user');

        $snapshot = app(DocumentSnapshotService::class)->forPenawaran($penawaran);

        $this->assertSame('PNW/0001/V/2026', $snapshot['nomor']);
        $this->assertSame('PT Pelanggan', $snapshot['customer_name']);
        $this->assertSame('Bandung', $snapshot['customer_address']);
        $this->assertSame(100000.0, $snapshot['subtotal']);
        $this->assertSame(1, count($snapshot['items']));
        $this->assertSame('Jasa A', $snapshot['items'][0]['nama']);
        $this->assertSame('Catatan', $snapshot['keterangan']);
    }
}
