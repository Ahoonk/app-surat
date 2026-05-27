# Database Audit

Audit awal skema database `surat-app` untuk persiapan rewrite yang lebih bersih.

## Gambaran Umum

Skema saat ini sudah memisahkan beberapa domain utama:

- tenant dan akses: `companies`, `users`
- master data: `customers`, `mitras`
- transaksi inti: `penawarans`, `purchasing_orders`, `invoices`
- dokumen turunan: `faktur_pajaks`, `surat_jalans`, `berita_acaras`
- transaksi retail: `nota_tokos`, `nota_toko_items`

Arsitekturnya sudah cukup jelas secara alur bisnis, tetapi masih bercampur antara:

- data master
- snapshot dokumen
- numbering / template config
- role / permission logic

## Tabel Inti

### `companies`

- Menyimpan identitas perusahaan.
- Dipakai sebagai tenant boundary utama.

### `users`

- Memiliki `company_id` dan `role`.
- Saat ini role masih dibatasi ke `admin` dan `superadmin`.
- `superadmin` masih menjadi konsep akses eksplisit di beberapa controller dan view.

### `customers`

- Master customer per company.
- Dipakai oleh `penawaran` dan `nota toko`.

### `mitras`

- Bukan sekadar master partner.
- Berisi:
  - identitas mitra
  - nomor dokumen default
  - path template dokumen

Ini berarti `mitras` saat ini juga berperan sebagai konfigurasi dokumen.

## Alur Penawaran ke Dokumen

### `penawarans`

Field penting:

- `company_id`
- `user_id`
- `mitra_id`
- `nomor`
- `tanggal`
- `customer_nama`
- `to_company`
- `to_address`
- `jenis_kontrak`
- `signature_role`
- `subtotal`
- `tax_percent`
- `tax_amount`
- `total`
- `status`
- `invoice_date`
- `invoice_number`
- `invoice_sequence`

Catatan:

- Tabel ini menyimpan snapshot customer/tujuan dokumen, bukan hanya relasi master.
- Ada campuran data transaksi, snapshot cetak, dan metadata numbering.

### `penawaran_items`

- Detail item penawaran.
- Sudah cukup normal secara struktur.
- `satuan` masih enum kecil, sehingga perlu dipastikan kalau ke depan ada variasi unit baru.

### `purchasing_orders`

- Relasi 1:1 ke `penawarans`.
- Menyimpan file PO dan metadata upload.
- Menjadi penghubung ke invoice berikutnya.

### `invoices`

- Relasi ke `penawarans`.
- Opsional relasi ke `purchasing_orders`.
- Menjadi pusat untuk dokumen lanjutan.

### `faktur_pajaks`

- Relasi 1:1 ke `invoices`.
- Menyimpan file faktur pajak dan metadata upload.

### `surat_jalans`

- Relasi 1:1 ke `invoices`.
- Menyimpan nomor, tanggal, dan field manual tambahan.

### `berita_acaras`

- Relasi 1:1 ke `invoices`.
- Mirip `surat_jalans`, hanya beda isi dokumen.

## Transaksi Retail

### `nota_tokos`

- Transaksi retail yang berdiri sendiri.
- Menggunakan:
  - `company_id`
  - `user_id`
  - `customer_nama`
  - `alamat`
  - subtotal, tax, total
  - payment fields

### `nota_toko_items`

- Detail item nota toko.
- Struktur ini juga sudah cukup bersih.

## Pola yang Perlu Diperhatikan

### 1. Denormalisasi yang memang sengaja dipakai

Beberapa field disimpan sebagai snapshot:

- `penawarans.customer_nama`
- `penawarans.to_company`
- `penawarans.to_address`
- `nota_tokos.customer_nama`
- `nota_tokos.alamat`

Ini membantu dokumen lama tetap konsisten walau master data berubah, jadi jangan langsung dihapus tanpa rencana pengganti.

### 2. Numbering tersebar

Nomor dokumen saat ini ada di banyak tempat:

- `penawarans.nomor`
- `penawarans.invoice_number`
- `invoices.nomor`
- `surat_jalans.nomor`
- `berita_acaras.nomor`
- `nota_tokos.nomor`
- `mitras.nomor_penawaran`
- `mitras.nomor_invoice`
- `mitras.nomor_surat_jalan`
- `mitras.nomor_berita_acara`

Ini kandidat utama untuk dirapikan menjadi satu sistem numbering / series.

### 3. Relasi dokumen masih sangat invoice-centric

Invoice menjadi pusat untuk:

- surat jalan
- berita acara
- faktur pajak

Itu cocok kalau alur bisnis memang selalu dimulai dari invoice, tetapi kalau nanti ada alur baru, struktur ini perlu dievaluasi.

### 4. Tenant boundary belum seragam

Sebagian besar data mengikuti `company_id`, tetapi tidak semua tabel turunan menyimpan `company_id` langsung.

Ini masih aman secara relasi, tetapi untuk query dan filter multi-tenant yang lebih sederhana, penambahan `company_id` di beberapa tabel turunan bisa dipertimbangkan.

## Kandidat Rewrite Bersih

Prioritas yang paling masuk akal:

1. Buat tabel terpusat untuk konfigurasi numbering dokumen.
2. Pisahkan master partner dari template / format dokumen jika tetap dibutuhkan.
3. Seragamkan snapshot field pada dokumen yang dicetak.
4. Tambahkan strategi tenant-aware yang konsisten di semua tabel dokumen.
5. Rapikan role / permission agar tidak bergantung pada string `superadmin` di banyak tempat.

## Data Yang Harus Dipertahankan

Saat rewrite, data yang sebaiknya dipertahankan:

- `companies`
- `users`
- `customers`
- `mitras`
- semua transaksi: `penawarans`, `penawaran_items`, `purchasing_orders`, `invoices`, `faktur_pajaks`, `surat_jalans`, `berita_acaras`, `nota_tokos`, `nota_toko_items`

Data yang paling aman dirombak adalah struktur dan relasinya, bukan isinya.

## Kesimpulan Awal

Skema saat ini belum rusak, tetapi terasa berkembang bertahap dan menyimpan banyak keputusan bisnis di level tabel individual.

Jadi arah rewrite yang paling aman adalah:

- pertahankan data lama
- tambahkan struktur baru secara bertahap
- migrasikan relasi dan numbering ke model yang lebih terpusat
- jangan menghapus tabel lama sebelum pemetaan data selesai
