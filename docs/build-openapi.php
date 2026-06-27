<?php

/**
 * Generate docs/openapi.yaml with per-endpoint descriptions and response examples.
 * Run: php docs/build-openapi.php
 */

require dirname(__DIR__).'/vendor/autoload.php';

function dumpYaml(mixed $data, int $indent = 0): string
{
    $pad = str_repeat('  ', $indent);
    $out = '';

    if (is_array($data)) {
        $isList = array_is_list($data);

        foreach ($data as $key => $value) {
            if ($isList) {
                if (is_array($value) && array_is_list($value)) {
                    $out .= "{$pad}- ".trim(dumpYaml($value, $indent + 1));
                } elseif (is_array($value)) {
                    $out .= "{$pad}-\n".dumpYaml($value, $indent + 1);
                } else {
                    $out .= "{$pad}- ".yamlScalar($value)."\n";
                }
            } else {
                if (is_array($value)) {
                    $out .= "{$pad}{$key}:\n".dumpYaml($value, $indent + 1);
                } else {
                    $out .= "{$pad}{$key}: ".yamlScalar($value)."\n";
                }
            }
        }

        return $out;
    }

    return $pad.yamlScalar($data)."\n";
}

function yamlScalar(mixed $value): string
{
    if ($value === null) {
        return 'null';
    }
    if (is_bool($value)) {
        return $value ? 'true' : 'false';
    }
    if (is_int($value) || is_float($value)) {
        return (string) $value;
    }
    if (is_string($value)) {
        if (
            $value === '' ||
            preg_match('/^(?:[-+]?\d+(?:\.\d+)?|true|false|null)$/i', $value) ||
            preg_match('/[:\n#|>&*%!@`\{\}\[\],]/', $value) ||
            str_starts_with($value, ' ')
        ) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return $value;
    }

    return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

$ref = fn (string $name): array => ['$ref' => "#/components/schemas/{$name}"];

$jsend = fn (bool $status, ?string $message, mixed $data): array => [
    'status' => $status,
    'message' => $message,
    'data' => $data,
];

$json = fn (string $desc, array $example, ?array $schema = null): array => [
    'description' => $desc,
    'content' => [
        'application/json' => array_filter([
            'schema' => $schema,
            'example' => $example,
        ]),
    ],
];

$unauth = fn (string $context = 'Token tidak dikirim atau sudah tidak valid'): array => $json(
    "401 Unauthorized — {$context}",
    $jsend(false, 'Unauthenticated.', null),
    $ref('JSendError'),
);

$forbidden = fn (string $message, string $context): array => $json(
    "403 Forbidden — {$context}",
    $jsend(false, $message, null),
    $ref('JSendError'),
);

$notFound = fn (string $message, string $context): array => $json(
    "404 Not Found — {$context}",
    $jsend(false, $message, null),
    $ref('JSendError'),
);

$validation = fn (array $errors, string $context): array => $json(
    "422 Unprocessable Entity — {$context}",
    $jsend(false, 'Validasi gagal', ['errors' => $errors]),
    [
        'type' => 'object',
        'properties' => [
            'status' => ['type' => 'boolean', 'example' => false],
            'message' => ['type' => 'string', 'example' => 'Validasi gagal'],
            'data' => ['$ref' => '#/components/schemas/ValidationErrorData'],
        ],
    ],
);

$rateLimit = fn (string $context): array => $json(
    "429 Too Many Requests — {$context}",
    $jsend(false, 'Terlalu banyak permintaan. Coba lagi nanti.', null),
    $ref('JSendError'),
);

$adminAuth = [
    '401' => $unauth('Diperlukan Bearer token admin via POST /v1/admin/login'),
    '403' => $json(
        '403 Forbidden — Token bukan role admin (middleware Spatie)',
        ['message' => 'User does not have the right roles.'],
    ),
];

$image422 = [
    'image' => ['Gambar wajib diunggah.'],
    'image.0' => ['Gambar harus berupa file jpeg, png, jpg, atau gif.'],
];

$banner = [
    'id' => 1,
    'title' => 'Promo Pemeriksaan Gigi Gratis',
    'subtitle' => 'Cek kesehatan gigi Anda bulan ini',
    'tag' => 'promo',
    'link_url' => null,
    'is_active' => true,
    'image_url' => 'https://example.com/storage/8/banner-1.jpg',
    'created_at' => '2026-06-07T04:28:06.000000Z',
    'updated_at' => '2026-06-07T04:28:06.000000Z',
];

$tip = [
    'id' => 1,
    'content' => 'Sikat gigi minimal 2 menit, 2 kali sehari.',
    'is_active' => true,
    'image_url' => 'https://example.com/storage/1/tip-1.jpg',
    'created_at' => '2026-06-07T04:28:06.000000Z',
    'updated_at' => '2026-06-07T04:28:06.000000Z',
];

$news = [
    'id' => 1,
    'title' => 'Teknologi AI dalam Diagnosa Gigi',
    'slug' => 'teknologi-ai-dalam-diagnosa-gigi',
    'category' => 'teknologi',
    'summary' => 'AI membantu dokter gigi mendeteksi karies lebih awal.',
    'content' => '<p>Konten berita lengkap dalam HTML.</p>',
    'view_count' => 42,
    'like_count' => 7,
    'is_published' => true,
    'cover_image_url' => 'https://example.com/storage/2/news-1.jpg',
    'created_at' => '2026-06-07T04:28:06.000000Z',
    'updated_at' => '2026-06-07T04:28:06.000000Z',
];

$article = [
    'id' => 1,
    'title' => 'Cara Menyikat Gigi yang Benar',
    'slug' => 'cara-menyikat-gigi-yang-benar',
    'category' => 'edukasi',
    'content' => '<p>Edukasi artikel dalam HTML.</p>',
    'view_count' => 120,
    'like_count' => 15,
    'is_published' => true,
    'cover_image_url' => 'https://example.com/storage/3/article-1.jpg',
    'created_at' => '2026-06-07T04:28:06.000000Z',
    'updated_at' => '2026-06-07T04:28:06.000000Z',
];

$product = [
    'id' => 1,
    'name' => 'Pasta Gigi Sensitif Pro',
    'slug' => 'pasta-gigi-sensitif-pro',
    'category' => 'produk_gigi',
    'description' => 'Pasta gigi untuk gigi sensitif.',
    'benefits' => ['Meredakan sensitif', 'Melindungi enamel'],
    'usage_instructions' => 'Sikat 2 menit setelah makan.',
    'doctor_tips' => 'Gunakan sikat lembut.',
    'dosage' => null,
    'is_active' => true,
    'product_image_url' => 'https://example.com/storage/5/product-1.jpg',
    'created_at' => '2026-06-07T04:28:06.000000Z',
    'updated_at' => '2026-06-07T04:28:06.000000Z',
];

$faq = [
    'id' => 1,
    'question' => 'Berapa kali sehari harus sikat gigi?',
    'answer' => 'Minimal 2 kali sehari, pagi dan malam.',
    'sort_order' => 1,
    'is_active' => true,
    'created_at' => '2026-06-07T04:28:06.000000Z',
    'updated_at' => '2026-06-07T04:28:06.000000Z',
];

$user = [
    'id' => 2,
    'name' => 'Sarah Aulia',
    'email' => 'sarah@clinic.com',
    'avatar_url' => 'https://example.com/storage/10/avatar.jpg',
    'email_verified_at' => null,
    'created_at' => '2026-06-07T04:28:06.000000Z',
    'updated_at' => '2026-06-07T04:28:06.000000Z',
];

$paginated = fn (array $items, int $perPage = 15): array => [
    'current_page' => 1,
    'data' => $items,
    'first_page_url' => 'http://127.0.0.1:8000/api/v1/example?page=1',
    'from' => 1,
    'last_page' => 1,
    'last_page_url' => 'http://127.0.0.1:8000/api/v1/example?page=1',
    'links' => [],
    'next_page_url' => null,
    'path' => 'http://127.0.0.1:8000/api/v1/example',
    'per_page' => $perPage,
    'prev_page_url' => null,
    'to' => count($items),
    'total' => count($items),
];

$listModeDoc = <<<'TXT'
**Mode response (FE yang tentukan):**
- Tanpa `page` → `data` berupa **array** semua item (sama seperti `/public/banners`)
- Dengan `page` → `data` berupa **objek pagination** Laravel

**Query pagination (opsional):**
- `page` — nomor halaman (wajib diisi FE jika mau pagination)
- `per_page` — item per halaman (1-50, default 15)
TXT;

$publicListModeDoc = $listModeDoc."\n\n**Alternatif tanpa pagination:**\n- `limit` — ambil N item sebagai array (max 20), tanpa perlu `page`";

$listResponse = fn (string $desc, string $message, array $item): array => [
    'description' => $desc,
    'content' => [
        'application/json' => [
            'examples' => [
                'tanpaPage_array' => [
                    'summary' => 'Tanpa ?page — data array (default)',
                    'value' => $jsend(true, $message, [$item]),
                ],
                'denganPage_pagination' => [
                    'summary' => 'Dengan ?page=1&per_page=15 — data pagination',
                    'value' => $jsend(true, $message, $paginated([$item])),
                ],
            ],
        ],
    ],
];

$publicListParams = [
    ['$ref' => '#/components/parameters/CategoryContent'],
    ['$ref' => '#/components/parameters/SearchParam'],
    ['$ref' => '#/components/parameters/LimitParam'],
    ['$ref' => '#/components/parameters/PageParam'],
    ['$ref' => '#/components/parameters/PerPageParam'],
];

$publicProductListParams = [
    ['$ref' => '#/components/parameters/CategoryProduct'],
    ['$ref' => '#/components/parameters/SearchParam'],
    ['$ref' => '#/components/parameters/LimitParam'],
    ['$ref' => '#/components/parameters/PageParam'],
    ['$ref' => '#/components/parameters/PerPageParam'],
];

$adminListParams = [
    ['$ref' => '#/components/parameters/PageParam'],
    ['$ref' => '#/components/parameters/PerPageParam'],
];

$idCrud = function (
    string $tag,
    string $resource,
    string $resourceLabel,
    array $item,
    string $detailMessage,
    string $updateMessage,
    string $deleteMessage,
    array $update422,
    ?array $multipartUpdate = null,
    bool $multipartUpdateUsesMethodSpoofing = false,
) use ($ref, $jsend, $json, $adminAuth, $notFound, $validation): array {
    $multipartUpdate ??= [
        'content' => [
            'multipart/form-data' => [
                'schema' => ['type' => 'object', 'properties' => ['name' => ['type' => 'string']]],
            ],
        ],
    ];

    if ($multipartUpdateUsesMethodSpoofing) {
        $multipartUpdate['content']['multipart/form-data']['schema']['properties'] = array_merge(
            ['_method' => ['type' => 'string', 'enum' => ['PUT'], 'example' => 'PUT']],
            $multipartUpdate['content']['multipart/form-data']['schema']['properties'] ?? [],
        );
    }

    $updateMethod = $multipartUpdateUsesMethodSpoofing ? 'post' : 'put';
    $updateDescription = "Memperbarui {$resourceLabel}. Kirim hanya field yang ingin diubah.\n\n**Auth:** Bearer token admin.";

    if ($multipartUpdateUsesMethodSpoofing) {
        $updateDescription .= "\n\n**PENTING (upload gambar):** Karena ada file `image`, kirim sebagai **POST** dengan field `_method=PUT` (method spoofing Laravel). Jangan pakai PUT multipart langsung — PHP tidak mem-parsing `\$_FILES` untuk PUT, sehingga gambar tidak tersimpan walau response sukses.";
    }

    return [
        'get' => [
            'tags' => [$tag],
            'summary' => "Detail {$resourceLabel}",
            'description' => "Mengambil detail {$resourceLabel} berdasarkan ID.\n\n**Auth:** Bearer token admin.",
            'operationId' => "admin{$resource}Show",
            'security' => [['bearerAuth' => []]],
            'parameters' => [['$ref' => '#/components/parameters/IdParam']],
            'responses' => [
                '200' => $json('200 OK — Detail '.$resourceLabel, $jsend(true, $detailMessage, $item)),
                '401' => $adminAuth['401'],
                '403' => $adminAuth['403'],
                '404' => $notFound('Data tidak ditemukan.', 'ID tidak ada di database'),
            ],
        ],
        $updateMethod => [
            'tags' => [$tag],
            'summary' => "Update {$resourceLabel}",
            'description' => $updateDescription,
            'operationId' => "admin{$resource}Update",
            'security' => [['bearerAuth' => []]],
            'parameters' => [['$ref' => '#/components/parameters/IdParam']],
            'requestBody' => $multipartUpdate,
            'responses' => [
                '200' => $json('200 OK — '.$resourceLabel.' diperbarui', $jsend(true, $updateMessage, $item)),
                '401' => $adminAuth['401'],
                '403' => $adminAuth['403'],
                '404' => $notFound('Data tidak ditemukan.', 'ID tidak ada'),
                '422' => $validation($update422, 'Validasi field gagal'),
            ],
        ],
        'delete' => [
            'tags' => [$tag],
            'summary' => "Hapus {$resourceLabel}",
            'description' => "Menghapus {$resourceLabel} beserta media terkait (jika ada).\n\n**Auth:** Bearer token admin.",
            'operationId' => "admin{$resource}Destroy",
            'security' => [['bearerAuth' => []]],
            'parameters' => [['$ref' => '#/components/parameters/IdParam']],
            'responses' => [
                '200' => $json('200 OK — '.$resourceLabel.' dihapus', $jsend(true, $deleteMessage, null)),
                '401' => $adminAuth['401'],
                '403' => $adminAuth['403'],
                '404' => $notFound('Data tidak ditemukan.', 'ID tidak ada'),
            ],
        ],
    ];
};

$spec = [
    'openapi' => '3.0.3',
    'info' => [
        'title' => 'Dental Health API',
        'description' => "Dokumentasi REST API Dental Health.\n\nSetiap endpoint di bawah memiliki deskripsi, contoh request, dan contoh response sukses/gagal.\n\n**Header wajib:** Accept: application/json\n**Auth:** Bearer token Sanctum (klik Authorize di Swagger UI, masukkan token tanpa prefix Bearer)\n\n**Akun demo:** admin@clinic.com / sarah@clinic.com — password: password",
        'version' => '2.2.0',
        'contact' => ['name' => 'Dental Health API'],
    ],
    'servers' => [
        ['url' => '/api', 'description' => 'Default — same origin (pakai ini di Swagger, hindari CORS)'],
        ['url' => 'https://eternal-outpour-humorist.ngrok-free.dev/api', 'description' => 'Ngrok Tunnel'],
        ['url' => 'http://127.0.0.1:8000/api', 'description' => 'Local explicit — hanya dari mesin yang menjalankan artisan serve'],
        ['url' => 'http://10.0.2.2:8000/api', 'description' => 'Android Emulator'],
    ],
    'tags' => [
        ['name' => 'Public API', 'description' => 'Tanpa auth, throttle 60/menit'],
        ['name' => 'Public Auth', 'description' => 'Register & login user'],
        ['name' => 'Public Bookmarks', 'description' => 'Butuh token user'],
        ['name' => 'Admin Auth', 'description' => 'Login & reset password admin'],
        ['name' => 'Admin Dashboard', 'description' => 'Statistik admin'],
        ['name' => 'Admin Banners', 'description' => 'CRUD banner'],
        ['name' => 'Admin News', 'description' => 'CRUD berita'],
        ['name' => 'Admin Articles', 'description' => 'CRUD artikel'],
        ['name' => 'Admin Products', 'description' => 'CRUD produk'],
        ['name' => 'Admin Tips', 'description' => 'CRUD tips'],
        ['name' => 'Admin FAQs', 'description' => 'CRUD FAQ'],
    ],
    'components' => [
        'securitySchemes' => [
            'bearerAuth' => [
                'type' => 'http',
                'scheme' => 'bearer',
                'bearerFormat' => 'Sanctum',
                'description' => 'Token dari login. Header: Authorization: Bearer {token}',
            ],
        ],
        'parameters' => [
            'SlugParam' => ['name' => 'slug', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string'], 'example' => 'cara-menyikat-gigi-yang-benar'],
            'IdParam' => ['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer'], 'example' => 1],
            'CategoryContent' => ['name' => 'category', 'in' => 'query', 'schema' => ['type' => 'string', 'enum' => ['kesehatan_gigi', 'teknologi', 'klinik', 'edukasi', 'umum']]],
            'CategoryProduct' => ['name' => 'category', 'in' => 'query', 'schema' => ['type' => 'string', 'enum' => ['perawatan_gigi', 'produk_gigi', 'tips_perawatan']]],
            'SearchParam' => ['name' => 'search', 'in' => 'query', 'schema' => ['type' => 'string'], 'example' => 'gigi'],
            'LimitParam' => ['name' => 'limit', 'in' => 'query', 'description' => 'Max 20. Response data jadi array.', 'schema' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 20, 'example' => 5]],
            'PageParam' => ['name' => 'page', 'in' => 'query', 'description' => 'Aktifkan pagination. Tanpa ini, response data = array.', 'schema' => ['type' => 'integer', 'minimum' => 1, 'example' => 1]],
            'PerPageParam' => ['name' => 'per_page', 'in' => 'query', 'description' => 'Item per halaman saat pakai page (1-50, default 15).', 'schema' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 50, 'example' => 15]],
            'SortParam' => ['name' => 'sort', 'in' => 'query', 'schema' => ['type' => 'string', 'enum' => ['latest', 'popular'], 'default' => 'latest']],
        ],
        'schemas' => [
            'JSendError' => ['type' => 'object', 'required' => ['status', 'message', 'data'], 'properties' => ['status' => ['type' => 'boolean'], 'message' => ['type' => 'string'], 'data' => ['nullable' => true]]],
            'ValidationErrorData' => ['type' => 'object', 'properties' => ['errors' => ['type' => 'object', 'additionalProperties' => ['type' => 'array', 'items' => ['type' => 'string']]]]],
            'Banner' => ['type' => 'object'],
            'Tip' => ['type' => 'object'],
            'News' => ['type' => 'object'],
            'Article' => ['type' => 'object'],
            'Product' => ['type' => 'object'],
            'Faq' => ['type' => 'object'],
            'User' => ['type' => 'object'],
            'Pagination' => ['type' => 'object'],
            'DashboardData' => ['type' => 'object'],
        ],
    ],
    'paths' => [
        '/v1/public/banners' => [
            'get' => [
                'tags' => ['Public API'],
                'summary' => 'List banner aktif',
                'description' => "Menampilkan banner untuk halaman Beranda aplikasi.\n\n**Auth:** Tidak perlu.\n**Filter:** Hanya `is_active = true`.\n**Urutan:** Terbaru dulu.\n**Rate limit:** 60 request/menit per IP.",
                'operationId' => 'publicBannersIndex',
                'responses' => [
                    '200' => $json(
                        '200 OK — Banner aktif berhasil dimuat',
                        $jsend(true, 'Data banner berhasil dimuat.', [$banner]),
                        ['type' => 'object', 'properties' => ['status' => ['type' => 'boolean'], 'message' => ['type' => 'string'], 'data' => ['type' => 'array', 'items' => $ref('Banner')]]],
                    ),
                    '429' => $rateLimit('Lebih dari 60 request per menit ke grup /public'),
                ],
            ],
        ],
        '/v1/public/tips/today' => [
            'get' => [
                'tags' => ['Public API'],
                'summary' => 'Tips aktif hari ini',
                'description' => "Menampilkan satu tips harian yang sedang aktif (`is_active = true`).\n\n**Auth:** Tidak perlu.\n**Catatan:** Jika tidak ada tip aktif, response 404.",
                'operationId' => 'publicTipsToday',
                'responses' => [
                    '200' => $json('200 OK — Tip hari ini ditemukan', $jsend(true, 'Tips hari ini berhasil dimuat.', $tip)),
                    '404' => $notFound('Tips hari ini belum tersedia.', 'Belum ada record tip dengan is_active=true'),
                    '429' => $rateLimit('Throttle grup public'),
                ],
            ],
        ],
        '/v1/public/news' => [
            'get' => [
                'tags' => ['Public API'],
                'summary' => 'List berita published',
                'description' => "Daftar berita untuk tab Berita.\n\n**Auth:** Tidak perlu.\n**Filter:** `is_published = true`.\n\n{$publicListModeDoc}",
                'operationId' => 'publicNewsIndex',
                'parameters' => $publicListParams,
                'responses' => [
                    '200' => $listResponse('200 OK — Berita berhasil dimuat', 'Data berita berhasil dimuat.', $news),
                    '429' => $rateLimit('Throttle grup public'),
                ],
            ],
        ],
        '/v1/public/news/{slug}' => [
            'get' => [
                'tags' => ['Public API'],
                'summary' => 'Detail berita',
                'description' => "Detail berita by slug. **`view_count` otomatis +1** setiap kali dibuka.\n\n**Auth:** Tidak perlu.\n**404:** Slug tidak ada atau berita draft.",
                'operationId' => 'publicNewsShow',
                'parameters' => [['$ref' => '#/components/parameters/SlugParam']],
                'responses' => [
                    '200' => $json('200 OK — Detail berita', $jsend(true, 'Detail berita.', $news)),
                    '404' => $notFound('Data tidak ditemukan.', 'Slug tidak ditemukan atau is_published=false'),
                    '429' => $rateLimit('Throttle grup public'),
                ],
            ],
        ],
        '/v1/public/news/{id}/like' => [
            'post' => [
                'tags' => ['Public API'],
                'summary' => 'Like berita',
                'description' => "Menambah `like_count` berita +1.\n\n**Auth:** Tidak perlu.\n**Path param:** `id` (integer, bukan slug).\n**Rate limit:** 20 request/menit.",
                'operationId' => 'publicNewsLike',
                'parameters' => [['$ref' => '#/components/parameters/IdParam']],
                'responses' => [
                    '200' => $json('200 OK — Like tercatat', $jsend(true, 'Berita berhasil disukai.', null)),
                    '404' => $notFound('Data tidak ditemukan.', 'ID berita tidak ada atau belum published'),
                    '429' => $rateLimit('Spam like — max 20/menit'),
                ],
            ],
        ],
        '/v1/public/articles' => [
            'get' => [
                'tags' => ['Public API'],
                'summary' => 'List artikel published',
                'description' => "Daftar artikel edukasi.\n\n**Auth:** Tidak perlu.\n**Sort:** `popular` atau `latest` (default)\n\n{$publicListModeDoc}",
                'operationId' => 'publicArticlesIndex',
                'parameters' => array_merge(
                    [['$ref' => '#/components/parameters/SortParam']],
                    $publicListParams,
                ),
                'responses' => [
                    '200' => $listResponse('200 OK — Artikel berhasil dimuat', 'Data artikel berhasil dimuat.', $article),
                    '429' => $rateLimit('Throttle grup public'),
                ],
            ],
        ],
        '/v1/public/articles/{slug}' => [
            'get' => [
                'tags' => ['Public API'],
                'summary' => 'Detail artikel',
                'description' => "Detail artikel by slug. **`view_count` +1** per kunjungan.\n\n**Auth:** Tidak perlu.",
                'operationId' => 'publicArticlesShow',
                'parameters' => [['$ref' => '#/components/parameters/SlugParam']],
                'responses' => [
                    '200' => $json('200 OK — Detail artikel', $jsend(true, 'Detail artikel.', $article)),
                    '404' => $notFound('Data tidak ditemukan.', 'Slug artikel tidak ada atau draft'),
                    '429' => $rateLimit('Throttle grup public'),
                ],
            ],
        ],
        '/v1/public/articles/{slug}/like' => [
            'post' => [
                'tags' => ['Public API'],
                'summary' => 'Like artikel',
                'description' => "Menambah `like_count` artikel +1.\n\n**Auth:** Tidak perlu.\n**Rate limit:** 20/menit.",
                'operationId' => 'publicArticlesLike',
                'parameters' => [['$ref' => '#/components/parameters/SlugParam']],
                'responses' => [
                    '200' => $json('200 OK — Like artikel', $jsend(true, 'Artikel berhasil disukai.', null)),
                    '404' => $notFound('Data tidak ditemukan.', 'Slug artikel tidak ditemukan'),
                    '429' => $rateLimit('Spam like artikel'),
                ],
            ],
        ],
        '/v1/public/products' => [
            'get' => [
                'tags' => ['Public API'],
                'summary' => 'List produk aktif',
                'description' => "Katalog produk/perawatan.\n\n**Auth:** Tidak perlu.\n**Filter:** category, search (hanya produk aktif).\n\n{$publicListModeDoc}",
                'operationId' => 'publicProductsIndex',
                'parameters' => $publicProductListParams,
                'responses' => [
                    '200' => $listResponse('200 OK — Produk berhasil dimuat', 'Data produk berhasil dimuat.', $product),
                    '429' => $rateLimit('Throttle grup public'),
                ],
            ],
        ],
        '/v1/public/products/{slug}' => [
            'get' => [
                'tags' => ['Public API'],
                'summary' => 'Detail produk + artikel terkait',
                'description' => "Detail produk dan 3 artikel populer terkait.\n\n**Auth:** Tidak perlu.\n**Response data:** `{ product, related_articles }`",
                'operationId' => 'publicProductsShow',
                'parameters' => [['$ref' => '#/components/parameters/SlugParam']],
                'responses' => [
                    '200' => $json('200 OK — Detail produk', $jsend(true, 'Detail produk.', ['product' => $product, 'related_articles' => [$article]])),
                    '404' => $notFound('Data tidak ditemukan.', 'Slug produk tidak ada atau inactive'),
                    '429' => $rateLimit('Throttle grup public'),
                ],
            ],
        ],
        '/v1/public/faqs' => [
            'get' => [
                'tags' => ['Public API'],
                'summary' => 'List FAQ aktif',
                'description' => "FAQ untuk halaman bantuan.\n\n**Auth:** Tidak perlu.\n**Filter:** `is_active = true`, urut `sort_order`.",
                'operationId' => 'publicFaqsIndex',
                'responses' => [
                    '200' => $json('200 OK — FAQ berhasil dimuat', $jsend(true, 'Data FAQ berhasil dimuat.', [$faq])),
                    '429' => $rateLimit('Throttle grup public'),
                ],
            ],
        ],
        '/v1/public/auth/register' => [
            'post' => [
                'tags' => ['Public Auth'],
                'summary' => 'Daftar akun user',
                'description' => "Registrasi pengguna baru dengan role `user`.\n\n**Auth:** Tidak perlu.\n**Body:** JSON\n**Rate limit:** 5/menit.\n**Response:** Langsung return token (auto-login).",
                'operationId' => 'publicAuthRegister',
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['type' => 'object', 'required' => ['name', 'email', 'password', 'password_confirmation'], 'properties' => [
                                'name' => ['type' => 'string'],
                                'email' => ['type' => 'string', 'format' => 'email'],
                                'password' => ['type' => 'string', 'minLength' => 8],
                                'password_confirmation' => ['type' => 'string'],
                            ]],
                            'example' => ['name' => 'Budi Santoso', 'email' => 'budi@email.com', 'password' => 'password', 'password_confirmation' => 'password'],
                        ],
                    ],
                ],
                'responses' => [
                    '201' => $json('201 Created — Registrasi sukses', $jsend(true, 'Registrasi berhasil', ['user' => $user, 'token' => '2|newtoken...'])),
                    '422' => $validation([
                        'email' => ['Email sudah terdaftar.'],
                        'password' => ['Password minimal 8 karakter.', 'Konfirmasi password tidak cocok.'],
                    ], 'Email duplikat atau password tidak valid'),
                    '429' => $rateLimit('Max 5 register per menit'),
                ],
            ],
        ],
        '/v1/public/auth/login' => [
            'post' => [
                'tags' => ['Public Auth'],
                'summary' => 'Login user',
                'description' => "Login akun dengan role `user` saja.\n\n**Auth:** Tidak perlu.\n**Rate limit:** 5/menit.\n**403:** Jika pakai akun admin di endpoint ini.",
                'operationId' => 'publicAuthLogin',
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['type' => 'object', 'required' => ['email', 'password'], 'properties' => [
                                'email' => ['type' => 'string', 'format' => 'email'],
                                'password' => ['type' => 'string'],
                            ]],
                            'example' => ['email' => 'sarah@clinic.com', 'password' => 'password'],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => $json('200 OK — Login user sukses', $jsend(true, 'Login berhasil', ['user' => $user, 'token' => '1|abc...'])),
                    '401' => $json('401 Unauthorized — Kredensial salah', $jsend(false, 'Email atau password salah', null), $ref('JSendError')),
                    '403' => $forbidden('Akun ini bukan akun pengguna.', 'admin@clinic.com tidak bisa login via endpoint user'),
                    '422' => $validation(['email' => ['Email wajib diisi.'], 'password' => ['Password wajib diisi.']], 'Field kosong'),
                    '429' => $rateLimit('Max 5 login per menit'),
                ],
            ],
        ],
        '/v1/public/auth/logout' => [
            'post' => [
                'tags' => ['Public Auth'],
                'summary' => 'Logout user',
                'description' => "Menghapus token Sanctum yang sedang dipakai.\n\n**Auth:** Bearer token user.",
                'operationId' => 'publicAuthLogout',
                'security' => [['bearerAuth' => []]],
                'responses' => [
                    '200' => $json('200 OK — Logout sukses', $jsend(true, 'Logout berhasil', null)),
                    '401' => $unauth('Header Authorization tidak ada atau token expired'),
                ],
            ],
        ],
        '/v1/public/auth/profile' => [
            'get' => [
                'tags' => ['Public Auth'],
                'summary' => 'Lihat profil user',
                'description' => "Mengambil data profil user yang sedang login.\n\n**Auth:** Bearer token user.",
                'operationId' => 'publicAuthProfileGet',
                'security' => [['bearerAuth' => []]],
                'responses' => [
                    '200' => $json('200 OK — Profil user', $jsend(true, 'Profil berhasil dimuat', $user)),
                    '401' => $unauth('Token tidak valid'),
                ],
            ],
            'post' => [
                'tags' => ['Public Auth'],
                'summary' => 'Update profil user',
                'description' => "Update nama, email, password, atau avatar.\n\n**Auth:** Bearer token user.\n**Content-Type:** multipart/form-data\n**Catatan:** Password hanya diubah jika field `password` diisi.\n\n**PENTING (upload avatar):** Karena ada file `avatar`, kirim sebagai **POST** dengan field `_method=PUT` (method spoofing Laravel). Jangan pakai PUT multipart langsung — PHP tidak mem-parsing `\$_FILES` untuk PUT, sehingga avatar tidak tersimpan walau response sukses.",
                'operationId' => 'publicAuthProfileUpdate',
                'security' => [['bearerAuth' => []]],
                'requestBody' => [
                    'content' => [
                        'multipart/form-data' => [
                            'schema' => ['type' => 'object', 'properties' => [
                                '_method' => ['type' => 'string', 'enum' => ['PUT'], 'example' => 'PUT'],
                                'name' => ['type' => 'string'],
                                'email' => ['type' => 'string', 'format' => 'email'],
                                'password' => ['type' => 'string', 'minLength' => 8],
                                'password_confirmation' => ['type' => 'string'],
                                'avatar' => ['type' => 'string', 'format' => 'binary'],
                            ]],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => $json('200 OK — Profil diperbarui', $jsend(true, 'Profil berhasil diperbarui', $user)),
                    '401' => $unauth('Token tidak valid'),
                    '422' => $validation([
                        'email' => ['Email sudah terdaftar.'],
                        'password' => ['Konfirmasi password tidak cocok.'],
                        'avatar' => ['Avatar harus berupa gambar jpeg/png/jpg/gif max 2MB.'],
                    ], 'Validasi profil gagal'),
                ],
            ],
        ],
        '/v1/public/bookmarks' => [
            'get' => [
                'tags' => ['Public Bookmarks'],
                'summary' => 'List artikel tersimpan',
                'description' => "Artikel yang di-bookmark user.\n\n**Auth:** Bearer token user.\n**Filter:** Hanya artikel published.\n\n{$listModeDoc}",
                'operationId' => 'publicBookmarksIndex',
                'security' => [['bearerAuth' => []]],
                'parameters' => [
                    ['$ref' => '#/components/parameters/PageParam'],
                    ['$ref' => '#/components/parameters/PerPageParam'],
                ],
                'responses' => [
                    '200' => $listResponse('200 OK — Bookmark dimuat', 'Artikel tersimpan berhasil dimuat.', $article),
                    '401' => $unauth('Harus login sebagai user'),
                ],
            ],
        ],
        '/v1/public/bookmarks/{slug}' => [
            'post' => [
                'tags' => ['Public Bookmarks'],
                'summary' => 'Simpan artikel',
                'description' => "Menambah artikel ke bookmark (idempotent — tidak error jika sudah ada).\n\n**Auth:** Bearer token user.",
                'operationId' => 'publicBookmarksStore',
                'security' => [['bearerAuth' => []]],
                'parameters' => [['$ref' => '#/components/parameters/SlugParam']],
                'responses' => [
                    '201' => $json('201 Created — Artikel disimpan', $jsend(true, 'Artikel berhasil disimpan.', null)),
                    '401' => $unauth('Harus login'),
                    '404' => $notFound('Data tidak ditemukan.', 'Slug artikel tidak ada'),
                ],
            ],
            'delete' => [
                'tags' => ['Public Bookmarks'],
                'summary' => 'Hapus bookmark',
                'description' => "Menghapus artikel dari daftar simpanan user.\n\n**Auth:** Bearer token user.",
                'operationId' => 'publicBookmarksDestroy',
                'security' => [['bearerAuth' => []]],
                'parameters' => [['$ref' => '#/components/parameters/SlugParam']],
                'responses' => [
                    '200' => $json('200 OK — Bookmark dihapus', $jsend(true, 'Artikel berhasil dihapus dari simpanan.', null)),
                    '401' => $unauth('Harus login'),
                    '404' => $notFound('Data tidak ditemukan.', 'Artikel tidak ditemukan'),
                ],
            ],
        ],
        '/v1/admin/login' => [
            'post' => [
                'tags' => ['Admin Auth'],
                'summary' => 'Login admin',
                'description' => "Login khusus akun role `admin`.\n\n**Auth:** Tidak perlu.\n**Rate limit:** 5/menit.",
                'operationId' => 'adminAuthLogin',
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['type' => 'object', 'required' => ['email', 'password'], 'properties' => [
                                'email' => ['type' => 'string', 'format' => 'email'],
                                'password' => ['type' => 'string'],
                            ]],
                            'example' => ['email' => 'admin@clinic.com', 'password' => 'password'],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => $json('200 OK — Login admin sukses', $jsend(true, 'Login berhasil', ['user' => array_merge($user, ['email' => 'admin@clinic.com']), 'token' => '3|admin...'])),
                    '401' => $json('401 Unauthorized — Kredensial admin salah', $jsend(false, 'Email atau password salah', null), $ref('JSendError')),
                    '403' => $forbidden('Akun ini bukan akun admin.', 'sarah@clinic.com tidak bisa login admin'),
                    '422' => $validation(['email' => ['Email wajib diisi.']], 'Validasi login'),
                    '429' => $rateLimit('Max 5 login admin per menit'),
                ],
            ],
        ],
        '/v1/admin/register' => [
            'post' => [
                'tags' => ['Admin Auth'],
                'summary' => 'Daftarkan admin baru',
                'description' => "Membuat akun admin baru. Hanya admin yang sudah login yang boleh akses.\n\n**Auth:** Bearer token admin.",
                'operationId' => 'adminAuthRegister',
                'security' => [['bearerAuth' => []]],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['type' => 'object', 'required' => ['name', 'email', 'password', 'password_confirmation'], 'properties' => [
                                'name' => ['type' => 'string'],
                                'email' => ['type' => 'string', 'format' => 'email'],
                                'password' => ['type' => 'string', 'minLength' => 8],
                                'password_confirmation' => ['type' => 'string'],
                            ]],
                        ],
                    ],
                ],
                'responses' => [
                    '201' => $json('201 Created — Admin baru', $jsend(true, 'Admin baru berhasil didaftarkan', array_merge($user, ['email' => 'admin2@clinic.com']))),
                    '401' => $adminAuth['401'],
                    '403' => $adminAuth['403'],
                    '422' => $validation(['email' => ['Email sudah terdaftar.']], 'Data registrasi admin tidak valid'),
                ],
            ],
        ],
        '/v1/admin/logout' => [
            'post' => [
                'tags' => ['Admin Auth'],
                'summary' => 'Logout admin',
                'description' => "Hapus token admin aktif.\n\n**Auth:** Bearer token admin.",
                'operationId' => 'adminAuthLogout',
                'security' => [['bearerAuth' => []]],
                'responses' => [
                    '200' => $json('200 OK — Logout admin', $jsend(true, 'Logout berhasil', null)),
                    '401' => $adminAuth['401'],
                ],
            ],
        ],
        '/v1/admin/profile' => [
            'get' => [
                'tags' => ['Admin Auth'],
                'summary' => 'Profil admin',
                'description' => "Data profil admin yang sedang login.\n\n**Auth:** Bearer token admin.",
                'operationId' => 'adminAuthProfile',
                'security' => [['bearerAuth' => []]],
                'responses' => [
                    '200' => $json('200 OK — Profil admin', $jsend(true, 'Profil berhasil dimuat', array_merge($user, ['email' => 'admin@clinic.com']))),
                    '401' => $adminAuth['401'],
                    '403' => $adminAuth['403'],
                ],
            ],
        ],
        '/v1/admin/forgot-password' => [
            'post' => [
                'tags' => ['Admin Auth'],
                'summary' => 'Kirim OTP reset password',
                'description' => "Mengirim kode OTP reset password ke email admin.\n\n**Auth:** Tidak perlu.",
                'operationId' => 'adminForgotPassword',
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['type' => 'object', 'required' => ['email'], 'properties' => ['email' => ['type' => 'string', 'format' => 'email']]],
                            'example' => ['email' => 'admin@clinic.com'],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => $json('200 OK — OTP terkirim', $jsend(true, 'Kode OTP reset password sudah dikirim ke email.', null)),
                    '400' => $json('400 Bad Request — Email tidak ditemukan', $jsend(false, 'We can\'t find a user with that email address.', null), $ref('JSendError')),
                    '422' => $validation(['email' => ['Email wajib diisi.']], 'Format email tidak valid'),
                ],
            ],
        ],
        '/v1/admin/reset-password' => [
            'post' => [
                'tags' => ['Admin Auth'],
                'summary' => 'Reset password',
                'description' => "Reset password admin menggunakan OTP dari email.\n\n**Auth:** Tidak perlu.",
                'operationId' => 'adminResetPassword',
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['type' => 'object', 'required' => ['email', 'otp', 'password', 'password_confirmation'], 'properties' => [
                                'email' => ['type' => 'string', 'format' => 'email'],
                                'otp' => ['type' => 'string', 'minLength' => 6, 'maxLength' => 6],
                                'password' => ['type' => 'string', 'minLength' => 8],
                                'password_confirmation' => ['type' => 'string'],
                            ]],
                            'example' => ['email' => 'admin@clinic.com', 'otp' => '123456', 'password' => 'newpassword123', 'password_confirmation' => 'newpassword123'],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => $json('200 OK — Password direset', $jsend(true, 'Password berhasil direset.', null)),
                    '400' => $json('400 Bad Request — OTP invalid', $jsend(false, 'Kode OTP reset password tidak valid atau sudah kedaluwarsa.', null), $ref('JSendError')),
                    '422' => $validation(['otp' => ['OTP harus 6 digit.'], 'password' => ['Konfirmasi password tidak cocok.']], 'Validasi reset password'),
                ],
            ],
        ],
        '/v1/admin/dashboard' => [
            'get' => [
                'tags' => ['Admin Dashboard'],
                'summary' => 'Statistik dashboard',
                'description' => "Ringkasan jumlah konten dan 5 konten terbaru.\n\n**Auth:** Bearer token admin.",
                'operationId' => 'adminDashboard',
                'security' => [['bearerAuth' => []]],
                'responses' => [
                    '200' => $json('200 OK — Dashboard', $jsend(true, 'Dashboard berhasil dimuat.', [
                        'stats' => ['news_count' => 9, 'articles_count' => 10, 'products_count' => 9, 'active_admins_count' => 1],
                        'recent_content' => [['type' => 'news', 'id' => 1, 'title' => 'Teknologi AI dalam Diagnosa Gigi', 'is_published' => true, 'created_at' => '2026-06-07T04:28:06.000000Z']],
                    ])),
                    '401' => $adminAuth['401'],
                    '403' => $adminAuth['403'],
                ],
            ],
        ],
    ],
];

