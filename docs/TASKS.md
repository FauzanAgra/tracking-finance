# Project Task Board & Implementation Backlog
## Tracking Finance: Personal Wealth, Cashflow & Investment Hub

* **Total Tugas**: 60 Tiket (Dibagi menjadi 6 Gelombang Prioritas)
* **Status Progres**: 55 / 60 Selesai (91.7%)
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
| [x] | `TXN-001` | CRUD Kategori Pengeluaran & Pemasukan | Kelola kategori dengan warna badge dan icon yang bisa disesuaikan pengguna. | 2026-11-16 |
| [x] | `TXN-002` | Form Input Transaksi Cepat (Smart Default) | Input income/expense/transfer (same-currency) dengan default tanggal hari ini & dompet utama. | 2026-11-16 |
| [x] | `TXN-004` | Tabel Riwayat Transaksi & Filter | Daftar riwayat transaksi mobile-friendly dengan pencarian, filter tanggal, dompet, & kategori. | 2026-11-16 |

## 🤝 Fase 5: Utang & Piutang Cicilan Bertahap - Kasus Jamal (`DEBT`)

| Status | ID Tiket | Nama Tugas | Kriteria Selesai (Acceptance Criteria) | Selesai |
|:---:|---|---|---|:---:|
| [x] | `DEBT-001` | Service Layer: `DebtService` | Logika cicilan, auto-update saldo dompet penerima, dan update status `paid`. | 2026-11-16 |
| [x] | `DEBT-002` | Daftar Utang & Piutang | Tampilan daftar orang yang berutang/kita utangi dengan badge status pembayaran. | 2026-11-16 |
| [x] | `DEBT-003` | Modal Input Pembayaran Cicilan | Form pembayaran bertahap (pilih tanggal, nominal, dan dompet masuk/keluar). | 2026-11-16 |
| [x] | `DEBT-004` | Log Histori Pembayaran per Orang | Tampilan rincian histori cicilan (contoh: Jumat 200k Cash, Sabtu 100k BCA). | 2026-11-16 |
| [x] | `DEBT-005` | Fitur "Ikhlaskan Sisa Utang" (Write-Off) | Tombol untuk menandai sisa pinjaman dianggap impas (`written_off`) secara bersih. | 2026-11-16 |

---

# 🌊 GELOMBANG 2: Wealth, Multi-Currency & Growth
> *Tujuan: Mengaktifkan konversi kurs (IDR ke USDT/USD), budgeting bulanan, target tabungan (Goals), dan portofolio investasi multi-aset.*

## 🗄️ Fase 2B: Database Schema Lanjutan (`DB`)

| Status | ID Tiket | Nama Tugas | Kriteria Selesai (Acceptance Criteria) | Selesai |
|:---:|---|---|---|:---:|
| [x] | `DB-006` | Migration & Model `budgets` & `system_exchange_rates` | Tabel batas budget bulanan (`YYYY-MM`) dan kurs acuan USD/IDR. | 2026-11-16 |
| [x] | `DB-007` | Migration & Model `goals` & `goal_contributions` | Tabel target tabungan impian dan setoran saldo bertahap dari dompet. | 2026-11-16 |
| [x] | `DB-008` | Migration & Model `investment_assets` & `investment_transactions` | Tabel master aset (`is_manual_price`) dan riwayat trade (support Lot IDX). | 2026-11-16 |

## 💱 Fase 4B: Konversi Valas & Budgeting (`TXN`)

| Status | ID Tiket | Nama Tugas | Kriteria Selesai (Acceptance Criteria) | Selesai |
|:---:|---|---|---|:---:|
| [x] | `TXN-003` | Fitur Transfer & Konversi Kurs Antar-Dompet | Transfer IDR ke USDT/USD dengan pencatatan kurs transaksi secara akurat. | 2026-11-16 |
| [x] | `TXN-005` | Modul Budgeting Bulanan & Indikator Visual | Progress bar pemakaian anggaran belanja (Hijau <70%, Kuning 70-99%, Merah ≥100%). | 2026-11-16 |

## 🎯 Fase 6: Financial Goals & Wishlist Tabungan (`GOAL`)

| Status | ID Tiket | Nama Tugas | Kriteria Selesai (Acceptance Criteria) | Selesai |
|:---:|---|---|---|:---:|
| [x] | `GOAL-001` | CRUD Target Tabungan (Goals) | Buat target impian (nama target, nominal target, deadline, mata uang). | 2026-11-16 |
| [x] | `GOAL-002` | Setoran Dana ke Goal | Alokasi uang dari dompet terpilih ke brankas goal (memotong saldo dompet). | 2026-11-16 |
| [x] | `GOAL-003` | Progress Bar & Estimasi Cerdas | Tampilan persentase progres dan kalkulasi estimasi sisa waktu target tercapai. | 2026-11-16 |

