<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan Keuangan - SPPG Joresan</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 16mm 14mm 18mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: "Times New Roman", Times, serif;
            color: #111;
            background: #fff;
            font-size: 12px;
            line-height: 1.45;
        }

        .report {
            width: 100%;
        }

        .letterhead {
            display: table;
            width: 100%;
            border-bottom: 3px solid #111;
            padding-bottom: 10px;
            margin-bottom: 18px;
        }

        .letterhead-logo,
        .letterhead-body {
            display: table-cell;
            vertical-align: middle;
        }

        .letterhead-logo {
            width: 90px;
        }

        .letterhead-logo img {
            width: 74px;
            height: 74px;
            object-fit: contain;
            display: block;
        }

        .letterhead-body {
            text-align: center;
            padding-right: 70px;
        }

        .agency-line {
            margin: 0;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .agency-name {
            margin: 4px 0 0;
            font-size: 20px;
            font-weight: 700;
            line-height: 1.2;
            text-transform: uppercase;
        }

        .doc-title {
            text-align: center;
            margin: 18px 0 6px;
            font-size: 16px;
            font-weight: 700;
            text-transform: uppercase;
            text-decoration: underline;
            letter-spacing: 0.06em;
        }

        .doc-subtitle {
            text-align: center;
            margin: 0 0 18px;
            font-size: 12px;
        }

        .meta {
            margin-bottom: 14px;
        }

        .meta table {
            width: 100%;
            border-collapse: collapse;
        }

        .meta td {
            padding: 2px 0;
            vertical-align: top;
        }

        .meta td:first-child {
            width: 180px;
        }

        .summary {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }

        .summary td {
            border: 1px solid #222;
            padding: 8px 10px;
            vertical-align: top;
        }

        .summary .label {
            font-weight: 700;
            text-transform: uppercase;
            font-size: 11px;
        }

        .summary .value {
            display: block;
            margin-top: 4px;
            font-size: 15px;
            font-weight: 700;
        }

        .summary .meta-text {
            display: block;
            margin-top: 4px;
            font-size: 11px;
        }

        .section {
            margin-top: 16px;
            break-inside: avoid;
        }

        .section-title {
            margin: 0 0 8px;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
        }

        table.report-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .report-table th,
        .report-table td {
            border: 1px solid #222;
            padding: 6px 7px;
            text-align: left;
            vertical-align: top;
            word-break: break-word;
        }

        .report-table th {
            text-align: center;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .is-right {
            text-align: right !important;
        }

        .empty {
            text-align: center !important;
            font-style: italic;
        }

        .signature {
            width: 100%;
            margin-top: 26px;
        }

        .signature td {
            width: 50%;
            vertical-align: top;
            text-align: center;
        }

        .signature-space {
            height: 72px;
        }

        .signature-name {
            font-weight: 700;
            text-decoration: underline;
        }

        .footnote {
            margin-top: 12px;
            font-size: 10px;
            color: #333;
        }
    </style>
</head>
<body onload="window.print()">
    <main class="report">
        <section class="letterhead">
            <div class="letterhead-logo">
                <img src="{{ asset('logobgn.png') }}" alt="Logo SPPG Joresan">
            </div>
            <div class="letterhead-body">
                <p class="agency-line">Yayasan Bani Mustofa</p>
                <p class="agency-name">Satuan Pelayanan Pemenuhan Gizi Joresan Mlarak</p>
            </div>
        </section>

        <h1 class="doc-title">Laporan Keuangan</h1>
        <p class="doc-subtitle">{{ $period_title }}</p>

        <section class="meta">
            <table>
                <tr>
                    <td>Unit Pelaksana</td>
                    <td>: Satuan Pelayanan Pemenuhan Gizi Joresan Mlarak</td>
                </tr>
                <tr>
                    <td>Naungan</td>
                    <td>: Yayasan Bani Mustofa</td>
                </tr>
                <tr>
                    <td>Tanggal Cetak</td>
                    <td>: {{ now()->translatedFormat('d F Y H:i') }}</td>
                </tr>
            </table>
        </section>

        <table class="summary">
            <tr>
                <td>
                    <span class="label">Total Pemasukan</span>
                    <span class="value">Rp {{ number_format($summary_in, 0, ',', '.') }}</span>
                    <span class="meta-text">{{ $income_count }} transaksi</span>
                </td>
                <td>
                    <span class="label">Total Pengeluaran</span>
                    <span class="value">Rp {{ number_format($summary_out, 0, ',', '.') }}</span>
                    <span class="meta-text">{{ $expense_count }} transaksi</span>
                </td>
                <td>
                    <span class="label">Laba Bersih</span>
                    <span class="value">{{ $net_profit >= 0 ? '+' : '-' }}Rp {{ number_format(abs($net_profit), 0, ',', '.') }}</span>
                    <span class="meta-text">Pemasukan dikurangi pengeluaran</span>
                </td>
            </tr>
        </table>

        <section class="section">
            <h2 class="section-title">I. Rincian per Kategori</h2>
            <table class="report-table">
                <thead>
                    <tr>
                        <th style="width: 6%;">No</th>
                        <th style="width: 34%;">Kategori</th>
                        <th style="width: 18%;">Tipe</th>
                        <th style="width: 18%;">Jumlah Transaksi</th>
                        <th style="width: 24%;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($breakdown as $index => $category)
                        <tr>
                            <td style="text-align: center;">{{ $index + 1 }}</td>
                            <td>{{ $category['name'] }}</td>
                            <td>{{ $category['type'] === 'IN' ? 'Pemasukan' : 'Pengeluaran' }}</td>
                            <td style="text-align: center;">{{ $category['count'] }}</td>
                            <td class="is-right">Rp {{ number_format($category['total'], 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="empty">Belum ada data kategori.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        <section class="section">
            <h2 class="section-title">II. Daftar Transaksi</h2>
            <table class="report-table">
                <thead>
                    <tr>
                        <th style="width: 6%;">No</th>
                        <th style="width: 16%;">Tanggal</th>
                        <th style="width: 21%;">Kategori</th>
                        <th style="width: 15%;">Tipe</th>
                        <th style="width: 24%;">Keterangan</th>
                        <th style="width: 18%;">Nominal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($print_transactions as $index => $transaction)
                        @php $isIncome = $transaction->category?->type === 'IN'; @endphp
                        <tr>
                            <td style="text-align: center;">{{ $index + 1 }}</td>
                            <td>{{ \Carbon\Carbon::parse($transaction->date)->translatedFormat('d M Y') }}</td>
                            <td>{{ $transaction->category?->name ?? '-' }}</td>
                            <td>{{ $isIncome ? 'Pemasukan' : 'Pengeluaran' }}</td>
                            <td>{{ $transaction->note ?: '-' }}</td>
                            <td class="is-right">{{ $isIncome ? '+' : '-' }}Rp {{ number_format($transaction->amount, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty">Belum ada transaksi pada periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        <table class="signature">
            <tr>
                <td></td>
                <td>
                    Joresan, {{ now()->translatedFormat('d F Y') }}<br>
                    Mengetahui,<br>
                    Pengelola SPPG Joresan Mlarak
                    <div class="signature-space"></div>
                    <div class="signature-name">(..........................................)</div>
                </td>
            </tr>
        </table>

        <div class="footnote">
            Dokumen ini dicetak otomatis dari sistem pelaporan keuangan Satuan Pelayanan Pemenuhan Gizi Joresan Mlarak Yayasan Bani Mustofa.
        </div>
    </main>
</body>
</html>