// Admin CRUD — banners
$spec['paths']['/v1/admin/banners'] = [
    'get' => [
        'tags' => ['Admin Banners'],
        'summary' => 'List semua banner',
        'description' => "Daftar semua banner (aktif & nonaktif).\n\n**Auth:** Bearer token admin.\n\n{$listModeDoc}",
        'operationId' => 'adminBannersIndex',
        'security' => [['bearerAuth' => []]],
        'parameters' => $adminListParams,
        'responses' => [
            '200' => $listResponse('200 OK — List banner admin', 'Data banner berhasil dimuat', $banner),
            '401' => $adminAuth['401'],
            '403' => $adminAuth['403'],
        ],
    ],
    'post' => [
        'tags' => ['Admin Banners'],
        'summary' => 'Buat banner',
        'description' => "Upload banner baru.\n\n**Auth:** Bearer token admin.\n**Wajib:** title, image\n**Opsional:** subtitle, tag (edukasi/promo/info), link_url, is_active",
        'operationId' => 'adminBannersStore',
        'security' => [['bearerAuth' => []]],
        'requestBody' => [
            'required' => true,
            'content' => [
                'multipart/form-data' => [
                    'schema' => ['type' => 'object', 'required' => ['title', 'image'], 'properties' => [
                        'title' => ['type' => 'string', 'example' => 'Promo Scaling Gigi Juni'],
                        'subtitle' => ['type' => 'string', 'example' => 'Diskon 20% sepanjang Juni'],
                        'tag' => ['type' => 'string', 'enum' => ['edukasi', 'promo', 'info'], 'example' => 'promo'],
                        'link_url' => ['type' => 'string', 'format' => 'uri', 'example' => 'https://dentalclinic.example.com/promo/scaling'],
                        'is_active' => ['type' => 'string', 'enum' => ['1', '0'], 'example' => '1', 'description' => 'Gunakan 1 (true) atau 0 (false) untuk form-data.'],
                        'image' => ['type' => 'string', 'format' => 'binary'],
                    ]],
                ],
            ],
        ],
        'responses' => [
            '201' => $json('201 Created — Banner dibuat', $jsend(true, 'Banner berhasil dibuat', $banner)),
            '401' => $adminAuth['401'],
            '403' => $adminAuth['403'],
            '422' => $validation(array_merge(['title' => ['Judul wajib diisi.']], $image422), 'Title kosong atau gambar invalid'),
        ],
    ],
];
$spec['paths']['/v1/admin/banners/{id}'] = $idCrud('Admin Banners', 'Banners', 'banner', $banner, 'Detail banner', 'Banner berhasil diperbarui', 'Banner berhasil dihapus', ['link_url' => ['Format URL tidak valid.']], [
    'content' => ['multipart/form-data' => ['schema' => ['type' => 'object', 'properties' => [
        'title' => ['type' => 'string', 'example' => 'Promo Scaling Gigi Juni - Updated'], 'subtitle' => ['type' => 'string', 'example' => 'Diskon 25% untuk pasien baru'], 'tag' => ['type' => 'string', 'enum' => ['edukasi', 'promo', 'info'], 'example' => 'promo'], 'link_url' => ['type' => 'string', 'format' => 'uri', 'example' => 'https://dentalclinic.example.com/promo/scaling'], 'is_active' => ['type' => 'string', 'enum' => ['1', '0'], 'example' => '1', 'description' => 'Gunakan 1 (true) atau 0 (false) untuk form-data.'], 'image' => ['type' => 'string', 'format' => 'binary'],
    ]]]],
], true);