## 📈 Fase 7: Portfolio Investasi Multi-Aset (`INV`)

| Status | ID Tiket | Nama Tugas | Kriteria Selesai (Acceptance Criteria) | Selesai |
|:---:|---|---|---|:---:|
| [x] | `INV-001` | Service Layer: `InvestmentService` (DCA Math) | Perhitungan Weighted Average Cost, konversi Lot Saham IDX (1 lot = 100 lembar). | 2026-11-16 |
| [x] | `INV-002` | Form Transaksi Beli/Jual Aset & Toggle Fee | Input transaksi beli/jual terhubung ke dompet dana + opsi fee broker otomatis. | 2026-11-16 |
| [x] | `INV-003` | Integrasi API Harga Pasar: `MarketPriceService` | Fetch data CoinGecko (Crypto) dan Yahoo Finance (Saham IDX & US). | 2026-11-16 |
| [x] | `INV-004` | Dashboard Portfolio & Floating PnL | Tampilan keuntungan/kerugian belum terealisasi (*Unrealized PnL* % dan Nominal). | 2026-11-16 |
| [x] | `INV-005` | Tombol Refresh Harga & Scheduler Otomatis | Tombol manual *"🔄 Refresh Harga"* dan Artisan Command untuk cron harian. | 2026-11-16 |
| [x] | `INV-006` | Fitur "Edit Harga Manual" (API Fallback) | Form edit harga aset manual (`is_manual_price`) apabila API eksternal down/error. | 2026-11-16 |

---

# 🌊 GELOMBANG 3: Magic AI, Analytics & Finishing
> *Tujuan: Menambahkan otomatisasi scan struk dengan AI Gemini, penyatuan total kekayaan bersih (Net Worth) dalam IDR, grafik visual, dan QA.*

## 🗄️ Fase 2C: Database Schema AI (`DB`)

| Status | ID Tiket | Nama Tugas | Kriteria Selesai (Acceptance Criteria) | Selesai |
|:---:|---|---|---|:---:|
| [x] | `DB-009` | Migration & Model `receipt_scans` | Tabel log hasil ekstraksi JSON foto struk dari Gemini Vision API. | 2026-11-16 |

## 🧾 Fase 8: AI Receipt Scanner via Gemini API (`AI`)

| Status | ID Tiket | Nama Tugas | Kriteria Selesai (Acceptance Criteria) | Selesai |
|:---:|---|---|---|:---:|
| [x] | `AI-001` | Service Layer: `GeminiReceiptService` | Kirim foto struk ke Google Gemini Vision API dan parse JSON terstruktur. | 2026-11-16 |
| [x] | `AI-002` | UI Upload Struk Drag & Drop / Kamera HP | Komponen upload gambar nota dengan preview gambar interaktif. | 2026-11-16 |
| [x] | `AI-003` | Form Verifikasi & Konfirmasi Transaksi | Form auto-fill dari respon AI untuk ditinjau pengguna sebelum disimpan ke database. | 2026-11-16 |

## 💎 Fase 9: Dashboard Total Net Worth & Analisis Visual (`DASH`)

| Status | ID Tiket | Nama Tugas | Kriteria Selesai (Acceptance Criteria) | Selesai |
|:---:|---|---|---|:---:|
| [x] | `DASH-001` | Kalkulasi Total Kekayaan Bersih (Net Worth) | Menggabungkan saldo kas + portofolio investasi + piutang - utang ke dalam IDR. | 2026-11-16 |
| [x] | `DASH-002` | Grafik Donat Distribusi Pengeluaran | Chart.js interaktif untuk melihat porsi pengeluaran berdasarkan kategori. | 2026-11-16 |
| [x] | `DASH-003` | Grafik Arus Kas Bulanan (Cashflow Trend) | Chart tren pemasukan vs pengeluaran bulanan. | 2026-11-16 |

## 🧹 Fase 10: Polishing, Export Laporan & QA (`FIN`)

