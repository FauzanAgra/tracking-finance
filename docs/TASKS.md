# Project Task Board & Implementation Backlog
## Tracking Finance: Personal Wealth, Cashflow & Investment Hub

* **Total Tugas**: 47 Tiket (Dibagi menjadi 3 Gelombang Prioritas)
* **Status Progres**: 15 / 47 Selesai (32%)
* **Dokumentasi Terkait**:
  - [PRD (Product Requirements v1.2.0)](PRD.md)
  - [ERD (Database Schema v1.2.0)](ERD.md)
  - [Tech Spec & Architecture](TECH_SPEC.md)
  - [Design System & UI Blueprint](DESIGN.md)

---

## 📌 Panduan Pembaruan Status Tugas
1. Setiap kali suatu sub-tugas selesai dikerjakan dan diverifikasi, **sebelum melakukan commit**, ganti tanda `[ ]` menjadi `[x]` dan isi tanggal selesai pada kolom `Selesai` (format: `YYYY-MM-DD`).
2. Gabungkan perubahan `docs/TASKS.md` bersama file kode tugas tersebut dalam **1 commit tunggal** (*single atomic commit*).
3. Seluruh commit wajib menggunakan format Conventional Commits: `<type>(<scope>): <subject>` yang mewakili nama tugasnya (tanpa mencantumkan ID tiket di pesan commit).
4. **Catatan UI/UX**: Seluruh implementasi tampilan Blade + Tailwind CSS v4 wajib mematuhi token warna Dual Mode, tata letak Mobile-First Adaptif, dan blueprint komponen di [docs/DESIGN.md](DESIGN.md) serta referensi visual di [docs/ref_design/](ref_design/).

---

# 🌊 GELOMBANG 1: Core Habit / MVP (Fokus Utama Sekarang)
> *Tujuan: Membangun fondasi yang solid agar aplikasi langsung bisa dipakai harian untuk mencatat saldo dompet, pemasukan/pengeluaran, dan utang-piutang si Jamal.*

## 🚀 Fase 0: Setup Lingkungan & Inisialisasi Proyek (`SETUP`)

| Status | ID Tiket | Nama Tugas | Kriteria Selesai (Acceptance Criteria) | Selesai |
|:---:|---|---|---|:---:|
| [x] | `SETUP-001` | Inisialisasi Proyek Laravel 13 & Git | Project Laravel 13 berhasil dibuat, Git repo tetap terjaga tanpa menimpa `docs/`. | 2026-11-10 |
| [x] | `SETUP-002` | Konfigurasi `.env` & Koneksi MySQL 8.0 | Koneksi ke database `tracking_finance` lokal berhasil (`php artisan db:show`). | 2026-11-10 |
| [x] | `SETUP-003` | Setup Tailwind CSS v4.3 via `@tailwindcss/vite` | Asset Vite berhasil dikompilasi dengan Tailwind CSS v4 (`npm run build`). | 2026-11-10 |
| [x] | `SETUP-004` | Setup Alpine.js & Layout Dasar | Alpine.js aktif di `resources/js/app.js` dan layout dasar Mobile-First Blade siap. | 2026-11-10 |

## 🔐 Fase 1: Autentikasi & Preferensi Pengguna (`AUTH`)

| Status | ID Tiket | Nama Tugas | Kriteria Selesai (Acceptance Criteria) | Selesai |
|:---:|---|---|---|:---:|
| [x] | `AUTH-001` | Instalasi Laravel Breeze (Blade Stack) | Login, Register, Forgot Password, Reset Password berfungsi dengan Blade. | 2026-11-11 |
| [x] | `AUTH-002` | Styling Tampilan Auth dengan Tailwind CSS | Halaman login & register memiliki desain clean dan responsif di layar HP. | 2026-11-15 |
| [x] | `AUTH-003` | Tambah Kolom `base_currency` pada User | Kolom `base_currency` ada di tabel `users` (default: 'IDR') dan dapat diubah di profile. | 2026-11-15 |

## 🗄️ Fase 2A: Database Schema Inti Gelombang 1 (`DB`)

| Status | ID Tiket | Nama Tugas | Kriteria Selesai (Acceptance Criteria) | Selesai |
|:---:|---|---|---|:---:|
| [x] | `DB-001` | Migration & Model `wallets` | Tabel dompet lengkap dengan kolom `currency`, `balance`, `type`, dan `is_default`. | 2026-11-15 |
| [x] | `DB-002` | Migration & Model `categories` | Tabel kategori (`income` / `expense`) beserta icon dan warna badge. | 2026-11-15 |
| [x] | `DB-003` | Migration & Model `transactions` | Tabel transaksi mendukung income, expense, transfer, dan field exchange rate. | 2026-11-15 |
| [x] | `DB-004` | Migration & Model `debts` & `debt_payments` | Tabel utang/piutang (include `written_off_amount`) dan histori cicilan per dompet. | 2026-11-15 |
| [x] | `DB-005` | Database Seeder Kategori Default | Seeder kategori bawaan (Makanan, Transport, Gaji, Sedekah/Write-off, dll.). | 2026-11-15 |

