<?php

namespace Database\Seeders;

use App\Enums\BannerTag;
use App\Enums\ContentCategory;
use App\Enums\ProductCategory;
use App\Models\Article;
use App\Models\Banner;
use App\Models\Faq;
use App\Models\News;
use App\Models\Product;
use App\Models\Tip;
use App\Models\User;
use Database\Seeders\Concerns\GeneratesPlaceholderImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DummyDataSeeder extends Seeder
{
    use GeneratesPlaceholderImage;

    public function run(): void
    {
        $this->truncateContentTables();

        $this->seedTips();
        $this->seedBanners();
        $this->seedNews();
        $this->seedArticles();
        $this->seedProducts();
        $this->seedFaqs();
        $this->seedDemoUsers();
    }

    private function truncateContentTables(): void
    {
        Schema::disableForeignKeyConstraints();

        if (Schema::hasTable('bookmarks')) {
            DB::table('bookmarks')->truncate();
        }

        if (Schema::hasTable('media')) {
            DB::table('media')->truncate();
        }

        if (Schema::hasTable('tips')) {
            Tip::truncate();
        }

        if (Schema::hasTable('banners')) {
            Banner::truncate();
        }

        if (Schema::hasTable('news')) {
            News::truncate();
        }

        if (Schema::hasTable('articles')) {
            Article::truncate();
        }

        if (Schema::hasTable('products')) {
            Product::truncate();
        }

        if (Schema::hasTable('faqs')) {
            Faq::truncate();
        }

        Schema::enableForeignKeyConstraints();
    }

    private function seedTips(): void
    {
        $tips = [
            'Sikat gigi 2 kali sehari selama 2 menit — pagi setelah sarapan dan malam sebelum tidur.',
            'Ganti sikat gigi setiap 3-4 bulan atau setelah sembuh dari sakit.',
            'Gunakan benang gigi minimal sekali sehari, terutama sebelum tidur.',
            'Kurangi makanan dan minuman manis untuk mencegah gigi berlubang.',
            'Kunjungi dokter gigi rutin setiap 6 bulan untuk pemeriksaan dan scaling.',
            'Minum air putih setelah makan membantu membersihkan sisa makanan di mulut.',
            'Jangan menyikat gigi terlalu keras agar email dan gusi tidak rusak.',
        ];

        foreach ($tips as $index => $content) {
            $tip = Tip::create([
                'content' => $content,
                'is_active' => $index === 0,
            ]);

            $this->attachPlaceholderMedia($tip, 'tip_image', 'tip-'.($index + 1), 400, 400, 'FFE0B2');
        }
    }

    private function seedBanners(): void
    {
        $banners = [
            [
                'title' => 'Cara Sikat Gigi yang Benar',
                'subtitle' => 'Jaga gigi sehat setiap hari!',
                'tag' => BannerTag::Edukasi,
                'link_url' => 'https://youtube.com/watch?v=dental-brush',
                'is_active' => true,
                'color' => 'FF8C00',
            ],
            [
                'title' => 'Promo Diskon Perawatan 20%',
                'subtitle' => 'Berlaku hingga akhir bulan ini',
                'tag' => BannerTag::Promo,
                'link_url' => null,
                'is_active' => true,
                'color' => 'E65100',
            ],
            [
                'title' => 'Gratis Konsultasi Pertama!',
                'subtitle' => 'Mulai perjalanan menuju gigi sehat',
                'tag' => BannerTag::Info,
                'link_url' => 'https://wa.me/628123456789',
                'is_active' => true,
                'color' => 'FB8C00',
            ],
            [
                'title' => 'Edukasi: Pentingnya Scaling Gigi',
                'subtitle' => 'Bersihkan karang gigi secara rutin',
                'tag' => BannerTag::Edukasi,
                'link_url' => null,
                'is_active' => true,
                'color' => 'F57C00',
            ],
            [
                'title' => 'Banner Nonaktif (Draft)',
                'subtitle' => 'Tidak tampil di aplikasi',
                'tag' => BannerTag::Info,
                'link_url' => null,
                'is_active' => false,
                'color' => 'BDBDBD',
            ],
        ];

        foreach ($banners as $index => $data) {
            $banner = Banner::create(collect($data)->except('color')->all());
            $this->attachPlaceholderMedia($banner, 'banner_image', 'banner-'.($index + 1), 900, 400, $data['color']);
        }
    }

    private function seedNews(): void
    {
        $items = [
            [
                'title' => 'Klinik Buka Cabang Baru di Jakarta Selatan',
                'category' => ContentCategory::Klinik,
                'summary' => 'Cabang baru dengan fasilitas modern dan dokter berpengalaman.',
                'content' => $this->richContent('Klinik Kami Hadir Lebih Dekat', 'Kini kami hadir di Jakarta Selatan dengan ruang tunggu nyaman, peralatan digital radiografi, dan tim dokter spesialis.'),
                'is_published' => true,
                'views' => 210,
                'likes' => 45,
            ],
            [
                'title' => 'Jam Operasional Selama Libur Lebaran',
                'category' => ContentCategory::Klinik,
                'summary' => 'Klinik buka setengah hari selama periode libur.',
                'content' => $this->richContent('Informasi Libur', 'Selama libur lebaran, klinik tetap buka pukul 08:00–14:00. Layanan darurat gigi tetap tersedia.'),
                'is_published' => true,
                'views' => 180,
                'likes' => 22,
            ],
            [
                'title' => 'Layanan Baru: Pemutihan Gigi Laser',
                'category' => ContentCategory::Teknologi,
                'summary' => 'Teknologi laser terbaru, aman dan hasil lebih cepat.',
                'content' => $this->richContent('Pemutihan Laser', 'Prosedur pemutihan gigi kini lebih nyaman dengan teknologi laser yang diminimalkan panas pada jaringan gigi.'),
                'is_published' => true,
                'views' => 340,
                'likes' => 88,
            ],
            [
                'title' => 'Dokter Gigi Anak Baru Bergabung',
                'category' => ContentCategory::Klinik,
                'summary' => 'drg. Sarah Aulia, Sp.KGA siap menangani pasien anak.',
                'content' => $this->richContent('Spesialis Anak', 'Pendekatan ramah dan ruang bermain kecil membuat anak lebih nyaman saat pemeriksaan.'),
                'is_published' => true,
                'views' => 156,
                'likes' => 31,
            ],
            [
                'title' => 'Hari Kesehatan Gigi Nasional: Penyuluhan Gratis',
                'category' => ContentCategory::KesehatanGigi,
                'summary' => 'Penyuluhan gratis di 5 sekolah dasar se-Jakarta.',
                'content' => $this->richContent('Penyuluhan Sekolah', 'Tim dokter kami mengajarkan cara sikat gigi yang benar dan pentingnya pemeriksaan rutin.'),
                'is_published' => true,
                'views' => 95,
                'likes' => 19,
            ],
            [
                'title' => 'Teknologi Scan 3D untuk Perencanaan Implan',
                'category' => ContentCategory::Teknologi,
                'summary' => 'Perencanaan implan gigi lebih presisi dengan scan 3D.',
                'content' => $this->richContent('Scan 3D', 'Hasil scan membantu dokter merencanakan posisi implan secara akurat sebelum prosedur.'),
                'is_published' => true,
                'views' => 275,
                'likes' => 54,
            ],
            [
                'title' => 'Tips Menjaga Gigi Tetap Putih Alami',
                'category' => ContentCategory::KesehatanGigi,
                'summary' => 'Kebiasaan sehari-hari untuk senyum lebih cerah.',
                'content' => $this->richContent('Gigi Putih Alami', 'Kurangi kopi dan teh pekat, rutin scaling, dan gunakan pasta gigi yang sesuai rekomendasi dokter.'),
                'is_published' => true,
                'views' => 420,
                'likes' => 102,
            ],
            [
                'title' => 'Program Asuransi Gigi Kerja Sama Baru',
                'category' => ContentCategory::Umum,
                'summary' => 'Klaim perawatan gigi kini lebih mudah dengan 3 provider asuransi.',
                'content' => $this->richContent('Asuransi Gigi', 'Pasien dapat menggunakan BPJS Mandiri dan dua asuransi swasta mitra kami.'),
                'is_published' => true,
                'views' => 130,
                'likes' => 17,
            ],
            [
                'title' => '[DRAFT] Open House Klinik Akhir Tahun',
                'category' => ContentCategory::Klinik,
                'summary' => 'Belum dipublish — acara open house masih direncanakan.',
                'content' => '<p>Konten draft untuk acara open house.</p>',
                'is_published' => false,
                'views' => 0,
                'likes' => 0,
            ],
        ];

        foreach ($items as $index => $item) {
            $news = News::create([
                'title' => $item['title'],
                'category' => $item['category'],
                'summary' => $item['summary'],
                'content' => $item['content'],
                'is_published' => $item['is_published'],
                'view_count' => $item['views'],
                'like_count' => $item['likes'],
            ]);

            if ($item['is_published']) {
                $this->attachPlaceholderMedia($news, 'cover_image', 'news-'.($index + 1), 640, 360, '4FC3F7');
            }
        }
    }

    private function seedArticles(): void
    {
        $items = [
            ['Pentingnya Membersihkan Karang Gigi Secara Rutin', ContentCategory::KesehatanGigi, 'Karang gigi dapat merusak jaringan penyangga gigi jika dibiarkan.', 520, 98],
            ['Mitos dan Fakta Seputar Cabut Gigi', ContentCategory::Edukasi, 'Cabut gigi modern minim rasa sakit berkat anestesi lokal.', 410, 76],
            ['Cara Mengatasi Gigi Ngilu karena Sensitif', ContentCategory::KesehatanGigi, 'Pasta gigi khusus dan hindari makanan terlalu panas atau dingin.', 380, 64],
            ['Mengenal Kawat Gigi dan Perawatannya', ContentCategory::Edukasi, 'Behel memperbaiki gigitan sekaligus estetika senyum.', 290, 55],
            ['Dampak Merokok pada Kesehatan Rongga Mulut', ContentCategory::KesehatanGigi, 'Merokok memperburuk bau mulut, penyakit gusi, dan perubahan warna gigi.', 350, 41],
            ['Kapan Anak Harus Pertama Kali ke Dokter Gigi?', ContentCategory::Edukasi, 'Idealnya saat gigi pertama tumbuh atau usia 1 tahun.', 440, 87],
            ['Makanan Baik untuk Kesehatan Gigi', ContentCategory::KesehatanGigi, 'Sayur, buah, dan produk susu rendah gula mendukung gigi kuat.', 610, 120],
            ['Perbedaan Scaling dan Bleaching', ContentCategory::Edukasi, 'Scaling membersihkan karang gigi, bleaching memutihkan warna gigi.', 275, 48],
            ['Cara Merawat Gigi Pasca Cabut', ContentCategory::KesehatanGigi, 'Jangan berkumur keras, hindari makanan keras, dan ikuti obat sesuai resep.', 198, 33],
            ['[DRAFT] Artikel Orthodonti Terbaru', ContentCategory::Edukasi, 'Artikel masih dalam tahap review dokter.', 0, 0, false],
        ];

        foreach ($items as $index => $item) {
            $published = $item[5] ?? true;

            $article = Article::create([
                'title' => $item[0],
                'category' => $item[1],
                'content' => $this->richContent($item[0], $item[2]),
                'view_count' => $item[3],
                'like_count' => $item[4],
                'is_published' => $published,
            ]);

            if ($published) {
                $this->attachPlaceholderMedia($article, 'cover_image', 'article-'.($index + 1), 640, 360, '81C784');
            }
        }
    }

    private function seedProducts(): void
    {
        $items = [
            [
                'name' => 'Sikat Gigi Soft Bristle',
                'category' => ProductCategory::PerawatanGigi,
                'description' => 'Sikat gigi bulu lembut untuk gusi sensitif dan email tipis.',
                'benefits' => ['Tidak melukai gusi', 'Membersihkan plak efektif', 'Nyaman untuk pemakaian harian'],
                'usage_instructions' => "1. Basahi sikat gigi\n2. Oleskan pasta secukupnya\n3. Sikat 2 menit dengan gerakan melingkar\n4. Bilas hingga bersih",
                'doctor_tips' => 'Ganti sikat gigi setiap 3 bulan atau setelah sakit flu.',
                'dosage' => '2 kali sehari',
            ],
            [
                'name' => 'Pasta Gigi Sensitif (Potassium Nitrate)',
                'category' => ProductCategory::ProdukGigi,
                'description' => 'Formula khusus meredakan ngilu pada gigi sensitif.',
                'benefits' => ['Meredakan sensitivitas', 'Melindungi email', 'Membantu mencegah karies'],
                'usage_instructions' => 'Gunakan sebesar biji jagung, sikat perlahan seluruh permukaan gigi.',
                'doctor_tips' => 'Hasil optimal biasanya terlihat setelah 2 minggu pemakaian rutin.',
                'dosage' => '2 kali sehari',
            ],
            [
                'name' => 'Obat Kumur Antiseptik',
                'category' => ProductCategory::ProdukGigi,
                'description' => 'Cairan kumur antibakteri untuk gusi sehat dan napas segar.',
                'benefits' => ['Mengurangi bakteri', 'Meredakan peradangan ringan', 'Menyegarkan napas'],
                'usage_instructions' => 'Kumur 15 ml selama 30 detik, jangan dibilas air setelahnya.',
                'doctor_tips' => 'Hindari pemakaian jangka panjang tanpa anjuran dokter.',
                'dosage' => '2 kali sehari',
            ],
            [
                'name' => 'Benang Gigi (Dental Floss)',
                'category' => ProductCategory::TipsPerawatan,
                'description' => 'Membersihkan sela gigi yang tidak terjangkau sikat gigi.',
                'benefits' => ['Mencegah karang gigi', 'Mengurangi bau mulut', 'Menjaga gusi sehat'],
                'usage_instructions' => 'Gesek benang membentuk huruf C di setiap celah gigi.',
                'doctor_tips' => 'Lakukan sebelum tidur untuk hasil terbaik.',
                'dosage' => '1 kali sehari',
            ],
            [
                'name' => 'Pembersih Lidah (Tongue Scraper)',
                'category' => ProductCategory::PerawatanGigi,
                'description' => 'Alat pembersih lidah untuk mengurangi bakteri penyebab bau mulut.',
                'benefits' => ['Mengurangi bau mulut', 'Meningkatkan sensitivitas rasa', 'Mendukung kesehatan mulut'],
                'usage_instructions' => 'Bersihkan lidah dari belakang ke depan dengan lembut setiap pagi.',
                'doctor_tips' => 'Jangan menekan terlalu keras agar lidah tidak iritasi.',
                'dosage' => '1 kali sehari',
            ],
            [
                'name' => 'Gel Pereda Nyeri Tumbuh Gigi Anak',
                'category' => ProductCategory::ProdukGigi,
                'description' => 'Gel topikal untuk meredakan nyeri saat fase tumbuh gigi pada bayi.',
                'benefits' => ['Meredakan nyeri gusi', 'Mudah diaplikasikan', 'Aman untuk bayi sesuai panduan'],
                'usage_instructions' => 'Oleskan tipis pada gusi yang bengkak, maksimal 3-4 kali sehari.',
                'doctor_tips' => 'Konsultasikan ke dokter anak jika demam disertai rewel berlebihan.',
                'dosage' => 'Sesuai kebutuhan',
            ],
            [
                'name' => 'Sediaan Fluoride Topikal',
                'category' => ProductCategory::TipsPerawatan,
                'description' => 'Lapisan fluoride untuk memperkuat email dan mencegah karies.',
                'benefits' => ['Memperkuat email', 'Mencegah gigi berlubang', 'Cocok untuk risiko karies tinggi'],
                'usage_instructions' => 'Biasanya diaplikasikan dokter gigi di klinik setiap 6 bulan.',
                'doctor_tips' => 'Tidak menggantikan sikat gigi harian.',
                'dosage' => 'Sesuai resep dokter',
            ],
            [
                'name' => 'Pasta Gigi Whitening',
                'category' => ProductCategory::ProdukGigi,
                'description' => 'Pasta gigi dengan partikel halus untuk membantu mengangkat noda permukaan.',
                'benefits' => ['Membantu mengangkat noda kopi/teh', 'Menjaga kebersihan harian', 'Menyegarkan napas'],
                'usage_instructions' => 'Sikat 2 menit, hindari menyikat terlalu keras.',
                'doctor_tips' => 'Bukan pengganti prosedur bleaching di klinik.',
                'dosage' => '2 kali sehari',
            ],
        ];

        foreach ($items as $index => $item) {
            $product = Product::create([
                'name' => $item['name'],
                'category' => $item['category'],
                'description' => $item['description'],
                'benefits' => $item['benefits'],
                'usage_instructions' => $item['usage_instructions'],
                'doctor_tips' => $item['doctor_tips'],
                'dosage' => $item['dosage'],
                'is_active' => true,
            ]);

            $this->attachPlaceholderMedia($product, 'product_image', 'product-'.($index + 1), 600, 600, 'FFB74D');
        }

        Product::create([
            'name' => 'Produk Nonaktif (Contoh Draft)',
            'slug' => 'produk-nonaktif-contoh',
            'category' => ProductCategory::ProdukGigi,
            'description' => 'Produk contoh yang tidak tampil di public API.',
            'benefits' => ['Contoh'],
            'usage_instructions' => '-',
            'doctor_tips' => '-',
            'dosage' => '-',
            'is_active' => false,
        ]);
    }

    private function seedFaqs(): void
    {
        $faqs = [
            ['Bagaimana cara membuat janji konsultasi?', 'Hubungi kami via WhatsApp di nomor yang tertera di aplikasi atau datang langsung ke klinik pada jam operasional.', 1],
            ['Apakah layanan pemutihan gigi aman?', 'Ya, prosedur pemutihan di klinik kami menggunakan bahan dan peralatan yang telah teruji, serta dilakukan oleh tenaga profesional.', 2],
            ['Berapa lama proses pemasangan behel?', 'Pemasangan awal biasanya 1–2 jam. Kontrol rutin dilakukan setiap 4–6 minggu.', 3],
            ['Apakah anak usia 3 tahun perlu ke dokter gigi?', 'Disarankan sejak gigi pertama tumbuh atau paling lambat usia 1 tahun untuk pemeriksaan awal.', 4],
            ['Bagaimana cara menyimpan artikel favorit?', 'Login sebagai user, buka detail artikel, lalu gunakan fitur simpan/bookmark di profil.', 5],
            ['Apakah ada layanan gigi darurat?', 'Ya, kami menyediakan slot darurat terbatas setiap hari. Hubungi klinik untuk konfirmasi ketersediaan.', 6],
            ['Metode pembayaran apa yang diterima?', 'Tunai, kartu debit/kredit, QRIS, dan beberapa provider asuransi mitra.', 7],
            ['Apakah scaling gigi menyakitkan?', 'Umumnya minim nyeri. Dokter dapat memberikan anestesi topikal jika diperlukan.', 8],
        ];

        foreach ($faqs as $faq) {
            Faq::create([
                'question' => $faq[0],
                'answer' => $faq[1],
                'sort_order' => $faq[2],
                'is_active' => true,
            ]);
        }
    }

    private function seedDemoUsers(): void
    {
        $sarah = User::firstOrCreate(
            ['email' => 'sarah@clinic.com'],
            [
                'name' => 'Sarah Aulia',
                'password' => Hash::make('password'),
            ]
        );

        if (! $sarah->hasRole('user')) {
            $sarah->assignRole('user');
        }

        $this->attachPlaceholderMedia($sarah, 'avatar', 'avatar-sarah', 300, 300, 'FF8C00');

        $articleSlugs = Article::where('is_published', true)
            ->orderByDesc('like_count')
            ->take(3)
            ->pluck('id');

        $sarah->bookmarkedArticles()->syncWithoutDetaching($articleSlugs->all());
    }

    private function richContent(string $title, string $paragraph): string
    {
        return <<<HTML
        <h2>{$title}</h2>
        <p><strong>Penting:</strong> {$paragraph}</p>
        <p><em>Informasi ini bersifat edukasi</em> dan tidak menggantikan konsultasi langsung dengan dokter gigi.</p>
        <ul>
            <li>Jaga kebersihan mulut setiap hari</li>
            <li>Kurangi makanan manis berlebih</li>
            <li>Rutin periksa ke dokter gigi</li>
        </ul>
        HTML;
    }
}
