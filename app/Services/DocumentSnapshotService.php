<?php

namespace App\Services;

use App\Models\BeritaAcara;
use App\Models\Invoice;
use App\Models\NotaToko;
use App\Models\Penawaran;
use App\Models\SuratJalan;

class DocumentSnapshotService
{
    public function forPenawaran(Penawaran $penawaran): array
    {
        $penawaran->loadMissing([
            'company',
            'mitra',
            'items',
            'user',
        ]);

        return [
            'company' => [
                'id' => $penawaran->company?->id,
                'name' => $penawaran->company?->name,
                'address' => $penawaran->company?->address,
                'logo' => $penawaran->company?->logo,
            ],
            'mitra' => [
                'id' => $penawaran->mitra?->id,
                'name' => $penawaran->mitra?->nama,
            ],
            'nomor' => $penawaran->nomor,
            'tanggal' => $penawaran->tanggal,
            'customer_name' => $penawaran->to_company ?? $penawaran->customer_nama,
            'customer_address' => $penawaran->to_address,
            'jenis_kontrak' => $penawaran->jenis_kontrak,
            'signature_role' => $penawaran->signature_role,
            'keterangan' => $penawaran->keterangan,
            'subtotal' => (float) $penawaran->subtotal,
            'tax_percent' => (float) $penawaran->tax_percent,
            'tax_amount' => (float) $penawaran->tax_amount,
            'total' => (float) $penawaran->total,
            'status' => $penawaran->status,
            'invoice_date' => $penawaran->invoice_date,
            'invoice_number' => $penawaran->invoice_number,
            'invoice_sequence' => $penawaran->invoice_sequence,
            'approved_by' => $penawaran->approved_by,
            'approved_at' => $penawaran->approved_at,
            'creator_name' => $penawaran->user?->name,
            'items' => $penawaran->items->map(fn ($item) => [
                'nama' => $item->nama,
                'rincian' => $item->rincian,
                'qty' => (float) $item->qty,
                'satuan' => $item->satuan,
                'unit_price' => (float) $item->unit_price,
                'amount' => (float) $item->amount,
            ])->values()->all(),
        ];
    }

    public function forInvoice(Invoice $invoice): array
    {
        $invoice->loadMissing([
            'penawaran.company',
            'penawaran.mitra',
            'penawaran.items',
            'purchasingOrder',
        ]);

        $penawaran = $invoice->penawaran;
        $mitra = $penawaran?->mitra;

        return [
            'company' => [
                'id' => $penawaran?->company?->id,
                'name' => $penawaran?->company?->name,
                'address' => $penawaran?->company?->address,
                'logo' => $penawaran?->company?->logo,
            ],
            'is_mitra' => (bool) $mitra,
            'issuer_name' => $mitra?->nama ?? 'PT Aldera Saddatech Karya',
            'customer_name' => $penawaran?->to_company ?? $penawaran?->customer_nama,
            'customer_address' => $penawaran?->to_address,
            'invoice_number' => $invoice->nomor,
            'invoice_date' => $invoice->tanggal,
            'po_number' => $invoice->purchasingOrder?->nomor_po,
            'po_date' => $invoice->purchasingOrder?->tanggal_po,
            'items' => $penawaran?->items?->map(fn ($item) => [
                'nama' => $item->nama,
                'rincian' => $item->rincian,
                'qty' => (float) $item->qty,
                'satuan' => $item->satuan,
                'unit_price' => (float) $item->unit_price,
                'amount' => (float) $item->amount,
            ])->values()->all() ?? [],
            'subtotal' => (float) ($penawaran?->subtotal ?? 0),
            'tax_percent' => (float) ($penawaran?->tax_percent ?? 0),
            'tax_amount' => (float) ($penawaran?->tax_amount ?? 0),
            'total' => (float) ($penawaran?->total ?? $invoice->total ?? 0),
            'payment_status' => $invoice->payment_status,
            'payment_date' => $invoice->payment_date,
            'signature_role' => $penawaran?->signature_role,
        ];
    }

    public function forSuratJalan(SuratJalan $suratJalan): array
    {
        $suratJalan->loadMissing([
            'invoice.penawaran.company',
            'invoice.penawaran.mitra',
            'invoice.penawaran.items',
            'invoice.purchasingOrder',
        ]);

        $invoice = $suratJalan->invoice;
        $penawaran = $invoice?->penawaran;

        return [
            'invoice_number' => $invoice?->nomor,
            'invoice_date' => $invoice?->tanggal,
            'customer_name' => $penawaran?->to_company ?? $penawaran?->customer_nama,
            'customer_address' => $penawaran?->to_address,
            'sender_name' => $suratJalan->pemberi_nama,
            'sender_title' => $suratJalan->pemberi_jabatan,
            'sender_address' => $suratJalan->pemberi_alamat,
            'receiver_name' => $suratJalan->penerima_nama,
            'receiver_phone' => $suratJalan->penerima_hp,
            'city_date_manual' => $suratJalan->kota_tanggal_manual,
            'items' => $penawaran?->items?->map(fn ($item) => [
                'nama' => $item->nama,
                'rincian' => $item->rincian,
                'qty' => (float) $item->qty,
            ])->values()->all() ?? [],
        ];
    }

    public function forBeritaAcara(BeritaAcara $beritaAcara): array
    {
        $beritaAcara->loadMissing([
            'invoice.penawaran.company',
            'invoice.penawaran.mitra',
            'invoice.penawaran.items',
            'invoice.purchasingOrder',
        ]);

        $invoice = $beritaAcara->invoice;
        $penawaran = $invoice?->penawaran;
        $po = $invoice?->purchasingOrder;

        return [
            'invoice_number' => $invoice?->nomor,
            'invoice_date' => $invoice?->tanggal,
            'customer_name' => $penawaran?->to_company ?? $penawaran?->customer_nama,
            'customer_address' => $penawaran?->to_address,
            'po_number' => $po?->nomor_po,
            'subject' => $beritaAcara->perihal,
            'closing_note' => $beritaAcara->keterangan_akhir,
            'city_date_manual' => $beritaAcara->kota_tanggal_manual,
            'items' => $penawaran?->items?->map(fn ($item) => [
                'nama' => $item->nama,
                'rincian' => $item->rincian,
                'qty' => (float) $item->qty,
            ])->values()->all() ?? [],
        ];
    }

    public function forNotaToko(NotaToko $notaToko): array
    {
        $notaToko->loadMissing('items');

        return [
            'customer_name' => $notaToko->customer_nama,
            'customer_email' => $notaToko->customer_email,
            'address' => $notaToko->alamat,
            'notes' => $notaToko->keterangan,
            'date' => $notaToko->tanggal,
            'items' => $notaToko->items->map(fn ($item) => [
                'nama' => $item->nama,
                'qty' => (float) $item->qty,
                'satuan' => $item->satuan,
                'unit_price' => (float) $item->unit_price,
                'amount' => (float) $item->amount,
            ])->values()->all(),
            'subtotal' => (float) $notaToko->subtotal,
            'tax_percent' => (float) $notaToko->tax_percent,
            'tax_amount' => (float) $notaToko->tax_amount,
            'total' => (float) $notaToko->total,
            'payment_status' => $notaToko->payment_status,
            'payment_date' => $notaToko->payment_date,
        ];
    }
}
