# Master Prompts Google Stitch — Tracking Finance
## Blueprint Prompt Lengkap per Halaman, Fitur, Modal, & Tombol

* **Versi Dokumen**: 1.0.0
* **Acuan Utama**: [DESIGN.md](DESIGN.md) | [PRD.md](PRD.md) | [ERD.md](ERD.md) | [ref_design/](ref_design/)
* **Strategi Layout**: **Mobile-First Adaptif (`390px` baseline)** + **Desktop (`1440px`)**
* **Bahasa UI (*Microcopy*)**: Campuran Natural Indonesia–Inggris (*Fintech Indonesia modern*)

---

## 📌 Cara Menggunakan Dokumen Ini di Google Stitch (`stitch.withgoogle.com`)

1. **Mulai proyek baru di Google Stitch**, lalu **selalu salin Bagian 0 (*Master System Prompt*)** ke prompt pertama atau *Custom Instructions / Design System* agar seluruh layar memiliki warna, sudut kartu, font, dan komponen yang konsisten.
2. **Generate versi Mobile (`390x844`) terlebih dahulu** untuk setiap layar (`Prompt A`), karena 80% aktivitas pencatatan dilakukan di smartphone.
3. Setelah versi Mobile sesuai selera, gunakan prompt **Desktop Variant (`Prompt B`)** atau minta Stitch mengadaptasi layar tersebut ke lebar `1440px` dengan Left Sidebar.
4. Untuk menguji **Dark Mode**, tempel bagian tambahan **Dark Mode Override Snippet** di akhir prompt layar mana pun.

---

## Bagian 0: Master System Prompt (Global Brand & Style Lock)

```text
Act as an expert Fintech UI/UX Product Designer. Design high-craft, clean, production-grade mobile and responsive web screens for "Tracking Finance" — an all-in-one personal cashflow, multi-currency wallet, social debt, investment portfolio, and financial goals hub.

STRICT DESIGN SYSTEM & VISUAL TOKENS:
- Visual Direction: Hybrid of crisp Fintech Dashboard aesthetic (clean white/sage surfaces, forest & lime-mint green trust palette, outline icons, realistic minimalist wallet cards) and modern Mobile Bento Grid ergonomics (generous rounded corners, large legible numbers, thumb-friendly bottom dock).
- Light Mode Palette:
  - App Background Canvas: #F5F8F4 (soft sage off-white)
  - Sidebar / Subtle Panel: #EEF4EC
  - Bento Card Surface: #FFFFFF with crisp 1px border #E3ECE1 and subtle xs shadow
  - Secondary / Tinted Card: #EFF6EC
  - Primary Brand Color: #1B4332 (Deep Forest Green) & #122D21 hover
  - Highlight Accent: #BDF08E (Fresh Lime Mint) for active pills, positive trend tags, primary chart accent
  - Primary Text: #14241D | Secondary Muted Text: #64746B
- Dark Mode Palette (for Dual Mode):
  - Canvas: #0A110E | Sidebar: #0F1915 | Bento Card: #14211B (1px border #243B30) | Tinted Card: #1B2D24
  - Brand Forest Card: #1E4D38 | Bright Accent: #A8E874 | Primary Text: #F1F6F3 | Secondary: #96ABA0
- Semantic Colors:
  - Profit / Income / Safe Budget (<70%): #DCF7C6 bg with #16422E text (or solid #1B4332 white text)
  - Warning (70-99% Budget) / Partial: #FEF3C7 bg with #B45309 text
  - Loss / Expense / Overbudget (>=100%): #FFE4E6 bg with #BE123C text
  - Neutral / Write-Off ("Diikhlaskan") / Manual Price Tag: #E8EFEA bg with #47584F text
- Typography: Instrument Sans geometric sans-serif. Every currency figure, percentage, stock lot, and crypto quantity MUST use crisp tabular numerals (tabular-nums).
- Geometry & Icons:
  - Major hero bento cards: 24px radius (rounded-3xl). Standard cards & charts: 16px radius (rounded-2xl). Buttons & inputs: 12px radius (rounded-xl). Badges, pills, progress bars, avatars: full pill (rounded-full).
  - Clean thin outline icons (Lucide style, 1.75px stroke) housed inside soft #EFF6EC rounded-xl icon containers.
- Copy Language: Natural Indonesian–English Fintech Mix (e.g., "Total Net Worth", "Dompet", "Catat Cepat", "Piutang Jamal", "Ikhlaskan Sisa", "Saving Plans", "Harga Manual").
- Currency Conventions: IDR uses "Rp 1.500.000", USD uses "$1,250.50", Crypto uses "0.0035 BTC" / "31.64 USDT", IDX Stocks always display Lots with shares conversion e.g. "10 Lot (1.000 lbr)".
```

