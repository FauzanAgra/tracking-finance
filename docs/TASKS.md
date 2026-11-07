# Project Task Board & Implementation Backlog
## Tracking Finance: Personal Wealth, Cashflow & Investment Hub

* **Total Tugas**: 41 Tiket
* **Status Progres**: 0 / 41 Selesai (0%)
* **Dokumentasi Terkait**:
  - [PRD (Product Requirements)](PRD.md)
  - [ERD (Database Schema)](ERD.md)
  - [Tech Spec & Architecture](TECH_SPEC.md)

---

## 📌 Panduan Pembaruan Status Tugas
1. Setiap kali suatu sub-tugas selesai dikerjakan dan diverifikasi, ganti tanda `[ ]` menjadi `[x]`.
2. Cantumkan hash commit Git yang menyelesaikan tiket tersebut pada kolom `Commit`.
3. Seluruh commit wajib menggunakan format Conventional Commits: `<type>(<scope>): <subject>`.
4. Jika ada tugas tambahan di luar daftar ini, diskusikan terlebih dahulu dengan pemilik proyek sebelum menambahkannya.

---

## 🚀 Fase 0: Setup Lingkungan & Inisialisasi Proyek (`SETUP`)

| Status | ID Tiket | Nama Tugas | Kriteria Selesai (Acceptance Criteria) | Commit |
|:---:|---|---|---|:---:|
| [ ] | `SETUP-001` | Inisialisasi Proyek Laravel 13 & Git | Project Laravel 13 berhasil dibuat, Git repo terinisialisasi. | - |
| [ ] | `SETUP-002` | Konfigurasi `.env` & Koneksi MySQL 8.0 | Koneksi ke database `tracking_finance` lokal berhasil (`php artisan db:show`). | - |
| [ ] | `SETUP-003` | Setup Tailwind CSS v4.3 via `@tailwindcss/vite` | Asset Vite berhasil dikompilasi dengan Tailwind CSS v4 (`npm run build`). | - |
| [ ] | `SETUP-004` | Setup Alpine.js & Komponen Dasar Blade | Alpine.js aktif di `resources/js/app.js` dan layout dasar Blade siap. | - |

---

## 🔐 Fase 1: Autentikasi & Preferensi Pengguna (`AUTH`)

| Status | ID Tiket | Nama Tugas | Kriteria Selesai (Acceptance Criteria) | Commit |
|:---:|---|---|---|:---:|
| [ ] | `AUTH-001` | Instalasi Laravel Breeze (Blade Stack) | Login, Register, Forgot Password, Reset Password berfungsi dengan Blade. | - |
| [ ] | `AUTH-002` | Styling Tampilan Auth dengan Tailwind CSS | Halaman login & register memiliki desain clean, responsif, dan rapi. | - |
| [ ] | `AUTH-003` | Tambah Kolom `base_currency` pada User | Kolom `base_currency` ada di tabel `users` (default: 'IDR') dan dapat diubah di profile. | - |

---

## 🗄️ Fase 2: Database Schema & Migrations (`DB`)

| Status | ID Tiket | Nama Tugas | Kriteria Selesai (Acceptance Criteria) | Commit |
|:---:|---|---|---|:---:|
| [ ] | `DB-001` | Migration & Model `wallets` | Tabel dompet dengan kolom `currency`, `balance`, `type`, dsb. | - |
| [ ] | `DB-002` | Migration & Model `categories` & `budgets` | Tabel kategori (income/expense) dan batas budget bulanan (`YYYY-MM`). | - |
| [ ] | `DB-003` | Migration & Model `transactions` | Tabel transaksi lengkap dengan `destination_amount` dan `exchange_rate`. | - |
| [ ] | `DB-004` | Migration & Model `debts` & `debt_payments` | Tabel utang/piutang dan riwayat cicilan bertahap ke dompet tertentu. | - |
| [ ] | `DB-005` | Migration & Model `investment_assets` & `investment_transactions` | Tabel master aset (Saham IDX, US, Crypto) dan riwayat trade (support Lot). | - |
| [ ] | `DB-006` | Migration & Model `goals` & `goal_contributions` | Tabel target tabungan impian dan setoran saldo bertahap dari dompet. | - |
| [ ] | `DB-007` | Migration & Model `receipt_scans` & `system_exchange_rates` | Tabel log scan struk Gemini AI dan kurs acuan USD/IDR untuk Net Worth. | - |
| [ ] | `DB-008` | Database Seeder Kategori Default | Seeder kategori default (Makanan, Transport, Gaji, dll.) dan kurs awal. | - |

