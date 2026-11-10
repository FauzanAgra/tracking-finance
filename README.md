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
4. 📋 **[Tasks & Implementation Backlog](docs/TASKS.md)**  
   *Papan tugas terstruktur (47 tiket dalam 3 Gelombang) lengkap dengan kriteria penerimaan, checklist, dan tanggal selesai.*

---

## 🤖 Panduan untuk AI Coding Assistant (Claude, Cursor, Copilot, ChatGPT, dll.)

Jika kamu adalah AI Coding Assistant yang baru saja membuka repositori ini, **baca dan patuhi dokumen berikut sebelum menulis kode**:
* 👉 **[AGENTS.md](AGENTS.md)**: Berisi direktif wajib, aturan arsitektur Laravel, kepatuhan skema database, dan panduan membimbing pengguna.

---

## 🚀 Panduan Setup Lokal

```bash
# 1. Clone & Install Dependencies
composer install
npm install

# 2. Konfigurasi Environment
cp .env.example .env
php artisan key:generate

# 3. Jalankan Migrasi Database & Seeder
php artisan migrate --seed

# 4. Jalankan Dev Server
php artisan serve
npm run dev
```
