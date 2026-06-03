<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Tip;
use App\Models\News;
use App\Models\Article;
use App\Models\Product;
use App\Models\Banner;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Buat Admin User
        User::firstOrCreate([
            'email' => 'admin@clinic.com'
        ], [
            'name' => 'Administrator',
            'password' => Hash::make('password'),
        ]);

        // ==========================================
        // 1. DATA TIPS HARIAN
        // ==========================================
        $tips = [
            'Sikat gigi 2 kali sehari selama 2 menit setiap pagi setelah sarapan dan malam sebelum tidur.',
            'Ganti sikat gigi Anda setiap 3-4 bulan sekali atau setelah Anda sembuh dari sakit.',
            'Gunakan benang gigi (dental floss) setidaknya sekali sehari untuk membersihkan sela-sela gigi.',
            'Kurangi konsumsi makanan dan minuman manis untuk mencegah gigi berlubang.',
            'Kunjungi dokter gigi secara rutin setiap 6 bulan sekali untuk pemeriksaan dan pembersihan karang gigi.',
            'Perbanyak minum air putih setelah makan untuk membantu membersihkan sisa makanan di mulut.',
            'Hindari menyikat gigi terlalu keras karena dapat merusak email dan melukai gusi.'
        ];

        foreach ($tips as $index => $tip) {
            Tip::create([
                'content' => $tip,
                'is_active' => $index < 3 // 3 tip pertama aktif
            ]);
        }

        // ==========================================
        // 2. DATA BANNERS
        // ==========================================
        Banner::create(['title' => 'Promo Diskon Perawatan 20%', 'is_active' => true]);
        Banner::create(['title' => 'Cara Sikat Gigi yang Benar (Video)', 'link_url' => 'https://youtube.com', 'is_active' => true]);
        Banner::create(['title' => 'Gratis Konsultasi Pertama!', 'link_url' => 'https://wa.me/628123456789', 'is_active' => true]);
        Banner::create(['title' => 'Pentingnya Merawat Gigi Susu', 'is_active' => false]);

        // ==========================================
        // 3. DATA NEWS (BERITA TERKINI)
        // ==========================================
        $news_data = [
            ['Klinik Kami Buka Cabang Baru di Jakarta Selatan!', 'Kini kami hadir lebih dekat dengan Anda. Kunjungi cabang baru kami di Jakarta Selatan dengan fasilitas yang lebih modern...'],
            ['Jam Operasional Selama Libur Lebaran', 'Mohon perhatian, selama libur lebaran klinik tetap buka setengah hari dari pukul 08:00 hingga 14:00.'],
            ['Layanan Baru: Pemutihan Gigi Laser', 'Klinik kami kini menghadirkan layanan pemutihan gigi dengan teknologi laser terbaru yang aman dan cepat.'],
            ['Dokter Gigi Spesialis Anak Baru Telah Bergabung', 'Selamat datang drg. Sarah, Sp.KGA yang akan menangani pasien anak dengan pendekatan ramah dan menyenangkan.'],
            ['Peringatan Hari Kesehatan Gigi Nasional', 'Dalam rangka menyambut hari kesehatan gigi nasional, kami mengadakan penyuluhan gratis di beberapa sekolah dasar.'],
        ];

        foreach ($news_data as $news) {
            News::create([
                'title' => $news[0],
                'slug' => Str::slug($news[0]),
                'content' => $news[1],
                'is_published' => true
            ]);
        }

        // ==========================================
        // 4. DATA ARTICLES (EDUKASI)
        // ==========================================
        $articles_data = [
            ['Pentingnya Membersihkan Karang Gigi Secara Rutin', 'Karang gigi yang menumpuk tidak hanya menyebabkan bau mulut, tetapi juga dapat merusak jaringan pendukung gigi...'],
            ['Mitos dan Fakta Seputar Cabut Gigi', 'Banyak yang takut cabut gigi karena mitos yang beredar. Faktanya, cabut gigi saat ini sangat minim rasa sakit...'],
            ['Cara Mengatasi Gigi Ngilu Karena Sensitif', 'Gigi sensitif sering disebabkan oleh penurunan gusi atau penipisan email. Gunakan pasta gigi khusus untuk meredakannya...'],
            ['Mengenal Behel Gigi dan Perawatannya', 'Kawat gigi atau behel bukan hanya untuk estetika, tapi juga memperbaiki fungsi kunyah. Perawatannya membutuhkan ketelitian ekstra...'],
            ['Dampak Merokok Pada Kesehatan Rongga Mulut', 'Merokok tidak hanya berdampak pada paru-paru, tapi juga menyebabkan perubahan warna gigi, penyakit gusi, dan bau mulut...'],
            ['Kapan Anak Harus Mulai ke Dokter Gigi?', 'Anak sebaiknya diajak ke dokter gigi sejak gigi pertamanya tumbuh atau maksimal saat usianya mencapai 1 tahun...'],
        ];

        foreach ($articles_data as $index => $article) {
            Article::create([
                'title' => $article[0],
                'slug' => Str::slug($article[0]),
                'content' => $article[1],
                'view_count' => rand(50, 500),
                'like_count' => rand(10, 100),
                'is_published' => true
            ]);
        }

        // ==========================================
        // 5. DATA PRODUK (KATALOG EDUKASI OBAT)
        // ==========================================
        $products_data = [
            [
                'name' => 'Obat Kumur Antiseptik (Chlorhexidine)',
                'description' => 'Cairan kumur antibakteri yang sangat efektif untuk mengatasi peradangan gusi (gingivitis) dan mempercepat penyembuhan pasca operasi gigi.',
                'usage_instructions' => 'Kumur dengan 10-15 ml larutan selama 30-60 detik. Jangan dibilas dengan air setelahnya dan hindari makan/minum selama 30 menit.',
                'dosage' => '2 kali sehari (Pagi & Malam)'
            ],
            [
                'name' => 'Pasta Gigi Sensitif (Potassium Nitrate)',
                'description' => 'Pasta gigi dengan formula khusus untuk melapisi saraf gigi yang terbuka, meredakan rasa ngilu akibat makanan/minuman dingin, panas, atau asam.',
                'usage_instructions' => 'Oleskan sebesar biji jagung pada sikat gigi berbulu lembut. Sikat perlahan pada seluruh permukaan gigi.',
                'dosage' => 'Minimal 2 kali sehari secara rutin'
            ],
            [
                'name' => 'Gel Pereda Nyeri Tumbuh Gigi (Teething Gel)',
                'description' => 'Gel khusus untuk bayi yang sedang mengalami proses tumbuh gigi (teething) untuk meredakan rasa sakit dan rewel.',
                'usage_instructions' => 'Cuci tangan hingga bersih, oleskan sedikit gel pada jari telunjuk, lalu pijat lembut pada area gusi bayi yang meradang.',
                'dosage' => 'Maksimal 3-4 kali sehari sesuai kebutuhan'
            ],
            [
                'name' => 'Benang Gigi (Dental Floss)',
                'description' => 'Benang berlapis lilin (waxed) untuk membersihkan sisa makanan dan plak di celah sempit antar gigi yang tidak terjangkau sikat gigi biasa.',
                'usage_instructions' => 'Lilitkan pada jari, gesek perlahan pada celah gigi membentuk huruf C agar plak terangkat tanpa melukai gusi.',
                'dosage' => '1 kali sehari (Disarankan sebelum tidur)'
            ],
            [
                'name' => 'Obat Pereda Nyeri Pasca Cabut Gigi (Asam Mefenamat)',
                'description' => 'Obat analgesik untuk meredakan nyeri sedang hingga berat, sering diresepkan setelah prosedur pencabutan gigi atau operasi impaksi.',
                'usage_instructions' => 'Diminum sesudah makan untuk menghindari iritasi lambung. Hanya diminum jika terasa nyeri.',
                'dosage' => '1 tablet 500mg, 3 kali sehari (Sesuai resep)'
            ],
            [
                'name' => 'Obat Sariawan (Aloclair Plus / Hyaluronic Acid)',
                'description' => 'Cairan kental atau spray yang membentuk lapisan pelindung di atas sariawan, sehingga mengurangi rasa perih secara instan dan mempercepat penyembuhan.',
                'usage_instructions' => 'Teteskan atau semprotkan langsung menutupi area sariawan. Diamkan 2 menit agar terbentuk lapisan pelindung.',
                'dosage' => '3-4 kali sehari'
            ]
        ];

        foreach ($products_data as $product) {
            Product::create([
                'name' => $product['name'],
                'slug' => Str::slug($product['name']),
                'description' => $product['description'],
                'usage_instructions' => $product['usage_instructions'],
                'dosage' => $product['dosage'],
                'is_active' => true
            ]);
        }
    }
}