## 💼 Fase 3: Modul Multi-Dompet IDR (`WALL`)

| Status | ID Tiket | Nama Tugas | Kriteria Selesai (Acceptance Criteria) | Selesai |
|:---:|---|---|---|:---:|
| [x] | `WALL-001` | Service Layer: `WalletService` | Logika mutasi saldo atomik (`DB::transaction`) dan validasi kecukupan saldo. | 2026-11-15 |
| [x] | `WALL-002` | CRUD & Tampilan Kartu Dompet | Daftar dompet beserta fitur set **Dompet Default (`is_default`)** untuk input cepat. | 2026-11-15 |
| [x] | `WALL-003` | Form Tambah / Edit Dompet | Form modal Alpine.js untuk tambah/edit dompet (nama, tipe, warna, saldo awal). | 2026-11-15 |

## 💸 Fase 4A: Arus Kas & Transaksi Harian (`TXN`)

| Status | ID Tiket | Nama Tugas | Kriteria Selesai (Acceptance Criteria) | Selesai |
|:---:|---|---|---|:---:|
| [ ] | `TXN-001` | CRUD Kategori Pengeluaran & Pemasukan | Kelola kategori dengan warna badge dan icon yang bisa disesuaikan pengguna. | - |
| [ ] | `TXN-002` | Form Input Transaksi Cepat (Smart Default) | Input income/expense/transfer (same-currency) dengan default tanggal hari ini & dompet utama. | - |
| [ ] | `TXN-004` | Tabel Riwayat Transaksi & Filter | Daftar riwayat transaksi mobile-friendly dengan pencarian, filter tanggal, dompet, & kategori. | - |

## 🤝 Fase 5: Utang & Piutang Cicilan Bertahap - Kasus Jamal (`DEBT`)

| Status | ID Tiket | Nama Tugas | Kriteria Selesai (Acceptance Criteria) | Selesai |
|:---:|---|---|---|:---:|
| [ ] | `DEBT-001` | Service Layer: `DebtService` | Logika cicilan, auto-update saldo dompet penerima, dan update status `paid`. | - |
| [ ] | `DEBT-002` | Daftar Utang & Piutang | Tampilan daftar orang yang berutang/kita utangi dengan badge status pembayaran. | - |
| [ ] | `DEBT-003` | Modal Input Pembayaran Cicilan | Form pembayaran bertahap (pilih tanggal, nominal, dan dompet masuk/keluar). | - |
| [ ] | `DEBT-004` | Log Histori Pembayaran per Orang | Tampilan rincian histori cicilan (contoh: Jumat 200k Cash, Sabtu 100k BCA). | - |
| [ ] | `DEBT-005` | Fitur "Ikhlaskan Sisa Utang" (Write-Off) | Tombol untuk menandai sisa pinjaman dianggap impas (`written_off`) secara bersih. | - |

---

# 🌊 GELOMBANG 2: Wealth, Multi-Currency & Growth
> *Tujuan: Mengaktifkan konversi kurs (IDR ke USDT/USD), budgeting bulanan, target tabungan (Goals), dan portofolio investasi multi-aset.*

## 🗄️ Fase 2B: Database Schema Lanjutan (`DB`)

| Status | ID Tiket | Nama Tugas | Kriteria Selesai (Acceptance Criteria) | Selesai |
|:---:|---|---|---|:---:|
| [ ] | `DB-006` | Migration & Model `budgets` & `system_exchange_rates` | Tabel batas budget bulanan (`YYYY-MM`) dan kurs acuan USD/IDR. | - |
| [ ] | `DB-007` | Migration & Model `goals` & `goal_contributions` | Tabel target tabungan impian dan setoran saldo bertahap dari dompet. | - |
| [ ] | `DB-008` | Migration & Model `investment_assets` & `investment_transactions` | Tabel master aset (`is_manual_price`) dan riwayat trade (support Lot IDX). | - |

## 💱 Fase 4B: Konversi Valas & Budgeting (`TXN`)

| Status | ID Tiket | Nama Tugas | Kriteria Selesai (Acceptance Criteria) | Selesai |
|:---:|---|---|---|:---:|
| [ ] | `TXN-003` | Fitur Transfer & Konversi Kurs Antar-Dompet | Transfer IDR ke USDT/USD dengan pencatatan kurs transaksi secara akurat. | - |
| [ ] | `TXN-005` | Modul Budgeting Bulanan & Indikator Visual | Progress bar pemakaian anggaran belanja (Hijau <70%, Kuning 70-99%, Merah ≥100%). | - |

## 🎯 Fase 6: Financial Goals & Wishlist Tabungan (`GOAL`)