---

## 💼 Fase 3: Modul Multi-Currency Wallets (`WALL`)

| Status | ID Tiket | Nama Tugas | Kriteria Selesai (Acceptance Criteria) | Commit |
|:---:|---|---|---|:---:|
| [ ] | `WALL-001` | Service Layer: `WalletService` | Logika mutasi saldo atomik (`DB::transaction`) dan validasi kecukupan saldo. | - |
| [ ] | `WALL-002` | CRUD & Tampilan Kartu Dompet | Menampilkan daftar dompet per mata uang (IDR, USD, USDT) dengan kartu Tailwind. | - |
| [ ] | `WALL-003` | Form Tambah / Edit Dompet | Form modal Alpine.js untuk tambah dompet baru (pilih warna, icon, saldo awal). | - |

---

## 💸 Fase 4: Arus Kas, Transaksi & Budgeting (`TXN`)

| Status | ID Tiket | Nama Tugas | Kriteria Selesai (Acceptance Criteria) | Commit |
|:---:|---|---|---|:---:|
| [ ] | `TXN-001` | CRUD Kategori Pengeluaran & Pemasukan | Kelola kategori dengan warna badge dan icon yang bisa disesuaikan pengguna. | - |
| [ ] | `TXN-002` | Form Pencatatan Transaksi (Income & Expense) | Input transaksi pengeluaran/pemasukan dengan auto-update saldo dompet. | - |
| [ ] | `TXN-003` | Fitur Transfer & Konversi Kurs Antar-Dompet | Transfer IDR ke USDT/USD dengan pencatatan kurs transaksi secara akurat. | - |
| [ ] | `TXN-004` | Tabel Riwayat Transaksi & Filter | Tabel riwayat transaksi dengan pencarian, filter tanggal, dompet, kategori, & pagination. | - |
| [ ] | `TXN-005` | Modul Budgeting Bulanan & Indikator Visual | Progress bar pemakaian anggaran belanja (Hijau <70%, Kuning 70-99%, Merah ≥100%). | - |

---

## 🤝 Fase 5: Utang & Piutang Cicilan Bertahap - Kasus Jamal (`DEBT`)

| Status | ID Tiket | Nama Tugas | Kriteria Selesai (Acceptance Criteria) | Commit |
|:---:|---|---|---|:---:|
| [ ] | `DEBT-001` | Service Layer: `DebtService` | Logika cicilan, auto-update saldo dompet penerima, dan update status `paid`. | - |
| [ ] | `DEBT-002` | Daftar Utang & Piutang | Tampilan daftar orang yang berutang/kita utangi dengan badge status pembayaran. | - |
| [ ] | `DEBT-003` | Modal Input Pembayaran Cicilan | Form pembayaran bertahap (pilih tanggal, nominal, dan dompet masuk/keluar). | - |
| [ ] | `DEBT-004` | Log Histori Pembayaran per Orang | Tampilan rincian histori cicilan (contoh: Jumat 200k Cash, Sabtu 100k BCA). | - |

---

## 🎯 Fase 6: Financial Goals & Wishlist Tabungan (`GOAL`)