### Dark Mode Override Snippet *(Tempel di akhir prompt jika ingin men-generate versi Dark Mode)*
```text
THEME OVERRIDE — RENDER THIS EXACT SCREEN IN DARK MODE ("Obsidian Pine & Mint"):
Replace canvas with #0A110E, sidebar with #0F1915, card surfaces with #14211B and subtle 1px borders #243B30, soft mint tinted cards with #1B2D24, primary hero wallet card in #1E4D38 emerald gradient, active navigation pills and primary highlights in luminous lime-mint #A8E874 with dark #0A110E label text, primary numerals in #F1F6F3, and secondary text in #96ABA0. Ensure high WCAG contrast.
```

---

## Bagian 1: Autentikasi (`AUTH-001` & `AUTH-002`)

### Prompt 1A — Halaman Login (Mobile `390x844`)
```text
Apply the Tracking Finance Design System. Design the Mobile Login Screen (390x844).
Top Section:
- Clean #F5F8F4 sage canvas with a minimal top-right Dark/Light theme toggle icon button.
- Centered brand lockup: custom geometric leaf/wallet emblem in #1B4332 Deep Forest Green paired with fresh lime #BDF08E accent, brand title "Tracking Finance", and concise subtitle "Personal Wealth, Cashflow & Investment Hub".
Main Bento Form Card (#FFFFFF rounded-3xl, 1px border #E3ECE1, generous 24px padding):
- Heading: "Masuk ke Akunmu" + subtext "Kelola dompet, piutang, dan portofolio dalam satu tempat."
- Input 1: Label "Email", rounded-xl input field with envelope outline icon, placeholder "nama@email.com".
- Input 2: Label "Password", rounded-xl input field with lock outline icon, masked dots, and a clickable right eye outline icon to show/hide password.
- Utility row: Left checkbox "Ingat saya", Right text link in #1B4332 "Lupa password?".
- Primary CTA Button: Full-width 48px tall rounded-xl button in #1B4332 Deep Forest Green with crisp white bold label "Masuk".
- Bottom divider & footer inside/below card: "Belum punya akun?" with bold link "Daftar Sekarang".
```

### Prompt 1B — Halaman Register & Lupa Password (Mobile `390x844`)
```text
Apply the Tracking Finance Design System. Design two clean mobile auth screens side-by-side (390x844 each):
1) Register Screen:
- Top-left back arrow icon button inside soft white circle + title "Buat Akun Baru".
- Bento card (#FFFFFF rounded-3xl, border #E3ECE1) containing fields:
  - "Nama Lengkap" (user outline icon)
  - "Email" (mail icon)
  - "Mata Uang Utama (Base Currency)" selector pills: "IDR (Rupiah)" active in #1B4332 + "USD ($)" inactive in #EFF6EC
  - "Password" (with show/hide toggle)
  - "Konfirmasi Password"
- Full-width #1B4332 primary button: "Buat Akun".
- Bottom text link: "Sudah punya akun? Masuk".
2) Forgot Password Screen:
- Back button, soft mint icon circle with key-round outline icon.
- Heading "Reset Password" + helper text "Masukkan email terdaftar untuk menerima tautan atur ulang kata sandi."
- Email input field + full-width #1B4332 button "Kirim Tautan Reset" + secondary subtle link "Kembali ke Login".
```

---

## Bagian 2: Dashboard Utama & Shell Navigasi (`DASH` + `WALL` + `DEBT` + `GOAL`)

