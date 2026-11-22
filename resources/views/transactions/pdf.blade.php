<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keuangan - {{ config('app.name', 'Tracking Finance') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: #ffffff !important;
                color: #111827 !important;
                font-size: 10pt;
                line-height: 1.4;
            }
            .page-container {
                max-width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
                box-shadow: none !important;
                border: none !important;
            }
            table {
                page-break-inside: auto;
            }
            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
            thead {
                display: table-header-group;
            }
            tfoot {
                display: table-footer-group;
            }
            @page {
                size: A4 portrait;
                margin: 15mm 15mm 15mm 15mm;
            }
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-800 font-sans antialiased min-h-screen py-6 px-4 sm:px-6">

    <!-- Top Action Bar (Hidden on Print) -->
    <div class="no-print max-w-4xl mx-auto mb-6 flex flex-wrap items-center justify-between gap-3 bg-white p-4 rounded-2xl shadow-sm border border-gray-200">
        <div class="flex items-center gap-2">
            <a
                href="{{ route('transactions.index') }}"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 transition-colors"
            >
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                Kembali
            </a>
            <span class="text-xs font-medium text-gray-500 hidden sm:inline">Pratinjau Cetak / PDF Dokumen Resmi</span>
        </div>
        <div class="flex items-center gap-2">
            <button
                type="button"
                onclick="window.print()"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm active:scale-95 transition-all cursor-pointer"
            >
                <span class="material-symbols-outlined text-[18px]">print</span>
                Cetak / Simpan PDF
            </button>
        </div>
    </div>

    <!-- Printable Document Sheet -->
    <main class="page-container max-w-4xl mx-auto bg-white p-8 md:p-12 rounded-3xl shadow-md border border-gray-200">
        <!-- Document Header -->
        <header class="border-b border-gray-200 pb-6 mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-emerald-600 text-3xl">account_balance_wallet</span>
                        <h1 class="text-2xl font-bold tracking-tight text-gray-900">Tracking Finance</h1>
                    </div>
                    <p class="text-xs font-semibold text-emerald-600 tracking-wider uppercase mt-1">Laporan Arus Kas & Transaksi Keuangan</p>
                </div>
                <div class="text-left sm:text-right">
                    <span class="inline-block px-3 py-1 bg-emerald-50 text-emerald-700 rounded-full text-xs font-bold border border-emerald-200">
                        Dokumen Resmi
                    </span>
                    <p class="text-xs text-gray-500 mt-1">Dicetak pada: {{ now()->translatedFormat('d F Y, H:i') }} WIB</p>
                </div>
            </div>

            <!-- Metadata Details -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-6 pt-4 border-t border-gray-100 text-xs">
                <div>
                    <span class="text-gray-500 block">Pemilik Rekening</span>
                    <span class="font-bold text-gray-900 text-sm">{{ $user->name }}</span>
                    <span class="text-gray-500 block truncate">{{ $user->email }}</span>
                </div>
                <div>
                    <span class="text-gray-500 block">Periode Laporan</span>
                    <span class="font-semibold text-gray-900 text-sm">
                        @if($dateFrom && $dateTo)
                            {{ \Carbon\Carbon::parse($dateFrom)->translatedFormat('d M Y') }} - {{ \Carbon\Carbon::parse($dateTo)->translatedFormat('d M Y') }}
                        @elseif($dateFrom)
                            Mulai {{ \Carbon\Carbon::parse($dateFrom)->translatedFormat('d M Y') }}
                        @elseif($dateTo)
                            Sampai {{ \Carbon\Carbon::parse($dateTo)->translatedFormat('d M Y') }}
                        @else
                            Semua Waktu
                        @endif
                    </span>
                </div>
                <div>
                    <span class="text-gray-500 block">Mata Uang Utama</span>
                    <span class="font-bold text-gray-900 text-sm">{{ $user->base_currency ?? 'IDR' }}</span>
                </div>
                <div>
                    <span class="text-gray-500 block">Total Transaksi</span>
                    <span class="font-bold text-gray-900 text-sm">{{ number_format($transactions->count()) }} Catatan</span>
                </div>
            </div>
        </header>

        <!-- Financial Summary Cards -->
        <section class="mb-8">
            <h2 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Ringkasan Eksekutif Arus Kas</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="p-4 rounded-2xl bg-emerald-50/70 border border-emerald-200/60">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-emerald-800">Total Pemasukan</span>
                        <span class="material-symbols-outlined text-emerald-600 text-lg">arrow_downward</span>
                    </div>
                    <div class="text-lg sm:text-xl font-black text-emerald-700 mt-2">
                        +Rp {{ number_format($totalIncome, 0, ',', '.') }}
                    </div>
                    <span class="text-[11px] text-emerald-600 mt-0.5 block">
                        {{ $transactions->where('type', 'income')->count() }} transaksi pemasukan
                    </span>
                </div>

                <div class="p-4 rounded-2xl bg-rose-50/70 border border-rose-200/60">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-rose-800">Total Pengeluaran</span>
                        <span class="material-symbols-outlined text-rose-600 text-lg">arrow_upward</span>
                    </div>
                    <div class="text-lg sm:text-xl font-black text-rose-700 mt-2">
                        -Rp {{ number_format($totalExpense, 0, ',', '.') }}
                    </div>
                    <span class="text-[11px] text-rose-600 mt-0.5 block">
                        {{ $transactions->where('type', 'expense')->count() }} transaksi pengeluaran
                    </span>
                </div>

                <div class="p-4 rounded-2xl {{ $netCashflow >= 0 ? 'bg-blue-50/70 border-blue-200/60' : 'bg-amber-50/70 border-amber-200/60' }} border">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold {{ $netCashflow >= 0 ? 'text-blue-800' : 'text-amber-800' }}">Arus Kas Bersih (Net)</span>
                        <span class="material-symbols-outlined {{ $netCashflow >= 0 ? 'text-blue-600' : 'text-amber-600' }} text-lg">account_balance</span>
                    </div>
                    <div class="text-lg sm:text-xl font-black {{ $netCashflow >= 0 ? 'text-blue-700' : 'text-amber-700' }} mt-2">
                        {{ $netCashflow >= 0 ? '+' : '-' }}Rp {{ number_format(abs($netCashflow), 0, ',', '.') }}
                    </div>
                    <span class="text-[11px] {{ $netCashflow >= 0 ? 'text-blue-600' : 'text-amber-600' }} mt-0.5 block">
                        {{ $netCashflow >= 0 ? 'Surplus Keuangan' : 'Defisit (Pengeluaran > Pemasukan)' }}
                    </span>
                </div>
            </div>
        </section>

        <!-- Category Breakdown (If Expenses Exist) -->
        @if($categoryBreakdown->isNotEmpty())
            <section class="mb-8">
                <h2 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Rincian Pengeluaran Berdasarkan Kategori</h2>
                <div class="border border-gray-200 rounded-2xl overflow-hidden">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200 text-gray-600">
                                <th class="py-2.5 px-4 font-semibold">Kategori</th>
                                <th class="py-2.5 px-4 font-semibold text-center">Jumlah Transaksi</th>
                                <th class="py-2.5 px-4 font-semibold text-right">Total Biaya</th>
                                <th class="py-2.5 px-4 font-semibold text-right">Porsi Pengeluaran</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-800">
                            @foreach($categoryBreakdown as $cat)
                                @php
                                    $pct = $totalExpense > 0 ? ($cat['amount'] / $totalExpense) * 100 : 0;
                                @endphp
                                <tr class="hover:bg-gray-50/50">
                                    <td class="py-2 px-4 font-medium flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full inline-block" style="background-color: {{ $cat['color'] }};"></span>
                                        {{ $cat['name'] }}
                                    </td>
                                    <td class="py-2 px-4 text-center">{{ $cat['count'] }}x</td>
                                    <td class="py-2 px-4 text-right font-semibold">Rp {{ number_format($cat['amount'], 0, ',', '.') }}</td>
                                    <td class="py-2 px-4 text-right font-bold text-gray-600">{{ number_format($pct, 1) }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        <!-- Transaction Table -->
        <section class="mb-8">
            <h2 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Daftar Transaksi Lengkap</h2>
            @if($transactions->isEmpty())
                <div class="text-center py-10 border border-dashed border-gray-300 rounded-2xl text-gray-500 text-xs">
                    Tidak ada transaksi pada filter dan periode ini.
                </div>
            @else
                <div class="border border-gray-200 rounded-2xl overflow-hidden">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200 text-gray-600">
                                <th class="py-2.5 px-3 font-semibold">Tanggal</th>
                                <th class="py-2.5 px-3 font-semibold">Deskripsi & Catatan</th>
                                <th class="py-2.5 px-3 font-semibold">Tipe</th>
                                <th class="py-2.5 px-3 font-semibold">Kategori</th>
                                <th class="py-2.5 px-3 font-semibold">Dompet</th>
                                <th class="py-2.5 px-3 font-semibold text-right">Nominal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-800">
                            @foreach($transactions as $txn)
                                <tr class="hover:bg-gray-50/50">
                                    <td class="py-2 px-3 whitespace-nowrap text-gray-500">
                                        {{ $txn->transaction_date->format('d/m/Y') }}
                                    </td>
                                    <td class="py-2 px-3 font-medium">
                                        {{ $txn->description ?: '-' }}
                                    </td>
                                    <td class="py-2 px-3 whitespace-nowrap">
                                        @if($txn->type === 'income')
                                            <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Pemasukan</span>
                                        @elseif($txn->type === 'expense')
                                            <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">Pengeluaran</span>
                                        @elseif($txn->type === 'transfer')
                                            <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">Transfer</span>
                                        @else
                                            <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800">{{ ucfirst($txn->type) }}</span>
                                        @endif
                                    </td>
                                    <td class="py-2 px-3 text-gray-600">
                                        {{ $txn->category?->name ?? '-' }}
                                    </td>
                                    <td class="py-2 px-3 text-gray-600 whitespace-nowrap">
                                        {{ $txn->wallet?->name ?? '-' }}
                                        @if($txn->destinationWallet)
                                            &rarr; {{ $txn->destinationWallet->name }}
                                        @endif
                                    </td>
                                    <td class="py-2 px-3 text-right font-bold whitespace-nowrap {{ $txn->type === 'income' ? 'text-emerald-700' : ($txn->type === 'expense' ? 'text-rose-700' : 'text-gray-900') }}">
                                        @if($txn->type === 'income')
                                            +{{ $txn->wallet?->currency ?? 'IDR' }} {{ number_format($txn->amount, 0, ',', '.') }}
                                        @elseif($txn->type === 'expense')
                                            -{{ $txn->wallet?->currency ?? 'IDR' }} {{ number_format($txn->amount, 0, ',', '.') }}
                                        @else
                                            {{ $txn->wallet?->currency ?? 'IDR' }} {{ number_format($txn->amount, 0, ',', '.') }}
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <!-- Document Footer -->
        <footer class="pt-6 border-t border-gray-200 flex flex-col sm:flex-row justify-between items-center text-xs text-gray-500 gap-2">
            <div>
                <span>Digenerate otomatis oleh <strong>Tracking Finance</strong> &bull; Sistem Pengelolaan Keuangan Pribadi</span>
            </div>
            <div>
                <span>Halaman 1 dari 1 &bull; Rahasia & Pribadi</span>
            </div>
        </footer>
    </main>

</body>
</html>