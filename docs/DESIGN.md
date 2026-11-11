# Design System & UI/UX Blueprint (`DESIGN.md`)
## Project: Tracking Finance (Personal Wealth, Cashflow & Investment Hub)

* **Versi Dokumen**: 1.1.0
* **Status**: Disetujui (Mobile-First Priority + Dual Mode Light/Dark — Selaras Export Google Stitch)
* **Stack UI**: Laravel Blade + Tailwind CSS v4.3 (`@tailwindcss/vite`) + Alpine.js v3 + Chart.js v4
* **Referensi Visual**:
  - [Ref 1: Mobile Bento Pastel UI](ref_design/ref_design_1.jpg) *(Tata letak bento mobile, angka KPI besar, floating bottom dock, avatar kontak)*
  - [Ref 2: COINEST Fintech Dashboard](ref_design/ref_design_2.jpg) *(Palet warna Forest & Mint Green, kartu dompet Visa/Mastercard, Finance Score bar, Expense Breakdown ring, Cashflow spline chart, Saving Plans, ikon outline bersih)*
  - [Peta Layar Export Google Stitch](STITCH_SCREEN_MAP.md) *(20 pasang layar Light/Dark Mode Mobile di `docs/stitch_export/`)*
* **Dokumentasi Terkait**:
  - [PRD (Product Requirements)](PRD.md)
  - [ERD (Database Schema & Dictionary)](ERD.md)
  - [Technical Specification & Architecture](TECH_SPEC.md)
  - [Google Stitch Master Prompts](STITCH_PROMPTS.md)
  - [Stitch Screen Map](STITCH_SCREEN_MAP.md)
  - [Tasks & Backlog Board](TASKS.md)

---

## 1. Filosofi Desain & Strategi Responsif

Aplikasi ini dirancang dengan prinsip **Mobile-First First** (utamakan UI Mobile selesai & nyaman dipakai harian lebih dulu, lalu *refinement* Desktop dilakukan bertahap):
1. **Fase Eksekusi Utama — Smartphone (`390px+`)**:
   - Pencatatan pengeluaran harian, foto struk belanja, dan pencatatan cicilan utang teman (*kasus Jamal*) terjadi saat beraktivitas.
   - Seluruh 20 pasang halaman/modal dibangun presisi mengikuti export **Google Stitch** (`max-w-md mx-auto` rapi bila diakses dari browser desktop selama fase awal).
   - Memprioritaskan **angka besar yang langsung terbaca sekilas**, tombol ramah jempol (*thumb-zone*), **Floating Bottom Navigation Dock**, dan input transaksi $\le 3$ detik.
2. **Fase Ekspansi Bertahap — Desktop (`md:` / `lg:`)**:
   - Setelah seluruh tampilan & alur fitur mobile berfungsi solid, tata letak layar lebar (`1280px+`) dapat ditingkatkan secara bertahap menjadi **Left Sidebar (`250px`) + Bento Grid 2–3 Kolom**.
3. **Dual Mode Theme (Light & Dark Mode)**:
   - Mendukung pergantian tema **Light Mode (COINEST)** dan **Dark Mode (Obsidian Pine & Mint)** secara instan melalui class `.dark` di `<html>` dan penyimpanan `localStorage` (`tf_theme`).

---

## 2. Sistem Warna (Dual Mode Color Tokens)

Identitas warna mengambil kesan **segar, tenang, terpercaya, dan profesional** dari **COINEST (`ref_design_2.jpg`)**: perpaduan **Deep Forest Green** dan **Fresh Lime/Mint Green** di atas kanvas bersih.

### 2.1 Palet Light Mode (COINEST Signature)

