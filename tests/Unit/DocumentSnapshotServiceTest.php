<?php

namespace Tests\Unit;

use App\Models\Company;
use App\Models\BeritaAcara;
use App\Models\Invoice;
use App\Models\Penawaran;
use App\Models\PurchasingOrder;
use App\Models\SuratJalan;
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

    public function test_it_refreshes_existing_invoice_and_related_document_snapshots_when_penawaran_changes(): void
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
            'nomor' => 'PNW/0002/V/2026',
            'tanggal' => '2026-05-21',
            'customer_nama' => 'PT Pelanggan',
            'to_company' => 'PT Pelanggan',
            'to_address' => 'Bandung',
            'jenis_kontrak' => 'satuan',
            'signature_role' => 'Direktur',
            'subtotal' => 100000,
            'tax_percent' => 11,
            'tax_amount' => 11000,
            'total' => 111000,
            'status' => 'approved',
        ]);

        $penawaran->items()->create([
            'nama' => 'Jasa Lama',
            'rincian' => 'Rincian lama',
            'qty' => 1,
            'satuan' => 'pcs',
            'unit_price' => 100000,
            'amount' => 100000,
        ]);

        $po = PurchasingOrder::create([
            'company_id' => $company->id,
            'penawaran_id' => $penawaran->id,
            'dokumen_path' => 'purchasing-orders/po.pdf',
            'dokumen_name' => 'po.pdf',
            'nomor_po' => 'PO/001',
            'tanggal_po' => '2026-05-22',
            'uploaded_by' => $user->id,
            'uploaded_at' => now(),
        ]);

        $invoice = Invoice::create([
            'company_id' => $company->id,
            'penawaran_id' => $penawaran->id,
            'purchasing_order_id' => $po->id,
            'nomor' => 'INV/2026/05/001-ASK',
            'tanggal' => '2026-05-23',
            'sequence' => 1,
            'total' => 111000,
            'created_by' => $user->id,
        ]);

        $suratJalan = SuratJalan::create([
            'company_id' => $company->id,
            'invoice_id' => $invoice->id,
            'nomor' => 'SJ/2026/05/001',
            'tanggal' => '2026-05-23',
            'created_by' => $user->id,
        ]);

        $beritaAcara = BeritaAcara::create([
            'company_id' => $company->id,
            'invoice_id' => $invoice->id,
            'nomor' => 'BA/2026/05/001',
            'tanggal' => '2026-05-23',
            'created_by' => $user->id,
        ]);

        app(DocumentSnapshotService::class)->refreshPenawaranAndRelatedDocuments($penawaran);

        $penawaran->update([
            'to_company' => 'PT Pelanggan Baru',
            'customer_nama' => 'PT Pelanggan Baru',
            'to_address' => 'Serang',
            'subtotal' => 200000,
            'tax_amount' => 22000,
            'total' => 222000,
        ]);
        $penawaran->items()->delete();
        $penawaran->items()->create([
            'nama' => 'Jasa Baru',
            'rincian' => 'Rincian baru',
            'qty' => 2,
            'satuan' => 'pcs',
            'unit_price' => 100000,
            'amount' => 200000,
        ]);

        app(DocumentSnapshotService::class)->refreshPenawaranAndRelatedDocuments($penawaran);

        $invoice->refresh();
        $suratJalan->refresh();
        $beritaAcara->refresh();

        $this->assertSame(222000.0, (float) $invoice->total);
        $this->assertSame('PT Pelanggan Baru', data_get($invoice->snapshot_data, 'customer_name'));
        $this->assertSame('Jasa Baru', data_get($invoice->snapshot_data, 'items.0.nama'));
        $this->assertSame('Jasa Baru', data_get($suratJalan->snapshot_data, 'items.0.nama'));
        $this->assertSame('Jasa Baru', data_get($beritaAcara->snapshot_data, 'items.0.nama'));
    }
}
