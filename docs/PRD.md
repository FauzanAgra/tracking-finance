# Product Requirement Document (PRD)
## Tracking Finance: Personal Wealth, Cashflow & Investment Hub

* **Versi Dokumen**: 1.1.0
* **Status**: Disetujui
* **Tech Stack**: Laravel 13, Tailwind CSS v4.3, Alpine.js, PHP 8.4, MySQL 8.0
* **Dokumentasi Terkait**:
  - [ERD & Database Schema](ERD.md)
  - [Technical Specification & Architecture](TECH_SPEC.md)
  - [Tasks & Backlog Board](TASKS.md)

---

## 1. Executive Summary & Visi Produk

**Tracking Finance** adalah platform manajemen keuangan pribadi terintegrasi (*all-in-one*) yang dirancang untuk melacak arus kas harian, utang-piutang sosial (cicilan bertahap), rencana masa depan (*financial goals*), serta portofolio investasi multi-aset (Saham Indonesia/IDX berbasis **Lot**, Saham Luar Negeri/US, dan Cryptocurrency). 

Aplikasi ini mendukung **Multi-Currency** (IDR, USD, USDT) dengan fitur konversi kurs saat top-up/pindah dana (misal: IDR $\rightarrow$ USDT $\rightarrow$ BTC), pencatatan harga rata-rata (*Average Down/DCA*), pemantauan keuntungan/kerugian harian (*floating PnL*), serta asisten AI (Gemini API) untuk membaca dan mengkategorikan struk belanja secara otomatis.

---

## 2. Kebutuhan Pengguna & Alur Bisnis (User Stories & Business Rules)

### Modul 1: Multi-Dompet & Multi-Currency (Cashflow & Currency Exchange)
* **User Story**: Sebagai pengguna, saya ingin memisahkan uang saya ke berbagai dompet dengan mata uang berbeda (IDR untuk BCA/Cash/RDN, USDT untuk Binance/TokoCrypto, USD untuk Gotrade/Pluang) serta mencatat konversi mata uang saat top-up.
* **Aturan Bisnis (Business Rules)**:
  1. Setiap dompet memiliki mata uang (`currency`), misalnya: `IDR`, `USD`, `USDT`.
  2. **Transaksi Normal**:
     - Pemasukan menambah saldo dompet yang bersangkutan.
     - Pengeluaran mengurangi saldo dompet yang bersangkutan.
  3. **Transfer Antar-Dompet / Konversi Mata Uang (Currency Exchange)**:
     - Jika transfer dilakukan antar dompet dengan mata uang sama: Saldo dompet asal berkurang, saldo dompet tujuan bertambah nominal yang sama (dikurangi fee jika ada).
     - Jika transfer dilakukan antar dompet dengan mata uang berbeda (misal dari BCA IDR ke Binance USDT):
       - Pengguna memasukkan nominal keluar (contoh: `Rp 500.000`) dan nominal masuk (contoh: `31.64 USDT`).
       - Sistem mencatat nilai kurs transaksi saat itu: $\text{Exchange Rate} = 500.000 / 31.64 = \text{Rp } 15.802,78/\text{USDT}$.
       - Saldo BCA terpotong Rp 500.000, saldo Binance bertambah 31.64 USDT.

### Modul 2: Budgeting Bulanan (Pengawasan Anggaran)
* **User Story**: Sebagai pengguna, saya ingin membatasi pengeluaran bulanan per kategori (misal: budget Makan max Rp 1.500.000/bulan) agar tidak boros.
* **Aturan Bisnis**:
  1. Budget ditentukan per kategori untuk periode bulan tertentu (Format: `YYYY-MM`).
  2. Sistem menghitung realisasi belanja secara dinamis dari akumulasi transaksi pengeluaran di kategori tersebut pada bulan berjalan.
  3. Indikator visual progres:
     - Hijau: Pengeluaran < 70% dari budget.
     - Kuning: Pengeluaran 70% - 99% dari budget.
     - Merah: Pengeluaran $\ge$ 100% (Overbudget).

### Modul 3: Manajemen Utang & Piutang Bertahap (Kasus "Jamal")
* **User Story**: Sebagai pengguna, saya ingin mencatat pinjaman (utang kita ke orang lain atau piutang orang lain ke kita) yang dapat dicicil pembayarannya berkali-kali ke dompet yang berbeda-beda hingga lunas.
* **Aturan Bisnis**:
  1. **Jenis Record**:
     - `receivable` (Piutang): Orang lain pinjam uang ke kita (aset bertambah).
     - `payable` (Utang): Kita pinjam uang ke orang lain (kewajiban bertambah).
  2. **Pencatatan Cicilan / Pelunasan**:
     - Setiap ada cicilan pembayaran, dicatat di tabel `debt_payments`.
     - Pengguna menentukan: Jumlah bayar, tanggal bayar, dan **ke dompet mana uang tersebut masuk/keluar** (contoh: Jamal bayar Rp 200.000 Cash $\rightarrow$ masuk dompet Cash; Sabtu bayar Rp 100.000 transfer $\rightarrow$ masuk dompet BCA).
  3. **Status Otomatis**:
     - `unpaid`: Belum ada pembayaran sama sekali.
     - `partially_paid`: Sudah ada pembayaran, namun total cicilan < total utang.
     - `paid`: Total cicilan $\ge$ total utang (Lunas).