| Token CSS | Hex | Kegunaan Utama |
|---|---|---|
| `--color-canvas` | `#F5F8F4` | Latar belakang utama halaman (*soft sage off-white*) |
| `--color-sidebar` | `#EEF4EC` | Latar belakang sidebar desktop & panel lembut |
| `--color-surface` | `#FFFFFF` | Permukaan utama Bento Card (*crisp white*) |
| `--color-surface-tint` | `#EFF6EC` | Kartu sekunder (Mastercard wallet, icon well, Finance Score tint) |
| `--color-border-subtle` | `#E3ECE1` | Garis tepi kartu (`1px`) & pemisah tabel halus |
| `--color-brand-forest` | `#1B4332` | Warna brand primer: sidebar logo, kartu dompet utama, fill bar utama, kurva Expense |
| `--color-brand-forest-deep` | `#122D21` | Hover state tombol primer & gradasi kartu dompet gelap |
| `--color-accent-lime` | `#BDF08E` | Pill menu aktif sidebar, badge persen positif, kurva Income, bagian sisa bar Finance Score |
| `--color-accent-lime-soft` | `#E3F8CE` | Latar badge tren naik (`+1.78%`), highlight lembut, hover row |
| `--color-text-primary` | `#14241D` | Judul halaman, nominal saldo & angka KPI utama |
| `--color-text-secondary` | `#64746B` | Label kartu, subtitle, metadata tanggal, header tabel |
| `--color-text-muted` | `#8FA096` | Placeholder input, label sumbu grafik, teks non-aktif |

### 2.2 Palet Dark Mode (Obsidian Pine & Mint)

Pasangan tema gelap yang tetap menjaga DNA *Forest & Mint* agar mata nyaman saat malam hari memantau portofolio atau merekap transaksi:

| Token CSS (`.dark`) | Hex | Kegunaan Utama |
|---|---|---|
| `--color-canvas` | `#0A110E` | Latar belakang kanvas gelap (*deep obsidian pine*) |
| `--color-sidebar` | `#0F1915` | Sidebar desktop & bottom dock mobile gelap |
| `--color-surface` | `#14211B` | Permukaan utama Bento Card |
| `--color-surface-tint` | `#1B2D24` | Kartu sekunder, icon well gelap, background Finance Score |
| `--color-border-subtle` | `#243B30` | Garis batas kartu (`1px`) agar kartu tetap terpisah rapi |
| `--color-brand-forest` | `#1E4D38` | Permukaan kartu dompet utama (dibedakan dengan border `#2F6F52`) |
| `--color-accent-lime` | `#A8E874` | Aksen primer di mode gelap: menu aktif, CTA utama, kurva Income |
| `--color-accent-lime-soft` | `#1F3A2B` | Latar badge positif (`text-[#A8E874]`) |
| `--color-text-primary` | `#F1F6F3` | Nominal saldo, angka KPI, judul utama |
| `--color-text-secondary` | `#96ABA0` | Label, metadata transaksi, header kolom |
| `--color-text-muted` | `#647A6E` | Placeholder & garis bantu grafik |

### 2.3 Warna Semantik & Aturan Bisnis (`PRD.md`)

| Status / Konteks | Light Mode (Bg / Text) | Dark Mode (Bg / Text) | Aturan Penggunaan |
|---|---|---|---|
| **Income / Profit (`+PnL`) / Paid** | `#DCF7C6` / `#16422E` (atau solid `#1B4332` text `#FFF`) | `#1C3B2A` / `#86EFAC` | Pemasukan, floating profit saham/crypto, cicilan lunas |
| **Expense / Loss (`-PnL`) / Overbudget** | `#FFE4E6` / `#BE123C` | `#3B1822` / `#FDA4AF` | Pengeluaran, floating loss, budget $\ge 100\%$ |
| **Warning (`70%–99%` Budget) / Pending** | `#FEF3C7` / `#B45309` | `#3A2B12` / `#FCD34D` | Budget mendekati batas (`70%–99%`), status `partially_paid` |
| **Info / Neutral / Write-Off (`written_off`)** | `#E8EFEA` / `#47584F` | `#1E2B25` / `#A7B9AF` | Status `"Diikhlaskan"` (`written_off`), transfer antar dompet, `is_manual_price` |

### 2.4 Konfigurasi Tailwind CSS v4.3 Dual Mode (`resources/css/app.css`)

Di Tailwind CSS v4, mode `dark:` secara default membaca setting OS (*system preference*). Agar tombol **Toggle Dark/Light Mode** di aplikasi kita bisa bekerja manual melalui class `.dark` di elemen `<html>`, `resources/css/app.css` wajib menyertakan `@custom-variant dark`:

