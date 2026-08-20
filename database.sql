-- 1. Hapus CREATE DATABASE dan USE karena Supabase menggunakan satu database default ('postgres')

-- 2. Buat Fungsi Trigger untuk menggantikan 'ON UPDATE CURRENT_TIMESTAMP' milik MySQL
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
   NEW.updated_at = NOW();
   RETURN NEW;
END;
$$ language 'plpgsql';

-- 3. Buat Tabel Admins
CREATE TABLE IF NOT EXISTS admins (
    id SERIAL PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 4. Buat Tabel Profiles
CREATE TABLE IF NOT EXISTS profiles (
    id SMALLINT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    subtitle VARCHAR(255) NOT NULL,
    bio TEXT NOT NULL,
    email VARCHAR(150) DEFAULT '',
    instagram VARCHAR(255) DEFAULT '',
    whatsapp VARCHAR(50) DEFAULT '',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Pasang trigger update waktu untuk profiles
CREATE TRIGGER update_profiles_modtime
BEFORE UPDATE ON profiles
FOR EACH ROW
EXECUTE FUNCTION update_updated_at_column();

-- 5. Buat Tabel Portfolios
CREATE TABLE IF NOT EXISTS portfolios (
    id SERIAL PRIMARY KEY,
    category VARCHAR(100) NOT NULL UNIQUE,
    title VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    image VARCHAR(255) DEFAULT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Pasang trigger update waktu untuk portfolios
CREATE TRIGGER update_portfolios_modtime
BEFORE UPDATE ON portfolios
FOR EACH ROW
EXECUTE FUNCTION update_updated_at_column();

-- 6. Insert Data
INSERT INTO admins (username, password)
VALUES ('admin', '$2y$12$L1muhFGfif8WAfiCBb8tj.pfDWCsb3odma7P6jU.8pmSXveX5GRrm')
ON CONFLICT (username) DO NOTHING;

INSERT INTO profiles (id, name, subtitle, bio, email, instagram, whatsapp)
VALUES (
    1,
    'Aininna Halizah Rahma',
    'Master of Ceremony • Public Speaker • Voice Actor • Reviewer',
    'A warm voice, confident presence, and stories worth listening to. Selamat datang di PORTOFOLIZAH — ruang untuk mengenal karya, pengalaman, dan karakter suara Aininna Halizah Rahma.',
    'hello@example.com',
    '@aininnah',
    '6281234567890'
)
ON CONFLICT (id) DO NOTHING;

INSERT INTO portfolios (category, title, description, image, sort_order)
VALUES
('Master of Ceremony', 'Master of Ceremony', 'Membawakan acara dengan energi, elegansi, dan komunikasi yang hangat.', NULL, 1),
('Public Speaker', 'Public Speaker', 'Menyampaikan ide dengan struktur yang jelas, ekspresif, dan relatable.', NULL, 2),
('Voice Actor', 'Voice Actor', 'Eksplorasi karakter, emosi, dan warna suara untuk berbagai kebutuhan audio.', NULL, 3),
('Reviewer', 'Reviewer', 'Mengulas produk, karya, maupun pengalaman dengan sudut pandang yang jujur dan engaging.', NULL, 4)
ON CONFLICT (category) DO NOTHING;