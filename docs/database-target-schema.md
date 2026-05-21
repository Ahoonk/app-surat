# Target Database Schema

Blueprint awal untuk rewrite database `surat-app` agar struktur lebih bersih, konsisten, dan mudah dipelihara.

## Tujuan

- mempertahankan data lama
- memisahkan master data, konfigurasi, dan transaksi
- membuat numbering dokumen terpusat
- mengurangi field snapshot yang tersebar tanpa kontrol
- menyiapkan tenant-aware structure yang lebih konsisten

## Prinsip Desain

1. Data transaksi tetap tersimpan sebagai snapshot jika diperlukan untuk konsistensi PDF.
2. Konfigurasi dokumen tidak bercampur dengan master partner.
3. Numbering dokumen dikelola melalui satu tabel atau service yang jelas.
4. Semua tabel bisnis utama membawa `company_id` jika relevan untuk query tenant.
5. Role/permission harus dipindahkan ke konsep yang lebih eksplisit daripada string bebas.

## Struktur Inti yang Disarankan

### 1. `companies`

Tetap sebagai tenant root.

Field inti:

- `id`
- `name`
- `address`
- `logo`
- timestamps

### 2. `users`

Tetap terhubung ke `companies`.

Field inti:

- `company_id`
- `name`
- `email`
- `password`
- `role`

Rekomendasi:

- ganti `role` string menjadi enum atau permission table jika kebutuhan akses berkembang
- pertahankan `superadmin` hanya jika memang ada global admin lintas tenant

### 3. `customers`

Master customer per company.

Field inti:

- `company_id`
- `name`
- `address`
- `phone`
- `email`

Rekomendasi:

- gunakan nama kolom yang konsisten, misalnya `phone` bukan `no_hp` jika nanti seluruh skema ingin diseragamkan
- sediakan unique constraint per company jika dibutuhkan

### 4. `partners`

Pengganti atau evolusi dari `mitras`.

Pisahkan menjadi dua bagian:

- partner master
- partner document settings

Field partner master:

- `company_id`
- `name`
- `email`
- `address`

Field settings:

- `partner_id`
- `default_offer_number`
- `default_invoice_number`
- `default_delivery_number`
- `default_report_number`
- template paths

Alternatif:

- tetap pakai `mitras`
- tetapi tambah tabel `partner_document_settings`

## Struktur Transaksi

### 5. `quote_headers`

Pengganti atau evolusi dari `penawarans`.

Field inti:

- `company_id`
- `created_by`
- `partner_id` nullable
- `number`
- `quote_date`
- `customer_name_snapshot`
- `customer_company_snapshot`
- `customer_address_snapshot`
- `contract_type`
- `signature_role`
- `notes`
- `subtotal`
- `tax_rate`
- `tax_amount`
- `total`
- `status`
- `approved_by`
- `approved_at`
- `invoice_number_snapshot`
- `invoice_date_snapshot`
- `invoice_sequence_snapshot`

### 6. `quote_items`

Field inti:

- `quote_header_id`
- `name`
- `description`
- `quantity`
- `unit`
- `unit_price`
- `amount`

Rekomendasi:

- pakai `description` alih-alih `rincian` jika ingin bahasa internal seragam
- jangan bergantung pada enum unit yang terlalu sempit bila bisnis berkembang

### 7. `purchase_orders`

Field inti:

- `company_id`
- `quote_header_id`
- `file_path`
- `file_name`
- `number`
- `po_date`
- `uploaded_by`
- `uploaded_at`

### 8. `invoices`

Field inti:

- `company_id`
- `quote_header_id`
- `purchase_order_id` nullable
- `number`
- `invoice_date`
- `sequence`
- `total`
- `payment_status`
- `payment_date`
- `created_by`

### 9. `tax_invoices`

Field inti:

- `company_id`
- `invoice_id`
- `file_path`
- `file_name`
- `uploaded_by`
- `uploaded_at`
- `payment_status` jika memang mau tetap dipakai di level dokumen pajak
- `payment_date` jika alurnya masih dibutuhkan

### 10. `delivery_notes`

Field inti:

- `company_id`
- `invoice_id`
- `number`
- `date`
- `receiver_name`
- `receiver_phone`
- `sender_name`
- `sender_title`
- `sender_address`
- `manual_city_date`
- `created_by`

### 11. `acceptance_reports`

Field inti:

- `company_id`
- `invoice_id`
- `number`
- `date`
- `subject`
- `closing_note`
- `manual_city_date`
- `created_by`

### 12. `retail_invoices`

Pengganti atau evolusi dari `nota_tokos`.

Field inti:

- `company_id`
- `created_by`
- `number`
- `date`
- `customer_name_snapshot`
- `customer_email_snapshot`
- `address_snapshot`
- `notes`
- `subtotal`
- `tax_rate`
- `tax_amount`
- `total`
- `payment_status`
- `payment_date`

### 13. `retail_invoice_items`

Field inti:

- `retail_invoice_id`
- `name`
- `quantity`
- `unit`
- `unit_price`
- `amount`

## Tabel Konfigurasi

### 14. `document_series`

Satu tempat untuk numbering dokumen.

Contoh field:

- `company_id`
- `document_type`
- `prefix`
- `year_mode`
- `month_mode`
- `counter`
- `padding`
- `suffix`

### 15. `document_templates`

Jika template dokumen ingin dipisah dari partner.

Field contoh:

- `company_id`
- `document_type`
- `name`
- `file_path`
- `is_default`

Catatan implementasi:

- `file_path` bisa dipakai sebagai nama view Blade, misalnya `invoice.pdf`
- bila nanti butuh file fisik, kolom ini tetap bisa dipetakan ke path storage atau path template lain
- satu company bisa punya banyak template per `document_type`, tetapi hanya satu yang ditandai `is_default`

## Migration Order yang Disarankan

1. buat tabel konfigurasi baru
2. buat tabel inti baru berdampingan dengan tabel lama
3. backfill data lama ke struktur baru
4. update controller/model secara bertahap
5. verifikasi PDF/export per modul
6. pindahkan UI ke table baru satu per satu
7. setelah stabil, drop tabel lama

## Risiko Utama

- numbering dokumen bisa berubah kalau migrasi counter tidak dipetakan
- PDF bisa bergeser layout kalau snapshot field tidak konsisten
- relasi 1:1 invoice-ke-dokumen harus dijaga agar tidak menggandakan data
- field snapshot customer/partner harus diputuskan sejak awal: tetap disimpan atau diganti referensi penuh

## Keputusan yang Perlu Dipastikan Sebelum Implementasi

1. apakah `superadmin` tetap global atau diganti permission granular
2. apakah `mitras` tetap satu tabel atau dipecah menjadi partner + settings
3. apakah numbering dokumen mau central atau per company / per partner
4. apakah data lama harus di-backfill penuh atau hanya transaksi baru yang pindah ke schema baru