```css
@import 'tailwindcss';

/* Aktifkan manual dark mode menggunakan class .dark di tag <html> */
@custom-variant dark (&:where(.dark, .dark *));

@theme {
    --font-sans: 'Space Grotesk', ui-sans-serif, system-ui, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji',
        'Segoe UI Symbol', 'Noto Color Emoji';

    /* Light Mode (COINEST / Stitch Tokens) */
    --color-canvas-base: #F5F8F4;
    --color-surface-card: #FFFFFF;
    --color-surface-tint: #EFF6EC;
    --color-border-subtle: #E3ECE1;
    --color-forest-primary: #1B4332;
    --color-forest-deep: #122D21;
    --color-accent-lime: #BDF08E;
    --color-accent-lime-soft: #E3F8CE;
    --color-text-primary: #14241D;
    --color-text-secondary: #64746B;
    --color-text-muted: #8FA096;

    /* Dark Mode (Obsidian Pine & Mint / Stitch Tokens) */
    --color-dark-canvas: #0A110E;
    --color-dark-sidebar: #0F1915;
    --color-dark-surface: #14211B;
    --color-dark-surface-tint: #1B2D24;
    --color-dark-border: #243B30;
    --color-dark-forest: #1E4D38;
    --color-dark-lime: #A8E874;
    --color-dark-text-primary: #F1F6F3;
    --color-dark-text-secondary: #96ABA0;
    --color-dark-text-muted: #647A6E;

    /* Semantic Status Tokens */
    --color-semantic-profit-bg: #DCF7C6;
    --color-semantic-profit-text: #16422E;
    --color-semantic-loss-bg: #FFE4E6;
    --color-semantic-loss-text: #BE123C;
    --color-semantic-warning-bg: #FEF3C7;
    --color-semantic-warning-text: #B45309;
    --color-semantic-info-bg: #E8EFEA;
    --color-semantic-info-text: #47584F;
}

.material-symbols-outlined {
    font-family: 'Material Symbols Outlined';
    font-variation-settings: 'FILL' 0, 'wght' 450, 'GRAD' 0, 'opsz' 24;
    display: inline-block;
    vertical-align: middle;
    line-height: 1;
}

.material-symbols-outlined.fill-icon {
    font-variation-settings: 'FILL' 1, 'wght' 500, 'GRAD' 0, 'opsz' 24;
}

[x-cloak] {
    display: none !important;
}
```

---

## 3. Tipografi & Aturan Angka Finansial

* **Font Utama**: **`Space Grotesk`** (`400, 500, 600, 700` — di-bundle otomatis via `laravel-vite-plugin/fonts` `@fonts`, 100% selaras export Google Stitch).
* **Aturan Emas Angka (`tabular-nums`)**:
  Setiap angka uang, persentase profit/loss, jumlah lot, harga saham/crypto, dan saldo **wajib** memakai utility Tailwind `tabular-nums` (`font-variant-numeric: tabular-nums`) agar lebar setiap digit seragam dan kolom angka sejajar rapi saat dibandingkan vertikal.

| Level Teks | Kelas Tailwind Rekomendasi | Contoh Penggunaan |
|---|---|---|
| **Hero Balance / Net Worth** | `text-3xl sm:text-4xl font-bold tracking-tight tabular-nums` | Total Net Worth (`Rp 128.450.000`) |
| **Bento KPI Number** | `text-2xl font-bold tracking-tight tabular-nums` | Income bulan ini (`Rp 8.500.000`), Saldo Kartu (`Rp 14.250.000`) |
| **Page Title** | `text-xl sm:text-2xl font-semibold tracking-tight` | Judul halaman (`Dashboard`, `Dompet`, `Portofolio`) |
| **Card Title / Section** | `text-base font-semibold` | `"Cashflow"`, `"Finance Score"`, `"Saving Plans"` |
| **Body & Transaction Item** | `text-sm font-medium` | Nama transaksi, nama peminjam (`Jamal`), nama aset (`BBCA.JK`) |
| **Metadata & Badge** | `text-xs font-medium` | Tanggal (`10 Nov 2026`), badge `5 Lot (500 lbr)`, `+1.78%` |

### Konvensi Format Mata Uang
* **IDR**: `Rp 1.500.000` (tanpa desimal kecuali harga unit pecahan saham/rate; pemisah ribuan titik `.`).
* **USD**: `$1,250.50` (2 desimal).
* **USDT / Crypto Qty**: `31.64 USDT` atau `0.003500 BTC` (hapus trailing zero berlebih jika rapi, maksimal 6–8 desimal untuk crypto).
* **Saham IDX**: Selalu tampilkan jumlah **Lot** di depan dengan informasi lembar di sampingnya, contoh: `12 Lot (1.200 lbr)`.

---

## 4. Layout, Bento Geometry, & Breakpoints

