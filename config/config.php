<?php

/*
|--------------------------------------------------------------------------
| SETUP KONEKSI DATABASE (SUPABASE POSTGRESQL + VERCEL)
|--------------------------------------------------------------------------
*/
// Gunakan $_ENV sebagai tambahan karena beberapa serverless PHP tidak membaca getenv()
$databaseUrl = $_ENV['DATABASE_URL'] ?? getenv('DATABASE_URL');

if (!$databaseUrl) {
    // ⚠️ PENTING: Paste URL Connection Pooler Anda di sini!
    // Contoh bentuknya: postgresql://postgres:[PASSWORD-KAMU]@aws-0-ap-southeast-1.pooler.supabase.com:6543/postgres
    $databaseUrl = "postgresql://postgres.ddutuofcjkxzwnafycpi:halizah011104@aws-0-ap-northeast-1.pooler.supabase.com:6543/postgres";
}

$db_url = parse_url($databaseUrl);

$host   = $db_url['host'];
$port   = $db_url['port'];
$user   = $db_url['user'];
$pass   = $db_url['pass'];
$dbname = ltrim($db_url['path'], '/');

$dsn = "pgsql:host={$host};port={$port};dbname={$dbname};sslmode=require";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => true,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    http_response_code(500);
    exit('Database belum terhubung: ' . $e->getMessage());
}


/*
|--------------------------------------------------------------------------
| SESSION
|--------------------------------------------------------------------------
*/
if (session_status() === PHP_SESSION_NONE) {
    // Pastikan session menggunakan direktori temporary Vercel
    if (is_writable('/tmp')) {
        session_save_path('/tmp');
    }
    session_start();
}


/*
|--------------------------------------------------------------------------
| ESCAPE HTML
|--------------------------------------------------------------------------
*/
function e(string $value): string
{
    return htmlspecialchars(
        $value,
        ENT_QUOTES,
        'UTF-8'
    );
}


/*
|--------------------------------------------------------------------------
| CHECK ADMIN LOGIN
|--------------------------------------------------------------------------
*/
function is_admin(): bool
{
    return isset(
        $_SESSION['admin_id']
    );
}


/*
|--------------------------------------------------------------------------
| REQUIRE ADMIN
|--------------------------------------------------------------------------
*/
function require_admin(): void
{
    if (!is_admin()) {
        header('Location: /admin/login.php');
        exit;
    }
}


/*
|--------------------------------------------------------------------------
| UPLOAD IMAGE (KE SUPABASE STORAGE)
|--------------------------------------------------------------------------
*/
function upload_image(array $file, string $prefix = 'portfolio_'): ?string 
{
    if (empty($file) || !isset($file['error'])) return null;
    if ($file['error'] === UPLOAD_ERR_NO_FILE) return null;
    if ($file['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('Upload foto gagal.');
    if ($file['size'] > 5 * 1024 * 1024) throw new RuntimeException('Ukuran foto maksimal 5 MB.');

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    ];

    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Format foto harus JPG, PNG, atau WEBP.');
    }

    // Pengaturan Nama File & API Supabase
    $filename = $prefix . bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
    $bucketName = 'portofolio';
    $supabaseUrl = 'https://ddutuofcjkxzwnafycpi.supabase.co';
    
    // ⚠️ PENTING 2: Ganti tulisan di bawah dengan SERVICE ROLE KEY dari Supabase Anda
    $supabaseKey = getenv('SUPABASE_KEY') ?: 'MASUKKAN_SERVICE_ROLE_KEY_KAMU_DISINI'; 

    $endpoint = $supabaseUrl . '/storage/v1/object/' . $bucketName . '/' . $filename;
    
    // Baca isi file fisik
    $fileContent = file_get_contents($file['tmp_name']);

    // Kirim ke Supabase menggunakan cURL
    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fileContent);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $supabaseKey,
        'Content-Type: ' . $mime
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Jika sukses, kembalikan URL Publik gambar tersebut
    if ($httpCode >= 200 && $httpCode < 300) {
        return $supabaseUrl . '/storage/v1/object/public/' . $bucketName . '/' . $filename;
    } else {
        throw new RuntimeException('Gagal upload ke Supabase: ' . $response);
    }
}


/*
|--------------------------------------------------------------------------
| DELETE OLD FILE (DARI SUPABASE STORAGE)
|--------------------------------------------------------------------------
*/
function delete_old_file(?string $fileUrl): void 
{
    if (!$fileUrl) return;

    // Ambil nama file dari URL lengkap
    $urlParts = explode('/', $fileUrl);
    $filename = end($urlParts);

    $bucketName = 'portofolio';
    $supabaseUrl = 'https://ddutuofcjkxzwnafycpi.supabase.co';
    
    // ⚠️ PENTING 3: Ganti tulisan di bawah dengan SERVICE ROLE KEY dari Supabase Anda (Sama dengan PENTING 2)
    $supabaseKey = getenv('SUPABASE_KEY') ?: 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImRkdXR1b2Zjamt4enduYWZ5Y3BpIiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc4NzEyNjk5NSwiZXhwIjoyMTAyNzAyOTk1fQ.rW4sEVzukWKvjBYsyd_pB8NrfJ7NB7ivf5sUNOOVPME'; 

    $endpoint = $supabaseUrl . '/storage/v1/object/' . $bucketName . '/' . $filename;

    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $supabaseKey
    ]);
    
    curl_exec($ch);
    curl_close($ch);
}