| Status | ID Tiket | Nama Tugas | Kriteria Selesai (Acceptance Criteria) | Selesai |
|:---:|---|---|---|:---:|
| [x] | `FIN-001` | Fitur Export Laporan ke CSV / Excel | Download riwayat transaksi terfilter ke dalam file CSV/Excel. | 2026-11-16 |
| [x] | `FIN-002` | Uji Responsivitas Mobile Web App | Memastikan seluruh halaman rapi di layar ponsel (390px+) dan desktop. | 2026-11-16 |
| [x] | `FIN-003` | Verifikasi Build Produksi & Test Otomatis | Jalankan `npm run build` dan `php artisan test` tanpa warning/error. | 2026-11-16 |
| [x] | `FIN-004` | Styling Halaman Profil & Pengaturan | Merapikan `/profile` ke shell mobile Stitch Dual Mode + tombol Logout & akses modul. | 2026-11-16 |


---

# 🌊 GELOMBANG 4: Polish & UX Enhancement
> *Tujuan: Meningkatkan kenyamanan pemakaian harian — feedback visual yang lebih baik, keamanan hapus data, performa list transaksi, dan kemampuan edit transaksi yang sudah tercatat.*

## 🧹 Fase 11: Peningkatan UX & Performa (`IMV`)

| Status | ID Tiket | Nama Tugas | Kriteria Selesai (Acceptance Criteria) | Selesai |
|:---:|---|---|---|:---:|
| [x] | `IMV-001` | Reusable Delete Confirmation Modal | Semua tombol hapus di 7 halaman pakai modal Alpine.js terpusat, bukan browser `confirm()`. | 2026-11-22 |
| [x] | `IMV-002` | Flash Toast Notification Global | Pesan sukses & error muncul sebagai toast animasi dari bawah layar, auto-dismiss 3,5 detik. | 2026-11-22 |
| [x] | `IMV-003` | Pagination Riwayat Transaksi | Daftar transaksi di-paginate 20/halaman; statistik total tetap dihitung dari seluruh data filter. | 2026-11-22 |
| [x] | `IMV-004` | Edit & Update Transaksi | Tombol ✏️ Edit di tiap baris membuka bottom-sheet modal pre-filled; saldo dompet diperbarui atomik. | 2026-11-22 |


---

# 🌊 GELOMBANG 5: Growth & Advanced Analytics (Tier 2)
> *Tujuan: Menyediakan histori kekayaan visual, otomatisasi pencatatan transaksi rutin, analisis mendalam per dompet dan aset investasi, serta ekspor laporan PDF formal.*

## 📊 Fase 12: Analisis Lanjutan & Otomatisasi (`ADV`)

| Status | ID Tiket | Nama Tugas | Kriteria Selesai (Acceptance Criteria) | Selesai |
|:---:|---|---|---|:---:|
| [x] | `ADV-001` | Grafik Riwayat Net Worth (Net Worth History Chart) | Snapshot saldo berkala via scheduler + visualisasi tren garis kekayaan di Dashboard. | 2026-11-22 |
| [x] | `ADV-002` | Transaksi Rutin / Berulang (Recurring Transactions) | Template transaksi terjadwal (bulanan/mingguan) dengan eksekusi mutasi otomatis via cron. | 2026-11-22 |
| [x] | `ADV-003` | Halaman Rincian Dompet (Wallet Detail View) | Klik kartu dompet membuka detail saldo, tren mutasi, dan riwayat transaksi dompet terkait. | 2026-11-22 |
| [ ] | `ADV-004` | Histori Harga & Sparkline Portofolio Investasi | Simpan histori pergerakan harga pasar saham/crypto + visualisasi mini chart tren per aset. | - |
| [ ] | `ADV-005` | Ekspor Laporan Keuangan ke Format PDF | Download rangkuman arus kas dan rincian transaksi dalam format dokumen PDF resmi yang rapi. | - |

---

# 🌊 GELOMBANG 6: Intelligence & Collaboration (Tier 3)
> *Tujuan: Membawa asisten AI interaktif, sistem pengingat otomatis proaktif, dan kemampuan multi-pengguna untuk pengelolaan keuangan keluarga/pasangan.*

## 🤖 Fase 13: Magic AI & Keuangan Kolaboratif (`EXP`)

| Status | ID Tiket | Nama Tugas | Kriteria Selesai (Acceptance Criteria) | Selesai |
|:---:|---|---|---|:---:|
| [ ] | `EXP-001` | Sistem Notifikasi & Pengingat Cerdas (Smart Alerts) | Peringatan in-app saat budget hampir habis (>80%), utang mendekati tempo, atau goal tercapai. | - |
| [ ] | `EXP-002` | AI Financial Advisor / Chat (Gemini Powered) | Dialog interaktif dengan AI Gemini untuk analisis kebiasaan belanja dan estimasi masa depan. | - |
| [ ] | `EXP-003` | Multi-User & Keuangan Keluarga (Household Finance) | Dukungan satu rumah tangga dengan peran akun terpisah (owner & anggota keluarga). | - |