Menggabungkan kelegaan kartu **Bento (`ref_design_1.jpg`)** dengan kerapian **Fintech Card (`ref_design_2.jpg`)**:

### 4.1 Radius & Elevation
* **Bento Container / Mobile Hero Card**: `rounded-3xl` (`24px`) — memberi nuansa ramah di layar HP (`ref_design_1.jpg`).
* **Standard Dashboard & Chart Card**: `rounded-2xl` (`16px`) — presisi untuk grid, tabel transaksi, dan kartu dompet ala Visa (`ref_design_2.jpg`).
* **Input Field, Button, Inner Row**: `rounded-xl` (`12px`).
* **Pills, Badges, Avatars, Progress Track**: `rounded-full`.
* **Border & Shadow**: Utamakan garis batas `border border-[#E3ECE1] dark:border-[#243B30]` dengan `shadow-xs` tipis. Hindari bayangan hitam tebal (*heavy drop shadow*).

### 4.2 Grid Responsif (Mobile $\rightarrow$ Desktop)

```text
+-----------------------------------------------------------------------+
| MOBILE (< 768px / 390px baseline)                                     |
|  - Top Header: Greeting / Page Title + Theme Toggle + Avatar          |
|  - Hero Net Worth / Total Saldo Card (Full Width rounded-3xl)         |
|  - Mini Bento Row: grid-cols-2 gap-3 (e.g. Income & Expense)          |
|  - Horizontal Scroll Snap Wallet Cards (Visa/Mastercard style)        |
|  - Stacked Bento Sections (Finance Score, Jamal Debts, Goals, Txns)   |
|  - Floating / Sticky Bottom Pill Nav + Center "+" Quick Add Button    |
+-----------------------------------------------------------------------+
| DESKTOP (md: 768px+ & lg: 1024px+)                                    |
|  [ Left Sidebar (250px) ] | [ Top Bar: Search + Notifications + User] |
|  - Logo Tracking Finance  | [ 12-Col Bento Dashboard Grid           ] |
|  - Nav Items + Lime Pill  |   - Top KPI Strip (4 cards) + Score/Card  |
|  - Quick AI Scan Card     |   - Cashflow Chart (lg:col-span-5)        |
|                           |   - Expense Breakdown (lg:col-span-4)     |
|                           |   - Balance Wallets (lg:col-span-3)       |
|                           |   - Recent Txns + Goals + Debts Bento Grid|
+-----------------------------------------------------------------------+
```

---

## 5. Spesifikasi Komponen Utama (Component Blueprint)

Setiap komponen di bawah ini dipetakan langsung ke fitur resmi di [PRD](PRD.md) dan kolom di [ERD](ERD.md):

### 5.1 Adaptive Navigation Shell
* **Desktop (`md:flex`)**: Sidebar kiri `w-64` berlatar `#EEF4EC` (dark: `#0F1915`). Menu aktif memakai **Lime Pill** (`bg-[#BDF08E] text-[#14241D] font-semibold rounded-xl` — dark: `bg-[#A8E874] text-[#0A110E]`). Bagian bawah sidebar memiliki kartu gelap mini (*callout*) untuk akses cepat **AI Scan Struk (Gemini)**.
* **Mobile (`md:hidden`)**: Floating Bottom Navigation Dock (`rounded-full bg-white/95 dark:bg-[#14211B]/95 backdrop-blur border shadow-lg`) berisi 4 menu inti (*Home, Dompet, Utang/Goals, Investasi*) dan 1 tombol menonjol **`+`** (`bg-[#1B4332] dark:bg-[#A8E874]`) untuk modal **Input Cepat $\le 3$ detik** atau **Scan Struk**.

### 5.2 Wallet Cards (Gaya Visa/Mastercard dari `ref_design_2.jpg`)
* **Struktur Header**: Judul `"Total Saldo"` di atas nominal besar `tabular-nums`, diikuti kartu-kartu rekening (`wallets`).
* **Varian Kartu Utama (`is_default = true` atau tipe `bank`)**:
  - Light & Dark: Latar Deep Forest Green (`bg-gradient-to-br from-[#1B4332] to-[#122D21] text-white`), ornamen lingkaran halus transparan di sudut kanan atas, badge ikon dompet/bank (`BCA`, `MANDIRI`, `RDN`), badge kecil `"Utama"`, nominal saldo besar, serta 4 digit terakhir `account_number` + tombol copy.
