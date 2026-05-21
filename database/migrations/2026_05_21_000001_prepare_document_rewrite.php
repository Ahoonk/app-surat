<?php

use App\Models\BeritaAcara;
use App\Models\FakturPajak;
use App\Models\Invoice;
use App\Models\PurchasingOrder;
use App\Models\SuratJalan;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_series', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('document_type', 50);
            $table->string('prefix')->nullable();
            $table->boolean('year_mode')->default(true);
            $table->boolean('month_mode')->default(true);
            $table->unsignedBigInteger('counter')->default(0);
            $table->unsignedTinyInteger('padding')->default(3);
            $table->string('suffix')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'document_type']);
        });

        Schema::create('document_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('document_type', 50);
            $table->string('name');
            $table->string('file_path');
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index(['company_id', 'document_type']);
        });

        Schema::table('purchasing_orders', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('penawaran_id')->constrained()->cascadeOnDelete();
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('purchasing_order_id')->constrained()->cascadeOnDelete();
        });

        Schema::table('faktur_pajaks', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('invoice_id')->constrained()->cascadeOnDelete();
        });

        Schema::table('surat_jalans', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('invoice_id')->constrained()->cascadeOnDelete();
        });

        Schema::table('berita_acaras', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('invoice_id')->constrained()->cascadeOnDelete();
        });

        PurchasingOrder::query()
            ->with('penawaran:id,company_id')
            ->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    $row->update([
                        'company_id' => $row->penawaran?->company_id,
                    ]);
                }
            });

        Invoice::query()
            ->with('penawaran:id,company_id')
            ->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    $row->update([
                        'company_id' => $row->penawaran?->company_id,
                    ]);
                }
            });

        FakturPajak::query()
            ->with('invoice.penawaran:id,company_id')
            ->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    $row->update([
                        'company_id' => $row->invoice?->penawaran?->company_id,
                    ]);
                }
            });

        SuratJalan::query()
            ->with('invoice.penawaran:id,company_id')
            ->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    $row->update([
                        'company_id' => $row->invoice?->penawaran?->company_id,
                    ]);
                }
            });

        BeritaAcara::query()
            ->with('invoice.penawaran:id,company_id')
            ->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    $row->update([
                        'company_id' => $row->invoice?->penawaran?->company_id,
                    ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('berita_acaras', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
        });

        Schema::table('surat_jalans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
        });

        Schema::table('faktur_pajaks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
        });

        Schema::table('purchasing_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
        });

        Schema::dropIfExists('document_templates');
        Schema::dropIfExists('document_series');
    }
};