// Admin news
$spec['paths']['/v1/admin/news'] = [
    'get' => [
        'tags' => ['Admin News'],
        'summary' => 'List berita',
        'description' => "Semua berita termasuk draft.\n\n**Auth:** Bearer token admin.\n**Filter:** status, category, search\n\n{$listModeDoc}",
        'operationId' => 'adminNewsIndex',
        'security' => [['bearerAuth' => []]],
        'parameters' => array_merge(
            [
                ['name' => 'status', 'in' => 'query', 'schema' => ['type' => 'string', 'enum' => ['published', 'draft']]],
                ['$ref' => '#/components/parameters/CategoryContent'],
                ['$ref' => '#/components/parameters/SearchParam'],
            ],
            $adminListParams,
        ),
        'responses' => [
            '200' => $listResponse('200 OK — List berita admin', 'Data berita berhasil dimuat', $news),
            '401' => $adminAuth['401'],
            '403' => $adminAuth['403'],
        ],
    ],
    'post' => [
        'tags' => ['Admin News'],
        'summary' => 'Buat berita',
        'description' => "Buat berita baru. Slug auto-generate dari title.\n\n**Auth:** Bearer token admin.\n**Wajib:** title, content",
        'operationId' => 'adminNewsStore',
        'security' => [['bearerAuth' => []]],
        'requestBody' => [
            'required' => true,
            'content' => ['multipart/form-data' => ['schema' => ['type' => 'object', 'required' => ['title', 'content'], 'properties' => [
                'title' => ['type' => 'string', 'example' => 'Tips Menyikat Gigi yang Benar'], 'category' => ['type' => 'string', 'enum' => ['kesehatan_gigi', 'teknologi', 'klinik', 'edukasi', 'umum'], 'example' => 'edukasi'], 'summary' => ['type' => 'string', 'example' => 'Teknik menyikat gigi yang direkomendasikan dokter gigi.'], 'content' => ['type' => 'string', 'example' => 'Sikat gigi dua kali sehari selama dua menit dengan teknik Bass.'], 'is_published' => ['type' => 'string', 'enum' => ['1', '0'], 'example' => '1', 'description' => 'Gunakan 1 (true) atau 0 (false) untuk form-data.'], 'image' => ['type' => 'string', 'format' => 'binary'],
            ]]]],
        ],
        'responses' => [
            '201' => $json('201 Created — Berita dibuat', $jsend(true, 'Berita berhasil dibuat', $news)),
            '401' => $adminAuth['401'],
            '403' => $adminAuth['403'],
            '422' => $validation(['title' => ['Judul wajib diisi.'], 'content' => ['Konten wajib diisi.']], 'Field wajib berita'),
        ],
    ],
];
$spec['paths']['/v1/admin/news/{id}'] = $idCrud('Admin News', 'News', 'berita', $news, 'Detail berita', 'Berita berhasil diperbarui', 'Berita berhasil dihapus', ['title' => ['Judul maksimal 255 karakter.']], [
    'content' => ['multipart/form-data' => ['schema' => ['type' => 'object', 'properties' => [
        'title' => ['type' => 'string', 'example' => 'Tips Menyikat Gigi yang Benar - Updated'],
        'category' => ['type' => 'string', 'enum' => ['kesehatan_gigi', 'teknologi', 'klinik', 'edukasi', 'umum'], 'example' => 'edukasi'],
        'summary' => ['type' => 'string', 'example' => 'Panduan singkat menyikat gigi yang aman untuk email.'],
        'content' => ['type' => 'string', 'example' => 'Gunakan sikat berbulu lembut dan ganti sikat setiap 3 bulan.'],
        'is_published' => ['type' => 'string', 'enum' => ['1', '0'], 'example' => '1', 'description' => 'Gunakan 1 (true) atau 0 (false) untuk form-data.'],
        'image' => ['type' => 'string', 'format' => 'binary'],
    ]]]],
], true);