### Prompt 2A — Mobile Dashboard Utama (Home Bento Hub `390x844`)
```text
Apply the Tracking Finance Design System. Design the primary Mobile Home Dashboard (390x844) using a modern Bento Grid layout.
1. Sticky Mobile Header:
- Left: Circular user avatar + greeting "Halo, Agra" with subtitle "Ringkasan Keuangan".
- Right: 3 circular soft white icon buttons (40px, border #E3ECE1): Dark/Light mode moon/sun toggle, Search icon, Notification bell with small green dot.
2. Hero Bento Card — Total Net Worth (Full-width rounded-3xl, #1B4332 Deep Forest Green background with subtle organic mint curved highlight):
- Top row: Label "Total Net Worth (IDR)" + right pill badge "1 USD = Rp 15.850" (translucent mint tint).
- Big bold tabular-nums figure: "Rp 148.625.000" in crisp white, plus small green growth pill "+4.2% bulan ini".
- Mini breakdown row inside dark hero card separated by subtle translucent line: "Kas Rp 32.5M" | "Investasi Rp 115.7M" | "Piutang +Rp 400K".
3. 2x2 Mini KPI Bento Grid (White rounded-2xl cards, 1px border #E3ECE1, 12px gap):
- Card 1: Small mint icon well + "Income", bold "Rp 12.500.000", lime pill "+8.4%".
- Card 2: Small rose icon well + "Expense", bold "Rp 4.850.000", rose pill "-2.1%".
- Card 3 (Signature Finance Score from COINEST): Soft #EFF6EC tinted rounded-2xl card spanning full-width or paired cleanly: Top labels "Finance Health" + "Excellent" on left and "92%" bold on right, featuring a thick 20px-tall 2-tone pill progress bar (left 75% solid #1B4332 Forest Green, remaining segment solid #BDF08E Lime Mint).
4. Wallet Carousel ("Dompet Saya" + right link "Lihat Semua >"):
- Horizontal snap-scrolling Visa/Mastercard-inspired balance cards:
  - Card 1 (Default): #1B4332 dark forest gradient, badge "BCA • Utama ★", white bold "Rp 18.450.000", masked "•••• 9967" + copy icon.
  - Card 2: #EFF6EC pastel mint card, badge "Binance • USDT", dark forest bold "420.50 USDT", subtext "≈ Rp 6.664.925".
  - Card 3 peeking at right: "Tunai / Cash — Rp 1.250.000".
5. Social Debt Quick Strip ("Utang & Piutang" + "Kelola >"):
- Inspired by friendly avatar strips: Row of circular avatars with remaining amount tags: "Jamal (+100K)", "Budi (+250K)", and "+ Catat".
6. Cashflow Dual Smooth Chart Card (White rounded-2xl):
- Header "Cashflow" + pill dropdown "7 Hari v" + legend dots (Lime "Income", Forest "Expense").
- Smooth dual cubic spline area chart (lime green income line above dark forest expense line) with floating white callout pill tooltip showing "+Rp 3.200.000".
7. Expense Breakdown Card & Saving Plans Mini Card:
- Concentric multi-ring radial donut chart (#1B4332 outer, #BDF08E middle, #8FA89B inner) with category legend rows showing percentage pills ("50%", "30%", "20%").
- Saving Plans mini row for "Laptop Baru — 35% (Rp 6.3M / 18M)".
8. Floating Bottom Pill Navigation Dock (Sticky bottom with safe padding):
- Rounded-full frosted white container (#FFFFFF/95, border #E3ECE1, soft shadow) containing 5 slots:
  - "Home" (active lime-mint pill highlight)
  - "Dompet"
  - Center raised circular "+" FAB button in #1B4332 Forest Green with lime glow ring (triggers Quick Add <=3s sheet)
  - "Utang"
  - "Invest"
```

### Prompt 2B — Desktop Adaptive Dashboard (`1440x900`)
```text
Apply the Tracking Finance Design System. Design the Desktop Adaptive Dashboard (1440x900) following the exact COINEST web structure:
1. Left Sidebar (256px wide, #EEF4EC soft sage background):
- Top brand logo mark + "Tracking Finance".
- Vertical navigation menu list with crisp outline icons:
  - "Dashboard" (ACTIVE: bright #BDF08E Lime Mint rounded-xl pill with #14241D bold text)
  - "Dompet & Valas"
  - "Transaksi"
  - "Budgeting"
  - "Utang & Piutang" (small counter pill "2")
  - "Investasi"
  - "Financial Goals"
  - "AI Scan Struk"
  - "Pengaturan"
- Bottom-left dark callout card (#1B4332 rounded-2xl, subtle leaf pattern): Icon camera/sparkle, text "Foto Struk Belanja, Otomatis Tercatat AI Gemini", and full-width bright lime CTA button "Scan Struk AI".
2. Top Header Bar:
- Left: Page title "Dashboard" + Net Worth summary badge "Net Worth: Rp 148.625.000".
- Right: Search bar rounded-full (#FFFFFF border #E3ECE1) "Cari transaksi, aset, atau nama...", Dark/Light theme toggle icon button, Notification bell, and User Avatar + "Agra".
3. Main 12-Column Bento Content Grid:
- Row 1 (Left 8 cols): 2x2 KPI Cards ("Total Income", "Total Expense", "Net Cashflow", "Investment PnL" with +/- green/rose pills) + Top-Right (4 cols): "Finance Score" tinted #EFF6EC card with "Excellent 92%" & 2-tone Forest/Lime bar.
- Row 2:
  - Col 1-5: "Cashflow" dual smooth spline chart ("Income" lime vs "Expense" forest, dropdown "Bulan Ini v", interactive floating hover tooltip).
  - Col 6-9: "Expense Breakdown" concentric multi-ring donut chart with category legend + percentage pills (50%, 30%, 20%).
  - Col 10-12: "Balance / Wallets" panel with Total Balance + stacked Visa/Mastercard-style wallet cards (Dark Forest "BCA ★ Utama", Pastel Mint "Binance USDT").
- Row 3:
  - Col 1-6: "Recent Transactions" clean table (Columns: Transaksi, Dompet, Tanggal, Nominal, Kategori badge) + top-right "+ Catat Cepat" #1B4332 button.
  - Col 7-9: "Saving Plans (Goals)" progress bars ("Laptop Baru 35%", "Dana Darurat 60%") + "+ Tambah".
  - Col 10-12: "Piutang & Aktivitas (Kasus Jamal)" showing Jamal's remaining balance, wallet chips, and quick "+ Cicilan" link.
```

