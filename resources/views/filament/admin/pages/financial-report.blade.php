<x-filament-panels::page>
    <div class="financial-report-page">
        <x-filament::section
            heading="Filter Buku Kas"
            :description="$period_title"
        >
            <div class="financial-report-form">
                {{ $this->form }}
            </div>
        </x-filament::section>

        <div class="financial-report-stats">
            <x-filament::section heading="Pemasukan">
                <div class="financial-report-stat-body">
                    <div class="financial-report-stat-value is-success">Rp {{ number_format($summary_in, 0, ',', '.') }}</div>
                    <div class="financial-report-stat-meta">{{ $income_count }} transaksi pemasukan</div>
                </div>
            </x-filament::section>

            <x-filament::section heading="Pengeluaran">
                <div class="financial-report-stat-body">
                    <div class="financial-report-stat-value is-danger">Rp {{ number_format($summary_out, 0, ',', '.') }}</div>
                    <div class="financial-report-stat-meta">{{ $expense_count }} transaksi pengeluaran</div>
                </div>
            </x-filament::section>

            <x-filament::section heading="Laba Bersih">
                <div class="financial-report-stat-body">
                    <div class="financial-report-stat-value {{ $net_profit >= 0 ? 'is-success' : 'is-danger' }}">
                        {{ $net_profit >= 0 ? '+' : '-' }}Rp {{ number_format(abs($net_profit), 0, ',', '.') }}
                    </div>
                    <div class="financial-report-stat-meta">Pemasukan dikurangi pengeluaran</div>
                </div>
            </x-filament::section>

            <x-filament::section heading="Jumlah Transaksi">
                <div class="financial-report-stat-body">
                    <div class="financial-report-stat-value">{{ $transaction_count }}</div>
                    <div class="financial-report-stat-meta">Total transaksi pada filter yang sedang aktif</div>
                </div>
            </x-filament::section>
        </div>

        <div class="financial-report-content">
            <x-filament::section
                heading="Daftar Transaksi"
                description="Tambah, ubah, hapus, dan telusuri transaksi dari satu halaman yang sama."
            >
                {{ $this->table }}
            </x-filament::section>
        </div>
    </div>

    <style>
        .financial-report-page,
        .financial-report-form {
            display: grid;
            gap: 1.5rem;
            min-width: 0;
        }

        .financial-report-stats {
            display: grid;
            gap: 1rem;
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .financial-report-content {
            display: grid;
            gap: 1.5rem;
            align-items: start;
            overflow-x: auto;
            min-width: 0;
        }

        .financial-report-stat-body {
            display: grid;
            gap: 0.4rem;
        }

        .financial-report-stat-value {
            font-size: 1.75rem;
            font-weight: 700;
            line-height: 1.1;
        }

        .financial-report-stat-meta,
        .financial-report-breakdown-meta,
        .financial-report-empty {
            font-size: 0.9rem;
            color: rgb(107 114 128);
        }

        .dark .financial-report-stat-meta,
        .dark .financial-report-breakdown-meta,
        .dark .financial-report-empty {
            color: rgb(156 163 175);
        }

        .is-success {
            color: rgb(22 163 74);
        }

        .is-danger {
            color: rgb(220 38 38);
        }

        @media (max-width: 1200px) {
            .financial-report-stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 1024px) {
            .financial-report-stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .financial-report-content {
                grid-template-columns: 1fr;
            }

            .financial-report-stat-value {
                font-size: 1.4rem;
            }
        }

        @media (max-width: 640px) {
            .financial-report-stats {
                grid-template-columns: 1fr;
                gap: 0.75rem;
            }

            .financial-report-stat-value {
                font-size: 1.4rem;
                word-break: normal;
            }

            .financial-report-stat-meta {
                font-size: 0.85rem;
            }

            .financial-report-page {
                gap: 1rem;
            }
        }
    </style>
</x-filament-panels::page>