// Admin articles
$spec['paths']['/v1/admin/articles'] = [
    'get' => [
        'tags' => ['Admin Articles'],
        'summary' => 'List artikel',
        'description' => "Semua artikel termasuk draft.\n\n**Auth:** Bearer token admin.\n**Filter:** status, category, search\n\n{$listModeDoc}",
        'operationId' => 'adminArticlesIndex',
        'security' => [['bearerAuth' => []]],
        'parameters' => array_merge(
            [
                ['name' => 'status', 'in' => 'query', 'schema' => ['type' => 'string', 'enum' => ['published', 'draft']]],
                ['$ref' => '#/components/parameters/CategoryContent'],
                ['$ref' => '#/components/parameters/SearchParam'],
            ],
            $adminListParams,
        ),
        'responses' => [
            '200' => $listResponse('200 OK — List artikel admin', 'Data artikel berhasil dimuat', $article),
            '401' => $adminAuth['401'],
            '403' => $adminAuth['403'],
        ],
    ],
    'post' => [
        'tags' => ['Admin Articles'],
        'summary' => 'Buat artikel',
        'description' => "Buat artikel edukasi. Slug auto dari title.\n\n**Auth:** Bearer token admin.\n**Wajib:** title, content",
        'operationId' => 'adminArticlesStore',
        'security' => [['bearerAuth' => []]],
        'requestBody' => [
            'required' => true,
            'content' => ['multipart/form-data' => ['schema' => ['type' => 'object', 'required' => ['title', 'content'], 'properties' => [
                'title' => ['type' => 'string'], 'category' => ['type' => 'string'], 'content' => ['type' => 'string'], 'is_published' => ['type' => 'boolean'], 'image' => ['type' => 'string', 'format' => 'binary'],
            ]]]],
        ],
        'responses' => [
            '201' => $json('201 Created — Artikel dibuat', $jsend(true, 'Artikel berhasil dibuat', $article)),
            '401' => $adminAuth['401'],
            '403' => $adminAuth['403'],
            '422' => $validation(['title' => ['Judul wajib diisi.'], 'content' => ['Konten wajib diisi.']], 'Validasi artikel'),
        ],
    ],
];
$spec['paths']['/v1/admin/articles/{id}'] = $idCrud('Admin Articles', 'Articles', 'artikel', $article, 'Detail artikel', 'Artikel berhasil diperbarui', 'Artikel berhasil dihapus', ['content' => ['Konten wajib diisi.']], [
    'content' => ['multipart/form-data' => ['schema' => ['type' => 'object', 'properties' => [
        'title' => ['type' => 'string'],
        'category' => ['type' => 'string'],
        'content' => ['type' => 'string'],
        'is_published' => ['type' => 'string', 'enum' => ['1', '0'], 'example' => '1', 'description' => 'Gunakan 1 (true) atau 0 (false) untuk form-data.'],
        'image' => ['type' => 'string', 'format' => 'binary'],
    ]]]],
], true);

