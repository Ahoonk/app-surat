<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Company::updateOrCreate(
            ['name' => 'PT Aldera Saddatech Karya'],
            [
                'address' => 'Link. Ciwaduk Cilik No. 02 RT/RW 08/04, Kelurahan Ciwaduk, Kecamatan Cilegon, Kota Cilegon, Provinsi Banten',
                'logo' => null,
                'settings' => [
                    'company_name' => 'Aldera Saddatech Karya',
                    'home_seo_title' => 'Aldera Saddatech Karya | Company Profile',
                    'home_seo_description' => 'Aldera Saddatech Karya membantu perusahaan membangun website, sistem internal, dan layanan digital yang stabil, aman, serta mudah dikembangkan.',
                    'home_seo_keywords' => 'Aldera Saddatech Karya, company profile, perusahaan IT, laravel, inertia, vue, tailwind',
                    'tagline' => 'Solusi Digital untuk Perusahaan Modern',
                    'hero_title' => 'Solusi Digital Untuk Kebutuhan Perusahaan Anda',
                    'hero_description' => 'Aldera Saddatech Karya membantu perusahaan membangun website, sistem internal, dan layanan digital yang stabil, aman, serta mudah dikembangkan.',
                    'profile_title' => 'Partner teknologi yang memahami kebutuhan bisnis anda',
                    'profile_description' => 'Kami merancang dan mengembangkan solusi digital untuk membantu perusahaan bekerja lebih efisien, mengambil keputusan lebih cepat, dan memberi pengalaman yang lebih baik bagi pelanggan maupun tim internal.',
                    'vision_title' => 'Visi',
                    'vision_description' => 'Menjadi mitra teknologi terpercaya yang membantu perusahaan bertumbuh dengan sistem digital yang cepat, aman, dan berkelanjutan.',
                    'mission_items' => [
                        'Membangun solusi digital yang relevan dengan kebutuhan operasional perusahaan.',
                        'Membangun hubungan jangka panjang dengan klien melalui solusi yang bernilai tambah.',
                        'Mempercepat transformasi bisnis dengan pendekatan yang efisien dan terukur.',
                        'Mendorong pertumbuhan ekonomi digital nasional melalui teknologi.',
                    ],
                    'about_title' => 'About Us',
                    'about_description' => 'Kami percaya sistem perusahaan harus menjadi alat yang memudahkan kerja, bukan sekedar aset visual.',
                    'about_slug' => 'about-us',
                    'about_seo_title' => 'About Us | Aldera Saddatech Karya',
                    'about_seo_description' => 'Kami percaya sistem perusahaan harus menjadi alat yang memudahkan kerja, bukan sekedar aset visual.',
                    'about_seo_keywords' => 'About Us, Aldera Saddatech Karya, company profile, tim teknologi',
                    'clients_slug' => 'clients',
                    'clients_seo_title' => 'Clients | Aldera Saddatech Karya',
                    'clients_seo_description' => 'Daftar client dan partner yang pernah bekerja sama dengan Aldera Saddatech Karya.',
                    'clients_seo_keywords' => 'client, partner, Aldera Saddatech Karya, perusahaan IT',
                    'products_slug' => 'products',
                    'products_seo_title' => 'Products | Aldera Saddatech Karya',
                    'products_seo_description' => 'Produk dan layanan digital yang dikembangkan Aldera Saddatech Karya.',
                    'products_seo_keywords' => 'produk, layanan, software development, Aldera Saddatech Karya',
                    'contact_email' => 'aldera.saddatech.karya@gmail.com',
                    'contact_phone' => '+62 877 7113 8165',
                    'contact_whatsapp' => '+62 877 7113 8165 +62 896 1745 1858',
                    'company_address' => 'Link. Ciwaduk Cilik No. 02 RT/RW 08/04, Kelurahan Ciwaduk, Kecamatan Cilegon, Kota Cilegon, Provinsi Banten',
                    'logo_image_path' => 'site/lnoGM5g2UJLc4EzEDYoN8a7ggUGarpQSYG0JhXbS.png',
                    'hero_image_path' => 'site/sH8G8Bvqng46itEoLFn0RD83g2RPDdS3XDmhlGtY.png',
                    'about_image_path' => null,
                ],
            ]
        );
    }
}