### Modul 4: Portfolio Investasi Multi-Aset (Saham IDX, Saham US, Crypto & DCA)
* **User Story**: Sebagai investor, saya ingin mencatat pembelian aset menggunakan saldo dari dompet yang relevan, menghitung harga rata-rata (*Average Down*), serta melihat keuntungan/kerugian (*floating profit/loss*) berdasarkan harga pasar terkini dan kurs Dollar.
* **Aturan Bisnis**:
  1. **Tipe Aset & Satuan**:
     - `idx_stock` (Saham Indonesia): Input dalam satuan **Lot**. Sistem otomatis mengalikan dengan 100 untuk mendapatkan jumlah lembar ($1\text{ Lot} = 100\text{ Lembar}$). Mata uang: `IDR`.
     - `us_stock` (Saham AS): Input dalam lembar / lembar fraksional (misal: `0.5 AAPL`). Mata uang: `USD`.
     - `crypto`: Input dalam koin/token (misal: `0.0035 BTC`). Mata uang: `USDT` atau `USD`.
  2. **Sumber Dana Transaksi**:
     - Pembelian aset ditarik dari dompet dengan mata uang yang sesuai (misal: Beli BTC memotong saldo dompet `Binance USDT`, beli AAPL memotong dompet `Gotrade USD`, beli BBCA memotong dompet `RDN IDR`).
  3. **Perhitungan Average Down (Weighted Average Cost)**:
     Setiap transaksi beli baru:
     $$\text{New Average Price} = \frac{(\text{Qty Lama} \times \text{Avg Lama}) + (\text{Qty Baru} \times \text{Harga Beli Baru}) + \text{Fee}}{\text{Qty Lama} + \text{Qty Baru}}$$
  4. **Floating Profit / Loss (Unrealized PnL)**:
     $$\text{Market Value} = \text{Total Qty Tersisa} \times \text{Harga Terkini}$$
     $$\text{Total Modal Investasi} = \text{Total Qty Tersisa} \times \text{Average Price}$$
     $$\text{Unrealized PnL (Nominal)} = \text{Market Value} - \text{Total Modal Investasi}$$
     $$\text{Unrealized PnL (\%)} = \left(\frac{\text{Unrealized PnL}}{\text{Total Modal Investasi}}\right) \times 100\%$$
  5. **Pembaruan Harga Pasar**:
     - Crypto diambil via **CoinGecko API** (Free Tier).
     - Saham US & IDX diambil via **Yahoo Finance API** (Ticker `.JK` untuk IDX).
     - Tombol manual *"🔄 Refresh Harga"* di dashboard, plus cron otomatis sore hari.

### Modul 5: Financial Goals / Wishlist (Target Menabung)
* **User Story**: Sebagai pengguna, saya ingin membuat target tabungan untuk barang yang ingin saya beli (misal: Laptop Baru Rp 18.000.000) dan menyisihkan uang dari dompet saya secara bertahap.
* **Aturan Bisnis**:
  1. Setiap alokasi tabungan ke goal akan memindahkan saldo dari dompet yang dipilih ke "brankas goal".
  2. Menampilkan persentase pencapaian, sisa kekurangan, dan estimasi waktu selesai berdasarkan rata-rata tabungan bulanan.

### Modul 6: AI Receipt Scanner (Gemini API)
* **User Story**: Sebagai pengguna, saya ingin memfoto struk belanja agar data nominal, merchant, tanggal, dan kategorinya langsung terisi tanpa ketik manual.
* **Aturan Bisnis**:
  1. Pengguna mengunggah gambar struk (JPG/PNG).
  2. Backend mengirim gambar dengan Prompt Vision ke model Gemini Flash API.
  3. Respon berupa JSON terstruktur diekstrak ke form transaksi untuk ditinjau oleh pengguna sebelum disimpan ke database.

### Modul 7: Dashboard Ringkasan Kekayaan Bersih (Total Net Worth)
* Menghitung nilai kekayaan bersih secara instan dalam Rupiah (IDR):
  - Sistem memiliki kurs acuan terkini (misal: $1\text{ USD} = 1\text{ USDT} = \text{Rp } 15.850$).
  - Nilai semua dompet & aset dalam USD/USDT otomatis dikonversi ke IDR:
  $$\text{Net Worth (IDR)} = \sum \text{Dompet IDR} + \left(\sum \text{Dompet Non-IDR} \times \text{Kurs}\right) + \text{Nilai Saham IDX} + \left(\text{Nilai Saham US \& Crypto} \times \text{Kurs}\right) + \sum \text{Piutang} - \sum \text{Utang}$$