| Status | ID Tiket | Nama Tugas | Kriteria Selesai (Acceptance Criteria) | Selesai |
|:---:|---|---|---|:---:|
| [ ] | `GOAL-001` | CRUD Target Tabungan (Goals) | Buat target impian (nama target, nominal target, deadline, mata uang). | - |
| [ ] | `GOAL-002` | Setoran Dana ke Goal | Alokasi uang dari dompet terpilih ke brankas goal (memotong saldo dompet). | - |
| [ ] | `GOAL-003` | Progress Bar & Estimasi Cerdas | Tampilan persentase progres dan kalkulasi estimasi sisa waktu target tercapai. | - |

## 📈 Fase 7: Portfolio Investasi Multi-Aset (`INV`)

| Status | ID Tiket | Nama Tugas | Kriteria Selesai (Acceptance Criteria) | Selesai |
|:---:|---|---|---|:---:|
| [ ] | `INV-001` | Service Layer: `InvestmentService` (DCA Math) | Perhitungan Weighted Average Cost, konversi Lot Saham IDX (1 lot = 100 lembar). | - |
| [ ] | `INV-002` | Form Transaksi Beli/Jual Aset & Toggle Fee | Input transaksi beli/jual terhubung ke dompet dana + opsi fee broker otomatis. | - |
| [ ] | `INV-003` | Integrasi API Harga Pasar: `MarketPriceService` | Fetch data CoinGecko (Crypto) dan Yahoo Finance (Saham IDX & US). | - |
| [ ] | `INV-004` | Dashboard Portfolio & Floating PnL | Tampilan keuntungan/kerugian belum terealisasi (*Unrealized PnL* % dan Nominal). | - |
| [ ] | `INV-005` | Tombol Refresh Harga & Scheduler Otomatis | Tombol manual *"🔄 Refresh Harga"* dan Artisan Command untuk cron harian. | - |
| [ ] | `INV-006` | Fitur "Edit Harga Manual" (API Fallback) | Form edit harga aset manual (`is_manual_price`) apabila API eksternal down/error. | - |

---

# 🌊 GELOMBANG 3: Magic AI, Analytics & Finishing
> *Tujuan: Menambahkan otomatisasi scan struk dengan AI Gemini, penyatuan total kekayaan bersih (Net Worth) dalam IDR, grafik visual, dan QA.*

## 🗄️ Fase 2C: Database Schema AI (`DB`)

| Status | ID Tiket | Nama Tugas | Kriteria Selesai (Acceptance Criteria) | Selesai |
|:---:|---|---|---|:---:|
| [ ] | `DB-009` | Migration & Model `receipt_scans` | Tabel log hasil ekstraksi JSON foto struk dari Gemini Vision API. | - |

## 🧾 Fase 8: AI Receipt Scanner via Gemini API (`AI`)

| Status | ID Tiket | Nama Tugas | Kriteria Selesai (Acceptance Criteria) | Selesai |
|:---:|---|---|---|:---:|
| [ ] | `AI-001` | Service Layer: `GeminiReceiptService` | Kirim foto struk ke Google Gemini Vision API dan parse JSON terstruktur. | - |
| [ ] | `AI-002` | UI Upload Struk Drag & Drop / Kamera HP | Komponen upload gambar nota dengan preview gambar interaktif. | - |
| [ ] | `AI-003` | Form Verifikasi & Konfirmasi Transaksi | Form auto-fill dari respon AI untuk ditinjau pengguna sebelum disimpan ke database. | - |

## 💎 Fase 9: Dashboard Total Net Worth & Analisis Visual (`DASH`)

| Status | ID Tiket | Nama Tugas | Kriteria Selesai (Acceptance Criteria) | Selesai |
|:---:|---|---|---|:---:|
| [ ] | `DASH-001` | Kalkulasi Total Kekayaan Bersih (Net Worth) | Menggabungkan saldo kas + portofolio investasi + piutang - utang ke dalam IDR. | - |
| [ ] | `DASH-002` | Grafik Donat Distribusi Pengeluaran | Chart.js interaktif untuk melihat porsi pengeluaran berdasarkan kategori. | - |
| [ ] | `DASH-003` | Grafik Arus Kas Bulanan (Cashflow Trend) | Chart tren pemasukan vs pengeluaran bulanan. | - |

## 🧹 Fase 10: Polishing, Export Laporan & QA (`FIN`)

| Status | ID Tiket | Nama Tugas | Kriteria Selesai (Acceptance Criteria) | Selesai |
|:---:|---|---|---|:---:|
| [ ] | `FIN-001` | Fitur Export Laporan ke CSV / Excel | Download riwayat transaksi terfilter ke dalam file CSV/Excel. | - |
| [ ] | `FIN-002` | Uji Responsivitas Mobile Web App | Memastikan seluruh halaman rapi di layar ponsel (390px+) dan desktop. | - |
| [ ] | `FIN-003` | Verifikasi Build Produksi & Test Otomatis | Jalankan `npm run build` dan `php artisan test` tanpa warning/error. | - |
