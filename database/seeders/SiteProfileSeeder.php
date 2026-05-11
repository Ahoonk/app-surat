<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\SiteClient;
use App\Models\SiteProduct;
use Illuminate\Database\Seeder;

class SiteProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $company = Company::firstOrCreate(
            ['name' => 'PT Aldera Saddatech Karya'],
            [
                'address' => 'Link. Ciwaduk Cilik No. 02 RT/RW 08/04, Kelurahan Ciwaduk, Kecamatan Cilegon, Kota Cilegon, Provinsi Banten',
                'logo' => null,
            ]
        );

        $clients = [
            [
                'name' => 'Dinas Perumahan & Permukiman Kota Cilegon',
                'sector' => 'Pemerintah Kota Cilegon',
                'description' => 'Computer and Printer Maintenance',
                'image_path' => 'clients/1RwnOKuBaWZBzlaVejUxZgGbvb6XfcubB6kS9s7O.png',
                'sort_order' => 1,
            ],
            [
                'name' => 'PT Purna Baja Harsco',
                'sector' => 'Steel Mill Service and Scrap Recovery Services, Hot Metal Transport Blast Furnace, Civil and Mechanical business fields',
                'description' => 'Website Management & Network Infrastructure',
                'image_path' => 'clients/IQY5N10OJXBQ3iSkkmXtUPCyEOS6wJz1cImeHOdK.png',
                'sort_order' => 2,
            ],
            [
                'name' => 'PT Krakatau Repair Service Partners',
                'sector' => 'Operate in the Fields of Machine Repair and Maintenance Services',
                'description' => 'procurement of computers and provision of spare parts for computers, laptops and printers',
                'image_path' => 'clients/sHKKvPb7i6iSovWTvn3VOiObdBXykm7TBf0QS9sg.png',
                'sort_order' => 1,
            ],
            [
                'name' => 'Satpol PP Kota Cilegon',
                'sector' => 'Pemerintah Kota Cilegon',
                'description' => 'Computer and Printer Maintenance',
                'image_path' => 'clients/1LAPLQ3BYdDoiCudlhdzXoM8eVNnhwAqr25jQKdX.png',
                'sort_order' => 1,
            ],
            [
                'name' => 'PT  SLV Kota Cilegon',
                'sector' => 'organizing training and certification in the Oil and Gas sector, as well as various other related industrial sectors',
                'description' => 'procurement of computers and provision of spare parts for computers, laptops and printers',
                'image_path' => 'clients/oSYSTFPnM31XL8LSwpIUJSCkX4i26UP0yTmQxyIR.png',
                'sort_order' => 0,
            ],
        ];

        foreach ($clients as $client) {
            SiteClient::updateOrCreate(
                [
                    'company_id' => $company->id,
                    'name' => $client['name'],
                ],
                [
                    ...$client,
                    'company_id' => $company->id,
                ]
            );
        }

        $products = [
            [
                'name' => 'Software Development',
                'description' => 'Aplikasi web internal untuk operasional, dashboard manajemen, dan proses bisnis harian.',
                'features' => [
                    'Pembuatan Website (company profile, e-commerce, portal)',
                    'Web App (Laravel, React, Vue)',
                    'Mobile App (Android/iOS)',
                    'Sistem Internal Perusahaan (ERP, HRIS, CRM)',
                ],
                'image_path' => 'products/Hq5xVLsy0ug1NFqtgEoQbfErh6IpztoYtOIMriI6.png',
                'sort_order' => 0,
            ],
            [
                'name' => 'Keamanan IT (Cybersecurity)',
                'description' => 'Sistem Keamanan Aplikasi atau Website Perusahaan',
                'features' => [
                    'Audit Keamanan Sistem',
                    'Penetration Testing',
                    'Implementasi Firewall & VPN',
                    'Monitoring ancaman (SOC services)',
                ],
                'image_path' => 'products/E5gUk4OIihQuvbaFoAU6CENNQ9SY442Min8D1uKm.png',
                'sort_order' => 0,
            ],
            [
                'name' => 'Cloud & Server Services',
                'description' => 'SIap Melayani Kebutuhan Server dan Database untuk Perusahaan Anda',
                'features' => [
                    'Setup VPS / Cloud Server (AWS, GCP, DigitalOcean)',
                    'Deployment Aplikasi',
                    'Backup & Disaster Recovery',
                    'DevOps (CI/CD, Docker, Kubernetes)',
                ],
                'image_path' => 'products/VtjfvepDQxxaAPvdsPXM41LgwhWW6I0yjDk8TVUL.png',
                'sort_order' => 0,
            ],
            [
                'name' => 'Jaringan Internet & Infrastruktur IT',
                'description' => 'Perusahaan Kami Siap Membantu dalam Pengelolaan Internet di Perusahaan Anda',
                'features' => [
                    'Instalasi & Konfigurasi Jaringan (router, switch, access point)',
                    'Setup Perangkat MikroTik, Ruijie, Cisco, dll',
                    'Manajemen Bandwidth & Keamanan Jaringan',
                    'Monitoring Jaringan (The Dude, dll)',
                    'Maintenance & Troubleshooting',
                ],
                'image_path' => 'products/I4ktyBLdQheB3CYGZlZMMrSaXXcqawfRac8x0cFv.png',
                'sort_order' => 0,
            ],
            [
                'name' => 'Penjualan Produk IT (Hardware & Software)',
                'description' => 'Perusahaan Kami Siap Menyediakan segala Kebutuhan Perangkat IT untuk Perusahaan Anda',
                'features' => [
                    'Jual Perangkat (Router, Switch, Server, PC, Laptop, Printer)',
                    'Lisensi Software',
                    'Bundling dengan Jasa Instalasi',
                ],
                'image_path' => 'products/jkSsOooCFAU7Ykk7hxCiEgOjLGV0AmMqfsGmkbmK.png',
                'sort_order' => 0,
            ],
        ];

        foreach ($products as $product) {
            SiteProduct::updateOrCreate(
                [
                    'company_id' => $company->id,
                    'name' => $product['name'],
                ],
                [
                    ...$product,
                    'company_id' => $company->id,
                ]
            );
        }
    }
}