---

## Bagian 3: Multi-Dompet, Modal Tambah Dompet & Konversi Valas (`WALL` & `TXN-003`)

### Prompt 3A — Halaman Dompet (`Wallets` Mobile `390x844`)
```text
Apply the Tracking Finance Design System. Design the Mobile Wallets Screen (390x844):
- Top Header: Title "Dompet & Valas", right secondary pill button "💱 Transfer / Kurs" (#EFF6EC border #DCE8D9) and primary "+" button (#1B4332 text white).
- Top Summary Bento Card (#FFFFFF rounded-3xl): "Total Likuiditas Terkonversi (IDR)" -> "Rp 32.512.000", currency breakdown chips below: "IDR: Rp 19.7M" | "USD: $845" | "USDT: 420.5".
- Currency Filter Tabs: Horizontal pills "Semua" (active #1B4332), "IDR", "USD", "USDT".
- Section "Dompet Utama & Bank":
  - Card 1 (Default Wallet): Visa-style #1B4332 Forest Green gradient card, top row bank icon + "BCA" + lime pill "★ Default", bold white "Rp 18.450.000", bottom row masked account "•••• •••• 9967" + copy icon + small action icon buttons ("Edit", "Transfer").
  - Card 2: Soft #EFF6EC Mint card, "Tunai / Cash", type badge "Cash", bold "Rp 1.250.000", subtle button "Jadikan Default".
- Section "Dompet Valas & Investasi":
  - Card 3: Soft mint/slate card, "Binance Spot", badge "investment_cash • USDT", bold "420.50 USDT" + equiv "≈ Rp 6.664.925".
  - Card 4: Soft mint/slate card, "Gotrade US", badge "investment_cash • USD", bold "$845.20" + equiv "≈ Rp 13.396.420".
- Floating Bottom Navigation Dock with "Dompet" active.
```

### Prompt 3B — Modal Tambah/Edit Dompet & Modal Transfer Konversi Valas
```text
Apply the Tracking Finance Design System. Design two mobile Bottom Sheet Modals (rounded-t-3xl white overlay sheets with drag handle bar at top):
Modal 1 — "Tambah Dompet Baru":
- Header: "Tambah Dompet" + close "X" circular button.
- Field "Nama Dompet": input "Contoh: BCA, Cash, Binance USDT".
- Field "Tipe Dompet": 2x2 selectable pill cards with icons: "Bank" (active #1B4332), "Tunai / Cash", "E-Wallet", "Kas Investasi".
- Row 2 cols: Dropdown "Mata Uang" ("IDR", "USD", "USDT") + Input "Saldo Awal" (tabular-nums).
- Input "Nomor Rekening / Kartu (Opsional)".
- Color Preset Selector: 5 circular color swatches matching Forest Green, Mint, Emerald, Teal, Slate.
- Toggle Row inside tinted box: Label "Jadikan Dompet Utama (Default)" + subtext "Otomatis terpilih saat catat cepat <= 3 detik" + toggle switch ON in #1B4332.
- Full-width primary button: "Simpan Dompet".

Modal 2 — "Transfer & Konversi Kurs (Currency Exchange)":
- Header: "Pindah Saldo & Valas" + close button.
- Mode Switcher Tabs: "Transfer Sesama Kurs" vs "Konversi Mata Uang (Valas)" (Active in #BDF08E pill).
- Visual Flow Card:
  - Dari Dompet (Source): Dropdown card showing "BCA (IDR) — Saldo Rp 18.450.000", large input "Nominal Keluar: Rp 500.000".
  - Centered circular swap/arrow-down badge in #1B4332.
  - Ke Dompet (Destination): Dropdown card showing "Binance Spot (USDT) — Saldo 420.50", large input "Nominal Masuk: 31.64 USDT".
- Highlighted Exchange Rate Info Banner (#EFF6EC rounded-xl p-3): "Kurs Transaksi Tercatat: 1 USDT = Rp 15.802,78".
- Optional input: "Biaya Admin / Fee (Opsional)" e.g. "Rp 2.500", plus Date input ("Hari Ini").
- Full-width primary button: "Konfirmasi Transfer & Catat Kurs".
```

