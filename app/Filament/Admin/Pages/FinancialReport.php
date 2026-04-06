<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Resources\Transactions\TransactionResource;
use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema as DatabaseSchema;
use Illuminate\Support\Number;

class FinancialReport extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $title = 'Buku Kas';
    protected static ?string $slug = 'buku-kas';

    protected string $view = 'filament.admin.pages.financial-report';

    protected static string|\UnitEnum|null $navigationGroup = 'PENGELOLAAN BISNIS';

    protected static ?int $navigationSort = 3;

    public ?array $data = [];

    public static function getNavigationLabel(): string
    {
        return 'Buku Kas';
    }

    public function mount(): void
    {
        $this->form->fill([
            'period' => '1_month',
            'transaction_type' => 'all',
            'date_from' => null,
            'date_until' => null,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(['default' => 1, 'sm' => 3])
                    ->schema([
                        Select::make('period')
                            ->label('Periode')
                            ->options([
                                '1_week' => '1 Minggu Terakhir',
                                '2_weeks' => '2 Minggu Terakhir',
                                '1_month' => '1 Bulan Terakhir',
                                '2_months' => '2 Bulan Terakhir',
                                '3_months' => '3 Bulan Terakhir',
                                '1_year' => '1 Tahun Terakhir',
                                'custom' => 'Rentang Tanggal Custom',
                                'all_time' => 'Semua Waktu',
                            ])
                            ->required()
                            ->live()
                            ->default('1_month'),

                        Select::make('transaction_type')
                            ->label('Jenis')
                            ->options([
                                'all' => 'Semua Transaksi',
                                'IN' => 'Pemasukan',
                                'OUT' => 'Pengeluaran',
                            ])
                            ->required()
                            ->live()
                            ->default('all'),

                        DatePicker::make('date_from')
                            ->label('Dari Tanggal')
                            ->helperText('Isi jika memilih rentang tanggal custom.')
                            ->visible(fn (Get $get) => $get('period') === 'custom')
                            ->required(fn (Get $get) => $get('period') === 'custom')
                            ->live(),

                        DatePicker::make('date_until')
                            ->label('Sampai Tanggal')
                            ->helperText('Tanggal akhir laporan custom.')
                            ->visible(fn (Get $get) => $get('period') === 'custom')
                            ->required(fn (Get $get) => $get('period') === 'custom')
                            ->live(),
                    ]),
            ])
            ->statePath('data');
    }

    public function updatedData(): void
    {
        $this->resetTable();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('print')
                ->label('Cetak Laporan')
                ->icon('heroicon-m-printer')
                ->color('gray')
                ->outlined()
                ->url(fn (): string => route('buku-kas.print', array_filter([
                    'period' => $this->data['period'] ?? null,
                    'transaction_type' => $this->data['transaction_type'] ?? null,
                    'date_from' => $this->data['date_from'] ?? null,
                    'date_until' => $this->data['date_until'] ?? null,
                ], fn ($value) => filled($value))))
                ->openUrlInNewTab(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getFilteredTransactionsQuery())
            ->defaultSort('date', 'desc')
            ->paginated([10, 25, 50])
            ->columns([
                TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('category.type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'IN' => 'Pemasukan',
                        'OUT' => 'Pengeluaran',
                        default => '-',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'IN' => 'success',
                        'OUT' => 'danger',
                        default => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('note')
                    ->label('Keterangan')
                    ->placeholder('-')
                    ->wrap()
                    ->searchable()
                    ->description(fn (Transaction $record): ?string => 
                        request()->headers->get('User-Agent') && str_contains(strtolower(request()->headers->get('User-Agent')), 'mobile')
                            ? $record->category?->name 
                            : null
                    ),

                ImageColumn::make('receipt')
                    ->label('Nota')
                    ->square()
                    ->size(35)
                    ->toggleable()
                    ->placeholder('-'),

                TextColumn::make('amount')
                    ->label('Nominal')
                    ->formatStateUsing(fn (int $state): string => 'Rp ' . Number::format($state, 0, locale: 'id'))
                    ->alignment('right')
                    ->weight('bold')
                    ->color(fn (Transaction $record): string => $record->category?->type === 'IN' ? 'success' : 'danger'),
            ])
            ->actions($this->canManageTransactions() ? [
                EditAction::make()
                    ->modalWidth('xl')
                    ->schema(TransactionResource::getFormComponents())
                    ->using(function (Transaction $record, array $data): Transaction {
                        unset($data['transaction_type']);
                        $record->update($data);

                        return $record;
                    }),
                DeleteAction::make(),
            ] : [])
            ->emptyStateHeading('Belum ada transaksi')
            ->emptyStateDescription('Tambahkan transaksi pemasukan atau pengeluaran untuk mulai mengisi Buku Kas.');
    }

    protected function canManageTransactions(): bool
    {
        return auth()->user()?->isAdmin() === true;
    }

    protected function getReportData(): array
    {
        return static::buildReportData($this->data, includeTransactions: false);
    }

    public static function buildReportData(array $data = [], bool $includeTransactions = true): array
    {
        if (! DatabaseSchema::hasTable('transactions') || ! DatabaseSchema::hasTable('categories')) {
            $result = [
                'summary_in' => 0,
                'summary_out' => 0,
                'net_profit' => 0,
                'transaction_count' => 0,
                'period_title' => 'Data belum tersedia',
            ];

            if ($includeTransactions) {
                $result['print_transactions'] = collect();
            }

            return $result;
        }

        $period = $data['period'] ?? '1_month';
        $dateFrom = $data['date_from'] ?? null;
        $dateUntil = $data['date_until'] ?? null;

        $query = static::getFilteredTransactionsQueryFor($data);
        $now = Carbon::now();
        $title = 'Semua Waktu';

        switch ($period) {
            case '1_week':
                $from = $now->copy()->subDays(6)->startOfDay();
                $until = $now->copy()->endOfDay();
                $title = '7 Hari Terakhir (' . $from->format('d M') . ' - ' . $until->format('d M Y') . ')';
                break;

            case '2_weeks':
                $from = $now->copy()->subDays(13)->startOfDay();
                $until = $now->copy()->endOfDay();
                $title = '2 Minggu Terakhir (' . $from->format('d M') . ' - ' . $until->format('d M Y') . ')';
                break;

            case '1_month':
                $from = $now->copy()->subMonth()->startOfDay();
                $until = $now->copy()->endOfDay();
                $title = '1 Bulan Terakhir (' . $from->format('d M') . ' - ' . $until->format('d M Y') . ')';
                break;

            case '2_months':
                $from = $now->copy()->subMonths(2)->startOfDay();
                $until = $now->copy()->endOfDay();
                $title = '2 Bulan Terakhir (' . $from->format('d M') . ' - ' . $until->format('d M Y') . ')';
                break;

            case '3_months':
                $from = $now->copy()->subMonths(3)->startOfDay();
                $until = $now->copy()->endOfDay();
                $title = '3 Bulan Terakhir (' . $from->format('d M') . ' - ' . $until->format('d M Y') . ')';
                break;

            case '1_year':
                $from = $now->copy()->subYear()->startOfDay();
                $until = $now->copy()->endOfDay();
                $title = '1 Tahun Terakhir (' . $from->format('d M Y') . ' - ' . $until->format('d M Y') . ')';
                break;

            case 'custom':
                if ($dateFrom && $dateUntil) {
                    $from = Carbon::parse($dateFrom)->startOfDay();
                    $until = Carbon::parse($dateUntil)->endOfDay();
                    $title = 'Custom: ' . $from->format('d M Y') . ' - ' . $until->format('d M Y');
                }
                break;

            case 'all_time':
            default:
                $title = 'Semua Waktu';
                break;
        }

        $transactions = $query->get();

        $in = 0;
        $out = 0;

        foreach ($transactions as $transaction) {
            if ($transaction->category?->type === 'IN') {
                $in += $transaction->amount;
            } elseif ($transaction->category?->type === 'OUT') {
                $out += $transaction->amount;
            }
        }

        $breakdown = $transactions->groupBy('category_id')->map(function ($items) {
            $first = $items->first();
            return [
                'name' => $first->category?->name ?? 'Tanpa Kategori',
                'type' => $first->category?->type ?? 'OUT',
                'count' => $items->count(),
                'total' => $items->sum('amount'),
            ];
        })->values()->sortByDesc('total');

        $result = [
            'summary_in' => $in,
            'summary_out' => $out,
            'net_profit' => $in - $out,
            'transaction_count' => $transactions->count(),
            'period_title' => $title,
            'income_count' => $transactions->where('category.type', 'IN')->count(),
            'expense_count' => $transactions->where('category.type', 'OUT')->count(),
            'breakdown' => $breakdown,
        ];

        if ($includeTransactions) {
            $result['print_transactions'] = $transactions;
        }

        return $result;
    }

    protected function getViewData(): array
    {
        return $this->getReportData();
    }

    protected function getFilteredTransactionsQuery(): Builder
    {
        return static::getFilteredTransactionsQueryFor($this->data);
    }

    public static function getFilteredTransactionsQueryFor(array $data = []): Builder
    {
        $query = Transaction::query()
            ->with('category');

        [$from, $until] = static::resolveDateRangeFor($data);

        if ($from && $until) {
            $query->whereBetween('date', [$from, $until]);
        }

        $transactionType = $data['transaction_type'] ?? 'all';

        if (in_array($transactionType, ['IN', 'OUT'], true)) {
            $query->whereHas('category', fn (Builder $builder) => $builder->where('type', $transactionType));
        }

        return $query;
    }

    protected function resolveDateRange(): array
    {
        return static::resolveDateRangeFor($this->data);
    }

    public static function resolveDateRangeFor(array $data = []): array
    {
        $period = $data['period'] ?? '1_month';
        $dateFrom = $data['date_from'] ?? null;
        $dateUntil = $data['date_until'] ?? null;
        $now = Carbon::now();

        return match ($period) {
            '1_week' => [$now->copy()->subDays(6)->startOfDay(), $now->copy()->endOfDay()],
            '2_weeks' => [$now->copy()->subDays(13)->startOfDay(), $now->copy()->endOfDay()],
            '1_month' => [$now->copy()->subMonth()->startOfDay(), $now->copy()->endOfDay()],
            '2_months' => [$now->copy()->subMonths(2)->startOfDay(), $now->copy()->endOfDay()],
            '3_months' => [$now->copy()->subMonths(3)->startOfDay(), $now->copy()->endOfDay()],
            '1_year' => [$now->copy()->subYear()->startOfDay(), $now->copy()->endOfDay()],
            'custom' => ($dateFrom && $dateUntil)
                ? [Carbon::parse($dateFrom)->startOfDay(), Carbon::parse($dateUntil)->endOfDay()]
                : [null, null],
            default => [null, null],
        };
    }

    public static function printView(Request $request)
    {
        abort_unless(auth()->check(), 403);
        abort_unless(auth()->user()?->canAccessPanel(Filament::getPanel('admin')), 403);

        $data = static::buildReportData($request->only(['period', 'transaction_type', 'date_from', 'date_until']), includeTransactions: true);

        return response()->view('financial-report-print', $data);
    }
}