// Admin products
$spec['paths']['/v1/admin/products'] = [
    'get' => [
        'tags' => ['Admin Products'],
        'summary' => 'List produk',
        'description' => "Semua produk.\n\n**Auth:** Bearer token admin.\n**Filter:** status, category, search\n\n{$listModeDoc}",
        'operationId' => 'adminProductsIndex',
        'security' => [['bearerAuth' => []]],
        'parameters' => array_merge(
            [
                ['name' => 'status', 'in' => 'query', 'schema' => ['type' => 'string', 'enum' => ['active', 'inactive']]],
                ['$ref' => '#/components/parameters/CategoryProduct'],
                ['$ref' => '#/components/parameters/SearchParam'],
            ],
            $adminListParams,
        ),
        'responses' => [
            '200' => $listResponse('200 OK — List produk admin', 'Data katalog obat berhasil dimuat', $product),
            '401' => $adminAuth['401'],
            '403' => $adminAuth['403'],
        ],
    ],
    'post' => [
        'tags' => ['Admin Products'],
        'summary' => 'Buat produk',
        'description' => "Tambah produk katalog.\n\n**Auth:** Bearer token admin.\n**Wajib:** name\n**Opsional:** benefits (array), usage_instructions, doctor_tips, dosage, image",
        'operationId' => 'adminProductsStore',
        'security' => [['bearerAuth' => []]],
        'requestBody' => [
            'required' => true,
            'content' => ['multipart/form-data' => ['schema' => ['type' => 'object', 'required' => ['name'], 'properties' => [
                'name' => ['type' => 'string'], 'category' => ['type' => 'string'], 'description' => ['type' => 'string'],
                'benefits' => ['type' => 'array', 'items' => ['type' => 'string']], 'usage_instructions' => ['type' => 'string'],
                'doctor_tips' => ['type' => 'string'], 'dosage' => ['type' => 'string'], 'is_active' => ['type' => 'boolean'], 'image' => ['type' => 'string', 'format' => 'binary'],
            ]]]],
        ],
        'responses' => [
            '201' => $json('201 Created — Produk dibuat', $jsend(true, 'Katalog obat berhasil dibuat', $product)),
            '401' => $adminAuth['401'],
            '403' => $adminAuth['403'],
            '422' => $validation(['name' => ['Nama wajib diisi.']], 'Nama produk kosong'),
        ],
    ],
];
$spec['paths']['/v1/admin/products/{id}'] = $idCrud('Admin Products', 'Products', 'produk', $product, 'Detail katalog obat', 'Katalog obat berhasil diperbarui', 'Katalog obat berhasil dihapus', ['name' => ['Nama wajib diisi.']], [
    'content' => ['multipart/form-data' => ['schema' => ['type' => 'object', 'properties' => [
        'name' => ['type' => 'string'],
        'category' => ['type' => 'string'],
        'description' => ['type' => 'string'],
        'benefits' => ['type' => 'array', 'items' => ['type' => 'string']],
        'usage_instructions' => ['type' => 'string'],
        'doctor_tips' => ['type' => 'string'],
        'dosage' => ['type' => 'string'],
        'is_active' => ['type' => 'boolean'],
        'image' => ['type' => 'string', 'format' => 'binary'],
    ]]]],
], true);