---

## Bagian 4: Input Transaksi Cepat ($\le 3$ Detik), Riwayat, Kategori & Budgeting (`TXN`)

### Prompt 4A — Modal Quick Add Transaksi ($\le 3$ Detik)
```text
Apply the Tracking Finance Design System. Design the signature "Input Transaksi Cepat <= 3 Detik" Mobile Bottom Sheet Modal (rounded-t-3xl):
- Top drag pill + header "Catat Cepat" on left and quick button "📸 Scan Struk AI" (#EFF6EC border #DCE8D9 rounded-full pill) on right.
- 3-way Segmented Type Control: "Pengeluaran" (active #BE123C rose tint or #1B4332 dark), "Pemasukan", "Transfer".
- Hero Amount Input Box: Soft #F5F8F4 rounded-2xl card with currency prefix "Rp" and huge centered 32px bold tabular-nums input "45.000".
- Smart Default Pills Row (single-tap pre-filled for zero friction!):
  - Pill 1: Wallet icon + "BCA ★ Utama v" (dropdown pill)
  - Pill 2: Calendar icon + "Hari Ini (10 Nov) v"
- Kategori Quick-Tap Grid (4 columns x 2 rows of crisp rounded-2xl icon chips):
  - "Makanan" (ACTIVE: #1B4332 border + #BDF08E icon well), "Transport", "Nongkrong", "Belanja", "Tagihan", "Kesehatan", "Sedekah", "+ Lainnya".
- Compact input "Catatan opsional (misal: Kopi Susu)" + subtle upload icon for receipt attachment.
- Full-width 52px tall CTA button: "Simpan Transaksi" in #1B4332 Forest Green with white bold text.
```

### Prompt 4B — Halaman Riwayat Transaksi & Budgeting Bulanan + Kelola Kategori
```text
Apply the Tracking Finance Design System. Design two mobile screens (390x844):
Screen 1 — "Riwayat Transaksi":
- Header "Riwayat Transaksi" + right button "📥 Export CSV" (outline pill) + Filter icon button.
- Full-width rounded-xl Search Input "Cari catatan atau merchant...".
- Horizontal Filter Chips: "Nov 2026 v", "Semua Dompet v", "Semua Kategori v", "Pengeluaran".
- Period Summary Strip (2 cols): "Masuk: +Rp 12.500.000" (green) | "Keluar: -Rp 4.850.000" (rose).
- Grouped Date Sections ("Hari Ini", "Kemarin"): Clean white rounded-2xl cards with transaction rows. Each row shows left 40px soft icon container, merchant/note title, category + wallet chip ("Makanan • BCA"), and right tabular-nums signed amount ("-Rp 45.000" in coral red / "+Rp 200.000" in #1B4332 green).

Screen 2 — "Budgeting & Kategori" (with top tab switcher "Budget Bulanan" | "Kelola Kategori"):
- Month selector header "< November 2026 >" + right primary pill "+ Atur Budget".
- Overall Monthly Finance Health Card (#EFF6EC rounded-2xl): "Total Terpakai Rp 3.250.000 dari Rp 5.000.000" + 2-tone loading bar ("65% Aman").
- Category Budget Progress Cards (Strictly enforcing PRD 3 threshold colors):
  - Card 1 (Safe <70%): "Makanan & Minuman", "Rp 850.000 / Rp 1.500.000", green badge "56% Aman", Forest Green progress bar.
  - Card 2 (Warning 70-99%): "Nongkrong & Kopi", "Rp 820.000 / Rp 1.000.000", amber badge "82% Hampir Habis", Amber #F59E0B progress bar.
  - Card 3 (Overbudget >=100%): "Belanja & Hobi", "Rp 1.250.000 / Rp 1.000.000", rose pill "125% Overbudget!", Coral Rose #E11D48 full progress bar.
- Include inset preview of "Modal Kelola Kategori" with Name input, Income/Expense toggle, Color swatch picker, and Icon grid picker.
```

