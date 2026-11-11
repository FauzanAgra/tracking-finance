# Stitch Screen Mapping & Laravel Blade Conversion Guide (`STITCH_SCREEN_MAP.md`)
## Project: Tracking Finance (Personal Wealth, Cashflow & Investment Hub)

* **Versi Dokumen**: 1.0.0
* **Status**: Disetujui (Mobile-First First + Dual Mode Light/Dark)
* **Folder Export Lokal (di-ignore Git)**: [`stitch_export/`](stitch_export/)
* **Design System Export Stitch**: `docs/stitch_export/design_system/DESIGN.md`
* **Dokumentasi Terkait**:
  - [Design System & UI Blueprint](DESIGN.md)
  - [Google Stitch Master Prompts](STITCH_PROMPTS.md)
  - [Tasks & Backlog Board](TASKS.md)
  - [PRD (Product Requirements)](PRD.md)

---

## 1. Struktur Folder Lokal `docs/stitch_export/`

Seluruh export dari Google Stitch (40 layar + 1 Design System) sudah dirapikan menjadi 3 kelompok agar konsisten di seluruh tahap pengerjaan Wave 1–3:

```text
docs/stitch_export/
├── design_system/
│   └── DESIGN.md                  # Token warna, skala font Space Grotesk, radius & shadow Stitch
├── light/                         # 20 layar versi COINEST Light Mode
│   ├── 01_login/                  # (code.html + screen.png)
│   ...
│   └── 20_profile_settings/
└── dark/                          # 20 layar versi Obsidian Pine Dark Mode
    ├── 01_login/
    ...
    └── 20_profile_settings/
```

---

## 2. Tabel Pemetaan Resmi Layar → Tiket `TASKS.md` & Blade Views

> **Catatan Konversi Umum**:
> 1. Gabungkan pasangan `light/XX_*` dan `dark/XX_*` ke dalam **1 file Blade** menggunakan class `dark:` (`@custom-variant dark`).
> 2. **Buang mockup HP** (frame `max-w-[390px] h-[844px] rounded-[40px]` serta fake iOS status bar `09:41` + ikon sinyal/baterai).
> 3. **Terapkan Guardrail `PRD.md`**: Hilangkan tombol dekoratif *"Sidik Jari / Face ID"* di Login & Profil karena WebAuthn tidak masuk scope `PRD.md`.