// Admin tips
$spec['paths']['/v1/admin/tips'] = [
    'get' => [
        'tags' => ['Admin Tips'],
        'summary' => 'List tips harian',
        'description' => "Semua tips (aktif & nonaktif).\n\n**Auth:** Bearer token admin.\n\n{$listModeDoc}",
        'operationId' => 'adminTipsIndex',
        'security' => [['bearerAuth' => []]],
        'parameters' => $adminListParams,
        'responses' => [
            '200' => $listResponse('200 OK — List tips', 'Data tip harian berhasil dimuat', $tip),
            '401' => $adminAuth['401'],
            '403' => $adminAuth['403'],
        ],
    ],
    'post' => [
        'tags' => ['Admin Tips'],
        'summary' => 'Buat tip',
        'description' => "Buat tips harian.\n\n**Auth:** Bearer token admin.\n**Wajib:** content\n**Catatan:** Set `is_active=true` akan menggantikan tip aktif lain (via command rotate).",
        'operationId' => 'adminTipsStore',
        'security' => [['bearerAuth' => []]],
        'requestBody' => [
            'required' => true,
            'content' => ['multipart/form-data' => ['schema' => ['type' => 'object', 'required' => ['content'], 'properties' => [
                'content' => ['type' => 'string'], 'is_active' => ['type' => 'boolean'], 'image' => ['type' => 'string', 'format' => 'binary'],
            ]]]],
        ],
        'responses' => [
            '201' => $json('201 Created — Tip dibuat', $jsend(true, 'Tip harian berhasil dibuat', $tip)),
            '401' => $adminAuth['401'],
            '403' => $adminAuth['403'],
            '422' => $validation(['content' => ['Konten wajib diisi.']], 'Content tip kosong'),
        ],
    ],
];
$spec['paths']['/v1/admin/tips/{id}'] = $idCrud('Admin Tips', 'Tips', 'tip harian', $tip, 'Detail tip harian', 'Tip harian berhasil diperbarui', 'Tip harian berhasil dihapus', ['content' => ['Konten wajib diisi.']], [
    'content' => ['multipart/form-data' => ['schema' => ['type' => 'object', 'properties' => [
        'content' => ['type' => 'string'],
        'is_active' => ['type' => 'boolean'],
        'image' => ['type' => 'string', 'format' => 'binary'],
    ]]]],
], true);