---

## Bagian 5: Utang & Piutang Cicilan Bertahap — "Kasus Jamal" (`DEBT`)

### Prompt 5A — Halaman Daftar Utang/Piutang & Kartu Kasus Jamal (`390x844`)
```text
Apply the Tracking Finance Design System. Design the Mobile Debts & Receivables Screen (390x844) tailored for multi-wallet partial installments ("Kasus Jamal"):
- Header: "Utang & Piutang" + right "+ Tambah Catatan" pill button (#1B4332).
- 2-Column Top Summary Bento Cards:
  - Left (#EFF6EC): "Total Piutang (Hak Kita)" -> bold #1B4332 "Rp 650.000", badge "2 Orang".
  - Right (#FFF5F5 / #FFFFFF): "Total Utang (Kewajiban)" -> bold #BE123C "Rp 0", badge "Bebas Utang".
- Friend Quick Avatar Strip: Circular avatars with initial/photo ("Jamal" highlighted with green ring, "Budi", "+ Baru").
- Status Filter Tabs: "Semua" (active), "Piutang", "Utang", "Lunas / Diikhlaskan".
- Featured Bento Card — "Jamal" (Detailed Case):
  - Top row: Avatar circle "JM", Name "Jamal" + WhatsApp icon link, Right Pill Badge "Piutang • Dicicil" (soft amber/mint).
  - Middle numbers (3-col mini metrics): "Total: Rp 400.000" | "Dibayar: Rp 300.000" | "Sisa: Rp 100.000" (bold).
  - Progress bar 75% filled in #1B4332 Forest Green, subtext "Pinjam: 6 Nov • Jatuh Tempo: 20 Nov".
  - Inline Preview Timeline (Last 2 installments):
    - Row 1: "Jumat • +Rp 200.000" + pill badge "Dompet Cash"
    - Row 2: "Sabtu • +Rp 100.000" + pill badge "Bank BCA"
  - Card Action Footer (3 clear buttons):
    - Primary button: "+ Catat Cicilan" (#1B4332 text white rounded-xl)
    - Secondary button: "Riwayat" (#EFF6EC rounded-xl)
    - Text/Outline Neutral button: "🤝 Ikhlaskan Sisa" (subtle border #D8E2DA)
```

### Prompt 5B — Modal Catat Cicilan & Modal "Ikhlaskan Sisa Utang" (`DEBT-003` & `DEBT-005`)
```text
Apply the Tracking Finance Design System. Design two clear mobile Bottom Sheet Modals for Debt Management:
Modal 1 — "Catat Cicilan Masuk (Jamal)":
- Header: "Catat Cicilan Jamal" + info banner "Sisa tagihan saat ini: Rp 100.000 dari Rp 400.000".
- Input "Nominal Cicilan": Large tabular-nums input "100.000" with quick-fill pills underneath: "Rp 50.000", "Lunasi Penuh (Rp 100.000)".
- Critical Field — "Masuk ke Dompet Mana?": Horizontal selectable wallet chips with balances: "Cash (Tunai)" | "BCA ★ (ACTIVE #1B4332)" | "GoPay".
- Date picker "Tanggal Pembayaran" (default Hari Ini) + optional Note input ("Transfer BCA Senin").
- Full-width primary button: "Simpan Cicilan & Tambah Saldo Dompet".

Modal 2 — "Ikhlaskan Sisa Utang (Write-Off)":
- Neutral calm icon circle with handshake/heart outline icon.
- Title: "Ikhlaskan Sisa Piutang Jamal?"
- Summary comparison box (#F5F8F4 rounded-2xl):
  - "Sisa Belum Dibayar": "Rp 100.000"
  - "Status Baru": Pill "Diikhlaskan (Impas)"
- Clear educational helper alert: "Sisa Rp 100.000 akan dicatat sebagai written-off tanpa mengurangi atau menambah saldo dompet riil kamu."
- Checkbox option (checked): "Catat ke histori sebagai Beban / Sedekah".
- Optional note input + Two footer buttons: "Batal" (secondary) & "Ya, Ikhlaskan Sisa" (primary #1B4332).
```

---

## Bagian 6: Portofolio Investasi Multi-Aset & DCA (`INV`)