* **Varian Kartu Sekunder (`cash`, `e_wallet`, `investment_cash`)**:
  - Light: Latar Soft Mint (`bg-[#EFF6EC] text-[#14241D] border border-[#DCE8D9]`) dengan ikon badge Forest Green.
  - Dark: Latar Deep Moss (`bg-[#1B2D24] text-[#F1F6F3] border border-[#294336]`) dengan ikon badge Mint (`#A8E874`).
* **Badge Mata Uang**: Pill kecil `IDR`, `USD`, atau `USDT` agar user langsung sadar saat akan melakukan *Currency Exchange*.

### 5.3 Finance Score / Budget Health Bar (`"Excellent 92%"` dari `ref_design_2.jpg`)
Komponen favorit untuk memantau **kesehatan anggaran bulanan & rasio tabungan**:
* **Container**: `rounded-2xl p-5 bg-[#EFF6EC] dark:bg-[#1B2D24] border border-[#DCE8D9] dark:border-[#294336]`.
* **Header Bar**: Label kecil `"Finance Health"` di atas status tegas kiri (`"Excellent"`, `"Aman"`, `"Waspada"`, atau `"Overbudget"`) dan angka persen besar di kanan (`92%`, `tabular-nums`).
* **Signature 2-Tone Loading Bar**:
  - Batang progress tebal (`h-6 rounded-lg overflow-hidden flex gap-1`):
    - **Sisi Terisi**: Solid Deep Forest (`#1B4332` / dark `#34D399` atau `#23865F`) sesuai persentase skor/pemakaian.
    - **Sisi Sisa**: Fresh Lime-Mint (`#BDF08E` / dark `#243B30`) agar membentuk kontras dua-warna yang khas seperti referensi `ref_design_2.jpg`.
  - Saat modul **Budgeting Bulanan (`TXN-005`)** dipakai per kategori, warna sisi terisi mengikuti aturan warna PRD: Hijau/Forest (`< 70%`), Kuning Amber (`70%–99%`), Merah Rose ($\ge 100\%$).

### 5.4 Concentric Expense Breakdown (`ref_design_2.jpg`)
* **Visual Grafik**: Chart.js v4 **Concentric / Multi-Ring Donut** (atau donut bertingkat bersih) dengan sudut melengkung (`borderRadius: 999`, latar track lembut `#EEF3EC` / dark `#1F3027`).
* **Palet Segmen Chart**:
  1. Kategori #1 (Terbesar): `#1B4332` *(dark: `#A8E874`)*
  2. Kategori #2: `#BDF08E` *(dark: `#34D399`)*
  3. Kategori #3: `#8FA89B` *(dark: `#527968`)*
  4. Lainnya: `#D2DDD6` *(dark: `#2C4036`)*
* **Daftar Rincian di Bawah Grafik**:
  Setiap baris menampilkan kotak indikator warna + Nama Kategori + nominal (`Rp ...`) di kiri, dan badge persentase bertipe pill (`bg-[#EFF6EC] dark:bg-[#22362C] rounded-md px-2 py-0.5 tabular-nums`) di kanan.

### 5.5 Smooth Dual-Curve Cashflow Chart (`ref_design_2.jpg`)
* **Visual Grafik**: Chart.js v4 Line Chart bertipe kurva halus (`tension: 0.45`, `pointRadius: 0`, `pointHoverRadius: 5`).
* **Dua Seri Data**:
  - **Income (Pemasukan)**: Garis Fresh Lime (`#A8E874`) tebal `2.5px` dengan *vertical linear gradient area fill* transparan dari `rgba(189, 240, 142, 0.35)` ke `0`.
  - **Expense (Pengeluaran)**: Garis Deep Forest (`#1B4332` — di dark mode gunakan Mint Teal `#34D399` atau `#5EEAD4` agar kontras di background gelap!) tebal `2.5px` dengan area fill sangat halus.
* **Tooltip Interaktif**: Kartu kecil mengambang (`bg-white dark:bg-[#1B2D24] border shadow-md rounded-xl p-2.5`) menampilkan tipe, nominal `tabular-nums`, dan tanggal.

