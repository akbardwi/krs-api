# Tutorial Instalasi Aplikasi dengan Laravel 12

## Prasyarat
- PHP >= 8.2
- Composer
- MySQL/MariaDB/PostgreSQL
- Node.js & npm (opsional, untuk frontend)

## Langkah Instalasi

### 1. Clone Repository
```bash
git clone https://github.com/akbardwi/krs-api
cd krs-api
```

### 2. Install Dependency
```bash
composer install
```

### 3. Copy File Environment
```bash
cp .env.example .env
```

### 4. Konfigurasi Environment
Edit file `.env` sesuai konfigurasi database dan aplikasi Anda.

### 5. Generate Key
```bash
php artisan key:generate
```

### 6. Migrasi Database & Import data dummy via seeder
```bash
php artisan migrate --seed
```
### 7. Jalankan Server
```bash
php artisan serve
```

Akses aplikasi di `http://localhost:8000`.

---

**Catatan:**  
Pastikan semua prasyarat sudah terpasang sebelum menjalankan langkah di atas.