// Admin FAQs
$spec['paths']['/v1/admin/faqs'] = [
    'get' => [
        'tags' => ['Admin FAQs'],
        'summary' => 'List FAQ',
        'description' => "Semua FAQ termasuk nonaktif.\n\n**Auth:** Bearer token admin.\n\n{$listModeDoc}",
        'operationId' => 'adminFaqsIndex',
        'security' => [['bearerAuth' => []]],
        'parameters' => $adminListParams,
        'responses' => [
            '200' => $listResponse('200 OK — List FAQ admin', 'Data FAQ berhasil dimuat', $faq),
            '401' => $adminAuth['401'],
            '403' => $adminAuth['403'],
        ],
    ],
    'post' => [
        'tags' => ['Admin FAQs'],
        'summary' => 'Buat FAQ',
        'description' => "Tambah pertanyaan FAQ.\n\n**Auth:** Bearer token admin.\n**Body:** application/json\n**Wajib:** question, answer",
        'operationId' => 'adminFaqsStore',
        'security' => [['bearerAuth' => []]],
        'requestBody' => [
            'required' => true,
            'content' => ['application/json' => ['schema' => ['type' => 'object', 'required' => ['question', 'answer'], 'properties' => [
                'question' => ['type' => 'string'], 'answer' => ['type' => 'string'], 'sort_order' => ['type' => 'integer'], 'is_active' => ['type' => 'boolean'],
            ]], 'example' => ['question' => 'Kapan harus ke dokter gigi?', 'answer' => 'Minimal 6 bulan sekali.', 'sort_order' => 2, 'is_active' => true]]],
        ],
        'responses' => [
            '201' => $json('201 Created — FAQ dibuat', $jsend(true, 'FAQ berhasil dibuat', $faq)),
            '401' => $adminAuth['401'],
            '403' => $adminAuth['403'],
            '422' => $validation(['question' => ['Pertanyaan wajib diisi.'], 'answer' => ['Jawaban wajib diisi.']], 'FAQ tidak lengkap'),
        ],
    ],
];
$spec['paths']['/v1/admin/faqs/{id}'] = [
    'get' => [
        'tags' => ['Admin FAQs'],
        'summary' => 'Detail FAQ',
        'description' => "Detail FAQ by ID.\n\n**Auth:** Bearer token admin.",
        'operationId' => 'adminFaqsShow',
        'security' => [['bearerAuth' => []]],
        'parameters' => [['$ref' => '#/components/parameters/IdParam']],
        'responses' => [
            '200' => $json('200 OK — Detail FAQ', $jsend(true, 'Detail FAQ', $faq)),
            '401' => $adminAuth['401'],
            '403' => $adminAuth['403'],
            '404' => $notFound('Data tidak ditemukan.', 'FAQ ID tidak ada'),
        ],
    ],
    'put' => [
        'tags' => ['Admin FAQs'],
        'summary' => 'Update FAQ',
        'description' => "Perbarui FAQ.\n\n**Auth:** Bearer token admin.\n**Body:** application/json",
        'operationId' => 'adminFaqsUpdate',
        'security' => [['bearerAuth' => []]],
        'parameters' => [['$ref' => '#/components/parameters/IdParam']],
        'requestBody' => ['content' => ['application/json' => ['schema' => ['type' => 'object', 'properties' => [
            'question' => ['type' => 'string'], 'answer' => ['type' => 'string'], 'sort_order' => ['type' => 'integer'], 'is_active' => ['type' => 'boolean'],
        ]]]]],
        'responses' => [
            '200' => $json('200 OK — FAQ diperbarui', $jsend(true, 'FAQ berhasil diperbarui', $faq)),
            '401' => $adminAuth['401'],
            '403' => $adminAuth['403'],
            '404' => $notFound('Data tidak ditemukan.', 'FAQ tidak ada'),
            '422' => $validation(['question' => ['Pertanyaan wajib diisi.']], 'Validasi FAQ'),
        ],
    ],
    'delete' => [
        'tags' => ['Admin FAQs'],
        'summary' => 'Hapus FAQ',
        'description' => "Hapus FAQ permanen.\n\n**Auth:** Bearer token admin.",
        'operationId' => 'adminFaqsDestroy',
        'security' => [['bearerAuth' => []]],
        'parameters' => [['$ref' => '#/components/parameters/IdParam']],
        'responses' => [
            '200' => $json('200 OK — FAQ dihapus', $jsend(true, 'FAQ berhasil dihapus', null)),
            '401' => $adminAuth['401'],
            '403' => $adminAuth['403'],
            '404' => $notFound('Data tidak ditemukan.', 'FAQ tidak ada'),
        ],
    ],
];

$yaml = dumpYaml($spec);

file_put_contents(__DIR__.'/openapi.yaml', $yaml);

$ops = 0;
foreach ($spec['paths'] as $methods) {
    $ops += count($methods);
}
echo "Generated docs/openapi.yaml ({$ops} endpoints)\n";