| No | Layar / Modal | Path Referensi (`light/` & `dark/`) | Tiket `TASKS.md` | Target Blade View (Rencana) | Penyesuaian Khusus Saat Konversi Blade |
|:---:|---|---|---|---|---|
| **01** | **Login** | `01_login/` | `AUTH-002` | `resources/views/auth/login.blade.php` | Hapus tombol *Face ID / Biometrik*, ganti badge versi statis jika perlu, sambungkan `@error` & `old('email')`. |
| **02** | **Register** | `02_register/` | `AUTH-002`, `AUTH-003` | `resources/views/auth/register.blade.php` | Pilih `base_currency` (`IDR`/`USD`) simpan ke hidden input / Alpine state sebelum submit form. |
| **03** | **Forgot Password** | `03_forgot_password/` | `AUTH-001`, `AUTH-002` | `resources/views/auth/forgot-password.blade.php` (+ `reset-password.blade.php`) | Tampilkan alert status session Laravel (`session('status')`). |
| **04** | **Home Dashboard** | `04_home_dashboard/` | `SETUP-004`, `DASH-001`–`003` | `resources/views/dashboard.blade.php` | Rapikan spasi legend donut, gunakan tahun/data dinamis dari DB (bukan contoh statis 2024). |
| **05** | **Wallets & Valas** | `05_wallets_valas/` | `WALL-002` | `resources/views/wallets/index.blade.php` | Filter tab `Semua`, `IDR`, `USD`, `USDT` via Alpine; tombol copy no rekening + set default via form/PATCH. |
| **06** | **Modal Tambah Dompet** | `06_modal_wallet_form/` | `WALL-003` | `resources/views/wallets/partials/wallet-modal.blade.php` | Gunakan Alpine modal/bottom-sheet mendukung mode Create & Edit (`PUT /wallets/{id}`). |
| **07** | **Modal Transfer & Kurs** | `07_modal_transfer_fx/` | `TXN-002`, `TXN-003` | `resources/views/transactions/partials/transfer-modal.blade.php` | Hitung preview kurs otomatis (`amount_out / amount_in`) saat beda mata uang via Alpine.js. |
| **08** | **Modal Catat Cepat 3 Detik** | `08_modal_quick_transaction/` | `TXN-002` | `resources/views/components/quick-transaction-modal.blade.php` | Default tanggal = hari ini & dompet = `is_default`, chip nominal cepat (`+10rb`, `+50rb`, `+100rb`), link ke Scan AI. |
| **09** | **Riwayat Transaksi** | `09_transaction_history/` | `TXN-004`, `FIN-001` | `resources/views/transactions/index.blade.php` | Integrasi search, filter bulan/dompet/kategori, tombol Export CSV (`FIN-001`), dan active Bottom Nav. |
| **10** | **Budgeting & Kategori** | `10_budgeting_categories/` | `TXN-001`, `TXN-005` | `resources/views/budgets/index.blade.php` & `categories/` | Tab `Budget Bulanan` & `Kelola Kategori`; warna progress bar `<70%` hijau, `70–99%` amber, `≥100%` merah. |
| **11** | **Utang & Piutang** | `11_debts_receivables/` | `DEBT-002`, `DEBT-004` | `resources/views/debts/index.blade.php` | Filter tab Piutang/Utang/Lunas, tampilkan timeline histori cicilan (`debt_payments`) + tombol bayar & write-off. |
| **12** | **Modal Catat Cicilan** | `12_modal_debt_payment/` | `DEBT-003` | `resources/views/debts/partials/payment-modal.blade.php` | Pill shortcut nominal `Lunasi` = sisa tagihan, pilih dompet penerima/pembayar (`wallet_id`). |
| **13** | **Modal Ikhlaskan Sisa** | `13_modal_debt_writeoff/` | `DEBT-005` | `resources/views/debts/partials/writeoff-modal.blade.php` | Preview `screen.png` light sempat terpotong kanan — ikuti layout `code.html` center/bottom sheet rapi. |
| **14** | **Financial Goals** | `14_financial_goals/` | `GOAL-001`–`003` | `resources/views/goals/index.blade.php` | Perbaiki typo `"Tercapail"` → `"Tercapai"`, pisahkan drawer setoran menjadi modal Alpine terpisah, hapus light `screen.png` note. |
| **15** | **Portofolio Investasi** | `15_investment_portfolio/` | `INV-004`, `INV-005` | `resources/views/investments/index.blade.php` | Perbaiki badge wrapping (`IDX • IDR`), tampilkan `Qty Lot (Lembar)`, Avg DCA, Unrealized PnL, tombol Refresh Harga. |
| **16** | **Modal Transaksi Aset** | `16_modal_investment_trade/` | `INV-002` | `resources/views/investments/partials/trade-modal.blade.php` | Kalkulator live Alpine untuk konversi Lot (`× 100`), toggle Broker Fee otomatis (`0.15%` beli / `0.25%` jual), & estimasi Avg DCA. |
| **17** | **Modal Edit Harga Manual** | `17_modal_manual_price/` | `INV-006` | `resources/views/investments/partials/manual-price-modal.blade.php` | Referensi visual lengkap di `code.html` (tidak ada `screen.png`), tambahkan tombol Reset ke API (`is_manual_price = false`). |
| **18** | **AI Scan Struk (Upload)** | `18_ai_scan_upload/` | `AI-002` | `resources/views/receipts/create.blade.php` | Hapus ikon `<` berlebih di pil `Powered by Gemini`, gunakan input file `capture="environment"` untuk kamera HP. |
| **19** | **AI Scan Struk (Verifikasi)** | `19_ai_scan_verify/` | `AI-003` | `resources/views/receipts/verify.blade.php` | Form auto-fill editable hasil JSON Gemini sebelum disimpan ke tabel `transactions` via `DB::transaction`. |
| **20** | **Profil & Pengaturan** | `20_profile_settings/` | `AUTH-003`, `FIN-001` | `resources/views/profile/edit.blade.php` | Toggle Light/Dark (`tf_theme` di `localStorage`), pilih `base_currency` (`IDR`/`USD`), ubah password, hapus toggle Biometrik/Face ID, fix active Nav. |