| Status | ID Tiket | Nama Tugas | Kriteria Selesai (Acceptance Criteria) | Commit |
|:---:|---|---|---|:---:|
| [ ] | `GOAL-001` | CRUD Target Tabungan (Goals) | Buat target impian (nama target, nominal target, deadline, mata uang). | - |
| [ ] | `GOAL-002` | Setoran Dana ke Goal | Alokasi uang dari dompet terpilih ke brankas goal (memotong saldo dompet). | - |
| [ ] | `GOAL-003` | Progress Bar & Estimasi Cerdas | Tampilan persentase progres dan kalkulasi estimasi sisa waktu target tercapai. | - |

---

## 📈 Fase 7: Portfolio Investasi Multi-Aset (`INV`)

| Status | ID Tiket | Nama Tugas | Kriteria Selesai (Acceptance Criteria) | Commit |
|:---:|---|---|---|:---:|
| [ ] | `INV-001` | Service Layer: `InvestmentService` (DCA Math) | Perhitungan Weighted Average Cost, konversi Lot Saham IDX (1 lot = 100 lembar). | - |
| [ ] | `INV-002` | Form Transaksi Beli/Jual Aset | Input transaksi beli/jual saham IDX, US, atau crypto terhubung ke dompet dana. | - |
| [ ] | `INV-003` | Integrasi API Harga Pasar: `MarketPriceService` | Fetch data CoinGecko (Crypto) dan Yahoo Finance (Saham IDX & US). | - |
| [ ] | `INV-004` | Dashboard Portfolio & Floating PnL | Tampilan keuntungan/kerugian belum terealisasi (*Unrealized PnL* % dan Nominal). | - |
| [ ] | `INV-005` | Tombol Refresh Harga & Scheduler Otomatis | Tombol manual *"🔄 Refresh Harga"* dan Artisan Command untuk cron harian. | - |

---

## 🧾 Fase 8: AI Receipt Scanner via Gemini API (`AI`)

| Status | ID Tiket | Nama Tugas | Kriteria Selesai (Acceptance Criteria) | Commit |
|:---:|---|---|---|:---:|
| [ ] | `AI-001` | Service Layer: `GeminiReceiptService` | Kirim foto struk ke Google Gemini Vision API dan parse JSON terstruktur. | - |
| [ ] | `AI-002` | UI Upload Struk Drag & Drop | Komponen upload gambar nota dengan preview gambar interaktif. | - |
| [ ] | `AI-003` | Form Verifikasi & Konfirmasi Transaksi | Form auto-fill dari respon AI untuk ditinjau pengguna sebelum disimpan ke database. | - |

---

## 💎 Fase 9: Dashboard Total Net Worth & Analisis Visual (`DASH`)

| Status | ID Tiket | Nama Tugas | Kriteria Selesai (Acceptance Criteria) | Commit |
|:---:|---|---|---|:---:|
| [ ] | `DASH-001` | Kalkulasi Total Kekayaan Bersih (Net Worth) | Menggabungkan saldo kas + portofolio investasi + piutang - utang ke dalam IDR. | - |
| [ ] | `DASH-002` | Grafik Donat Distribusi Pengeluaran | Chart.js interaktif untuk melihat porsi pengeluaran berdasarkan kategori. | - |
| [ ] | `DASH-003` | Grafik Arus Kas Bulanan (Cashflow Trend) | Chart tren pemasukan vs pengeluaran bulanan. | - |

---

## 🧹 Fase 10: Polishing, Export Laporan & QA (`FIN`)

| Status | ID Tiket | Nama Tugas | Kriteria Selesai (Acceptance Criteria) | Commit |
|:---:|---|---|---|:---:|
| [ ] | `FIN-001` | Fitur Export Laporan ke CSV / Excel | Download riwayat transaksi terfilter ke dalam file CSV/Excel. | - |
| [ ] | `FIN-002` | Uji Responsivitas Mobile & Tablet | Memastikan seluruh halaman terlihat rapi di layar ponsel dan desktop. | - |
| [ ] | `FIN-003` | Verifikasi Build Produksi & Test Otomatis | Jalankan `npm run build` dan `php artisan test` tanpa warning/error. | - |