### Prompt 6A — Halaman Portfolio Investasi (`390x844`)
```text
Apply the Tracking Finance Design System. Design the Mobile Multi-Asset Investment Portfolio Screen (390x844):
- Header: "Portofolio Investasi" + Right Button "🔄 Refresh Harga" (compact #EFF6EC rounded-full pill with tiny caption "Update 16:05") + "+" FAB/Button.
- Hero Portfolio Bento Card (#1B4332 Forest Green rounded-3xl):
  - Label "Total Nilai Pasar (IDR)" -> Large crisp white "Rp 115.720.000".
  - Floating / Unrealized PnL Highlight Pill: "+Rp 4.120.000 (+3.69%)" in bright #BDF08E Lime Mint with dark #14241D text.
  - Sub-row: "Total Modal: Rp 111.600.000" | "Kurs USD: Rp 15.850".
- Asset Type Tabs: "Semua", "Saham IDX (Lot)", "Saham US", "Crypto".
- Asset Holding Bento Cards (#FFFFFF rounded-2xl border #E3ECE1):
  - Card 1 (IDX Stock): Badge "BBCA.JK" + pill "IDX • IDR", Name "Bank Central Asia", Qty "10 Lot (1.000 lbr)". Metrics grid: "Avg (DCA): Rp 9.400" | "Harga Kini: Rp 10.150" | Right PnL Pill "+Rp 750.000 (+7.98%)" in green. Action links: "+ Beli/Jual" & "✏️ Edit Harga".
  - Card 2 (US Stock): Badge "AAPL" + pill "US • USD", Qty "2.5 Lembar", "Avg: $212.00" | "Kini: $228.50", PnL "+$41.25 (+7.78%)".
  - Card 3 (Crypto with Manual Fallback Indicator): Badge "BTC" + pill "Crypto • USDT", Qty "0.0085 BTC", "Avg: 84.200 USDT" | "Kini: 91.500 USDT" + small neutral gray/mint badge "Harga Manual", PnL "+62.05 USDT (+8.67%)".
```

### Prompt 6B — Modal Beli/Jual Aset (`Fee Toggle` + `Lot IDX`) & Modal Edit Harga Manual
```text
Apply the Tracking Finance Design System. Design two Mobile Bottom Sheet Modals for Investment Trading:
Modal 1 — "Catat Transaksi Aset":
- Top segmented toggle: "Beli (Buy)" (active #1B4332), "Jual (Sell)", "Dividen".
- Asset Type pills: "Saham IDX (Lot)" (active), "Saham US", "Crypto".
- Asset Symbol input/select: "BBCA.JK — Bank Central Asia".
- Dynamic Qty Row:
  - Input "Jumlah Lot": "5" -> Live inline helper pill beside it: "= 500 lembar otomatis (1 Lot = 100 lbr)".
  - Input "Harga Beli / Lembar": "Rp 9.800".
- Wallet Selector: "Sumber Dana Dompet" -> "RDN BCA (IDR) — Saldo Rp 12.400.000".
- Broker Fee Toggle Card (#EFF6EC rounded-xl p-3):
  - Switch ON: "Otomatis Hitung Fee Broker (0.15% Beli / 0.25% Jual)"
  - Auto-filled readonly/editable fee: "Rp 7.350".
- Live DCA Calculation Summary Box:
  - "Total Biaya Potong Saldo": "Rp 4.907.350"
  - "Estimasi Harga Rata-Rata Baru (Avg DCA)": "Rp 9.538 / lbr".
- Full-width primary button: "Simpan Transaksi Investasi".

Modal 2 — "Edit Harga Manual (API Fallback)":
- Header "Update Harga Manual — BTC".
- Neutral info banner: "Gunakan fitur ini jika API CoinGecko/Yahoo sedang gangguan. Status aset akan ditandai 'Harga Manual'."
- Input "Harga Pasar Saat Ini (USDT)": "91.500".
- Two buttons: Secondary outline "Reset ke API Otomatis" & Primary #1B4332 "Simpan Harga Manual".
```

---

## Bagian 7: Financial Goals / Saving Plans (`GOAL`)

