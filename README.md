# 📊 Tracking Finance

> **Personal Wealth, Cashflow, & Multi-Asset Investment Hub**  
> Dibangun dengan **Laravel 13**, **PHP 8.4**, **Tailwind CSS v4.3**, **Alpine.js**, dan **MySQL 8.0**.

---

## 🌟 Tentang Proyek

**Tracking Finance** adalah platform manajemen finansial pribadi yang komprehensif, mencakup:
* 💰 **Multi-Dompet & Multi-Currency**: Mengelola saldo Bank (BCA, Mandiri), Tunai, E-Wallet, dan saldo Valas/Crypto (USD, USDT) dengan fitur konversi kurs otomatis.
* 👥 **Pencatatan Utang & Piutang Bertahap**: Fitur cicilan pinjaman perorangan (kasus Jamal) dengan mutasi otomatis ke dompet yang berbeda-beda.
* 📈 **Portofolio Investasi Multi-Aset**: Melacak Saham Indonesia (IDX berbasis Lot), Saham Luar Negeri (US), dan Crypto dengan kalkulasi *Average Down (DCA)* dan *Floating Profit/Loss (Unrealized PnL)* real-time.
* 🎯 **Financial Goals**: Wishlist target menabung bertahap untuk barang impian atau dana darurat.
* 🧾 **AI Receipt Scanner**: Ekstraksi struk belanja otomatis menggunakan Google Gemini Vision API.
* 💎 **Total Net Worth Dashboard**: Kalkulasi kekayaan bersih terpadu yang menggabungkan seluruh saldo kas, nilai pasar investasi, dan piutang ke dalam Rupiah (IDR).

---

## 📚 Dokumentasi Proyek

Seluruh dokumentasi teknikal dan arsitektur tersimpan rapi di dalam folder [`docs/`](docs/):

1. 📄 **[Product Requirement Document (PRD)](docs/PRD.md)**  
   *Spesifikasi lengkap fitur, user stories, alur bisnis, rumus DCA, dan kalkulasi kurs.*
2. 🗄️ **[Entity Relationship Diagram (ERD)](docs/ERD.md)**  
   *Diagram visual Mermaid dan Kamus Data (Data Dictionary) lengkap untuk 12 tabel database.*
3. ⚙️ **[Technical Specification & Architecture (Tech Spec)](docs/TECH_SPEC.md)**  
   *Arsitektur Service Layer, kontrak API pihak ketiga (Gemini, CoinGecko, Yahoo), struktur folder, dan panduan koding.*
4. 🎨 **[Design System & UI/UX Blueprint (DESIGN.md)](docs/DESIGN.md)**  
   *Panduan UI Mobile-First Adaptif, palet Dual Mode Light/Dark (Forest & Mint), tata letak Bento, serta blueprint komponen utama (mengacu pada [`docs/ref_design/`](docs/ref_design/)).*
5. 📋 **[Tasks & Implementation Backlog](docs/TASKS.md)**  
   *Papan tugas terstruktur (47 tiket dalam 3 Gelombang) lengkap dengan kriteria penerimaan, checklist, dan tanggal selesai.*

---

## 🤖 Panduan untuk AI Coding Assistant (Claude, Cursor, Copilot, ChatGPT, dll.)

Jika kamu adalah AI Coding Assistant yang baru saja membuka repositori ini, **baca dan patuhi dokumen berikut sebelum menulis kode**:
* 👉 **[AGENTS.md](AGENTS.md)**: Berisi direktif wajib, aturan arsitektur Laravel, kepatuhan skema database, dan panduan membimbing pengguna.

---

## 🚀 Panduan Instalasi & Setup Lokal

### 1. Prasyarat Sistem
Pastikan perangkatmu sudah terinstall:
* **PHP** `>= 8.4` (ekstensi: `pdo_mysql`, `mbstring`, `openssl`, `curl`, `fileinfo`, `gd`)
* **Composer** `>= 2.x`
* **Node.js** `>= 20.x` & **NPM**
* **MySQL** `8.0+`

### 2. Clone & Install Dependencies
```bash
git clone <url-repositori-kamu>
cd tracking-finance
composer install
npm install
```

### 3. Konfigurasi Environment (`.env`)
Salin file `.env.example` menjadi `.env`, lalu buat application key:
```bash
cp .env.example .env
php artisan key:generate
```

Buat database baru di MySQL (contoh: `tracking_finance`), lalu sesuaikan konfigurasi berikut di `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tracking_finance
DB_USERNAME=root
DB_PASSWORD=

# Opsional — Wajib diisi jika ingin memakai fitur AI Receipt Scanner
GEMINI_API_KEY=isi_api_key_google_ai_studio_anda
GEMINI_MODEL=gemini-3.5-flash
```
> **Tips**: Kamu bisa mendapatkan `GEMINI_API_KEY` gratis melalui [Google AI Studio](https://aistudio.google.com/apikey).

### 4. Migrasi Database, Seeder & Storage Link
```bash
php artisan migrate --seed
php artisan storage:link
```

### 5. Build Asset & Jalankan Aplikasi
Untuk development:
```bash
php artisan serve
npm run dev
```
Atau untuk build produksi:
```bash
npm run build
php artisan serve
```
Aplikasi dapat diakses di `http://127.0.0.1:8000`.

### 6. Scheduler Harga Pasar & Pengujian (Opsional)
* **Refresh Harga Investasi Manual**:
  ```bash
  php artisan investments:refresh-prices
  ```
* **Jalankan Scheduler Lokal** (otomatis setiap hari pukul `16:30 WIB`):
  ```bash
  php artisan schedule:work
  ```
* **Jalankan Automated Test**:
  ```bash
  php artisan test
  ```