### 5.6 Social Debt & Cicilan Cards ("Kasus Jamal" — `ref_design_1.jpg` + `ref_design_2.jpg`)
* **Ringkasan Teman (`ref_design_1.jpg`)**: Deretan avatar inisial bulat (`rounded-full`) kontak yang paling aktif berutang/berpiutang (misal *Jamal*, *Budi*) lengkap dengan sisa nominal di bawah nama agar bisa langsung diklik untuk bayar cicilan.
* **Kartu Detail Cicilan**:
  - Menampilkan badge `Piutang` (hijau) / `Utang` (rose), total pokok, total terbayar, sisa utang, dan progress bar.
  - **Timeline Histori Cicilan**: List vertikal menampilkan tanggal, nominal masuk/keluar, dan badge **dompet tujuan** (misal `Cash`, `BCA`).
  - Tombol aksi sekunder: **"+ Catat Cicilan"** dan opsi **"Ikhlaskan Sisa (`written_off`)"** dengan dialog konfirmasi yang jelas.

### 5.7 Portofolio Investasi Multi-Aset & Financial Goals
* **Investment Card / Table**:
  - Kolom/baris per aset menampilkan: Ticker badge (`BBCA.JK`, `AAPL`, `BTC`), tipe aset pill (`IDX Lot` / `US` / `Crypto`), Qty (`10 Lot (1.000 lbr)`), Harga Rata-rata (*Avg Price*), Harga Terkini, dan pill **Unrealized PnL** (`+Rp 1.250.000 / +8.4%` atau `-2.1%`).
  - Jika `is_manual_price = true`, tampilkan badge kecil netral `"Harga Manual"` di dekat harga pasar sebagai pengingat fallback.
  - Header portofolio menyediakan tombol **"🔄 Refresh Harga"**.
* **Saving Plans / Financial Goals (`ref_design_2.jpg`)**:
  - Ikon target dalam kotak lembut + judul goal (misal *Laptop Baru*), progress bar 2 warna, teks `"Rp 6.300.000 / Rp 18.000.000"` di kiri, persentase `"35%"` di kanan, serta estimasi cerdas (`"± 8 bulan lagi"`).

---

## 6. Ikonografi & Micro-Interactions (Alpine.js)

### 6.1 Gaya Ikon (100% Selaras Export Google Stitch)
* Gunakan **Google Material Symbols Outlined** (`wght,FILL@100..700,0..1`, default `FILL: 0, wght: 450`). Gunakan modifier `.fill-icon` (`FILL: 1`) khusus untuk ikon aktif di Bottom Navigation Dock atau badge tertentu.
* Ikon kategori transaksi dan aktivitas dibungkus dalam kotak membulat lembut (`w-10 h-10 rounded-xl bg-surface-tint dark:bg-dark-surface-tint text-forest-primary dark:text-dark-lime flex items-center justify-center`).

### 6.2 Pola Interaksi Alpine.js
* **Mobile Bottom Sheet vs Desktop Modal**:
  - Form tambah transaksi, bayar cicilan Jamal, dan edit harga manual tampil sebagai **Bottom Sheet** (*muncul dari bawah layar*, `rounded-t-3xl`) di HP (`max-md:`), dan otomatis tampil sebagai **Centered Dialog Modal** (`md:rounded-2xl md:max-w-lg`) di layar laptop.
* **Transisi Halus & Anti-Kedip**:
  - Semua elemen `x-show` wajib memakai atribut `x-cloak` dan transisi halus `x-transition.opacity.duration.200ms`.

---

## 7. Checklist Verifikasi UI Sebelum Commit

Sebelum menyelesaikan tiket apa pun yang menyentuh tampilan (`resources/views/**/*.blade.php`), pastikan 6 poin berikut lolos pemeriksaan:
1. [ ] **Mobile-First Fit**: Tampil rapi tanpa scroll horizontal pada viewport `390px` (iPhone/Android standar) dengan tombol mudah dijangkau jempol.
2. [ ] **Desktop Clean Container / Expansion**: Rapi & nyaman dilihat di browser desktop (`max-w-md` terpusat pada tahap Mobile-First, hingga Sidebar + Bento Grid ditambahkan pada tahap ekspansi Desktop).
3. [ ] **Dual Mode Contrast**: Terbaca jelas baik di **Light Mode** maupun saat class `.dark` aktif di `<html>`.
4. [ ] **Tabular Numerals**: Semua angka saldo, persentase, lot, dan harga menggunakan `tabular-nums`.
5. [ ] **Zero Out-Of-Topic UI**: Tidak menampilkan fitur/widget fiktif yang tidak ada di `docs/PRD.md`.
6. [ ] **Vite Build Clean**: `npm run build` selesai tanpa error/warning CSS.