### Prompt 7 — Halaman Goals & Modal Sisihkan Dana (`390x844`)
```text
Apply the Tracking Finance Design System. Design the Mobile Financial Goals Screen (390x844) styled after COINEST Saving Plans:
- Header: "Financial Goals" + right primary pill "+ Buat Goal".
- Summary Bento Card (#EFF6EC rounded-3xl): "Total Terkumpul di Brankas Goals" -> "Rp 18.300.000" across "2 Target Aktif".
- Goal Bento Card 1 (Active):
  - Top row: 44px soft mint rounded-xl laptop icon box, Title "Laptop Baru (MacBook)", Target Date chip "Target: Jul 2027", Status Pill "In Progress".
  - 2-Tone Progress Bar (tall rounded-full, 35% filled #1B4332, track #D9EAD2) with right bold "35%".
  - Progress values: "Rp 6.300.000" of "Rp 18.000.000" (sisa kurang Rp 11.700.000).
  - Smart Estimation Pill (#E3F8CE text #16422E): "✨ Est. tercapai dalam 8 bulan lagi (rata-rata Rp 1.5M/bln)".
  - Full-width soft button or action row: "+ Sisihkan Dana dari Dompet" (#1B4332 text white rounded-xl).
- Goal Bento Card 2 (Achieved): "Dana Darurat", "Rp 12.000.000 / Rp 12.000.000", "100%", Forest green badge "🎉 Tercapai!".
- Inset/Overlay Modal "Sisihkan Dana ke Laptop Baru": Dropdown Dompet Asal ("BCA ★"), Nominal Setoran ("Rp 1.500.000"), Tanggal ("Hari Ini"), Note, CTA "Alokasikan ke Goal".
```

---

## Bagian 8: AI Receipt Scanner via Gemini Vision (`AI`)

### Prompt 8 — Flow Upload Foto Struk & Verifikasi Hasil Gemini AI (`390x844`)
```text
Apply the Tracking Finance Design System. Design two sequential Mobile Screens (390x844) for AI Receipt Scanner:
Screen A — Upload & Scanning State:
- Header: Back arrow + "Scan Struk AI" + pill badge "Powered by Gemini".
- Interactive Dashed Dropzone Card (#FFFFFF rounded-3xl border-2 dashed #9CC49A):
  - Illustration/icon of receipt + camera, text "Foto Struk Belanja atau Struk Transfer".
  - Two action buttons: Primary #1B4332 "📷 Buka Kamera HP" and Secondary #EFF6EC "🖼️ Pilih dari Galeri".
- Below: Thumbnail preview of uploaded Indomaret receipt with animated mint scanning beam & status pill "Sedang membaca merchant, item, dan total dengan Gemini Vision AI...".

Screen B — Verifikasi & Auto-Fill State (Before Save):
- Collapsible compact receipt photo preview at top ("Lihat Foto Struk Asli").
- AI Confidence Banner (#E3F8CE rounded-xl): "✨ Data Berhasil Diekstrak AI — Periksa sebelum disimpan".
- Editable Bento Form Card:
  - Field "Merchant / Judul": "Indomaret Point"
  - Field "Total Nominal": bold "Rp 78.500"
  - Field "Kategori Saran AI": Chip "Belanja Bulanan" + mini tag "AI Suggested"
  - Field "Tanggal Transaksi": "10 Nov 2026"
  - Field "Ambil dari Dompet": "BCA (Utama ★)"
  - Expandable Line Items Preview Box (#F5F8F4 rounded-xl):
    - "Roti Gandum — Rp 22.000"
    - "Susu UHT 1L — Rp 28.500"
    - "Kopi Botol — Rp 28.000"
- Sticky bottom bar: Secondary "🔄 Scan Ulang" + Primary #1B4332 "✅ Konfirmasi & Simpan Transaksi".
```

---

## Bagian 9: Profil, Mata Uang Utama & Dual Mode Toggle (`AUTH-003`)

### Prompt 9 — Halaman Pengaturan & Preferensi (`390x844`)
```text
Apply the Tracking Finance Design System. Design the Mobile Settings & Profile Screen (390x844):
- Header: "Profil & Pengaturan".
- Profile Card (#FFFFFF rounded-3xl): Avatar circle, Name "Agra", Email "agra@example.com", button "Edit Profil".
- Settings Group 1 — "Tampilan & Mata Uang":
  - Row 1 (Dual Mode Switch): Icon moon/sun + "Mode Tampilan (Light / Dark)" + Interactive Toggle Switch between COINEST Light & Obsidian Dark.
  - Row 2 (`base_currency`): Icon globe + "Mata Uang Utama Net Worth" + Segmented Pill "IDR" (active #1B4332) / "USD".
  - Row 3 ("Kurs Acuan System"): "USD/IDR: Rp 15.850" & "USDT/IDR: Rp 15.850" + inline "Update Kurs" link button.
- Settings Group 2 — "Keamanan & Data":
  - Row "Ubah Password" (chevron right)
  - Row "Kelola Kategori Transaksi" (chevron right)
  - Row "Export Seluruh Data (CSV)" (chevron right)
- Full-width subtle danger outline button at bottom: "Keluar (Logout)" in soft coral rose text.
```
