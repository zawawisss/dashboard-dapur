<?php

namespace App\Filament\Admin\Resources\Transactions;

use App\Models\Category;
use App\Models\Transaction;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static bool $shouldRegisterNavigation = false;

    public static function getNavigationLabel(): string
    {
        return 'Buku Kas';
    }

    public static function getModelLabel(): string
    {
        return 'Transaksi';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Buku Kas (Transaksi)';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'PENGELOLAAN BISNIS';
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
    }

    public static function getFormComponents(): array
    {
        return [
            Radio::make('transaction_type')
                ->label('Jenis Transaksi')
                ->options([
                    'IN' => 'Pemasukan',
                    'OUT' => 'Pengeluaran',
                ])
                ->inline()
                ->required()
                ->live()
                ->dehydrated(false)
                ->afterStateHydrated(function (Radio $component, ?\Illuminate\Database\Eloquent\Model $record) {
                    if ($record) {
                        $component->state($record->type);
                    }
                })
                ->afterStateUpdated(fn ($set) => $set('category_id', null))
                ->columnSpanFull(),

            Select::make('category_id')
                ->label('Pilih Kategori Transaksi')
                ->required()
                ->options(function (Get $get) {
                    $type = $get('transaction_type');

                    if (! $type) {
                        return Category::where('type', 'OUT')->pluck('name', 'id');
                    }

                    return Category::where('type', $type)->pluck('name', 'id');
                })
                ->searchable()
                ->preload()
                ->live()
                ->columnSpanFull(),

            Grid::make(2)
                ->schema([
                    TextInput::make('amount')
                        ->label('Nominal Uang (Rp)')
                        ->required()
                        ->numeric()
                        ->prefix('Rp')
                        ->columnSpan(1),

                    DatePicker::make('date')
                        ->label('Tanggal')
                        ->required()
                        ->default(now())
                        ->native(false)
                        ->columnSpan(1),
                ]),

            Textarea::make('note')
                ->label('Catatan Keterangan')
                ->placeholder('Misal: Belanja sayur atau penjualan soto')
                ->rows(3)
                ->columnSpanFull(),

            Hidden::make('user_id')
                ->default(fn () => auth()->id()),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components(static::getFormComponents());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('category.type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'IN' => 'success',
                        'OUT' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => $state === 'IN' ? 'Pemasukan' : 'Pengeluaran'),

                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->searchable(),

                TextColumn::make('amount')
                    ->label('Nominal')
                    ->money('IDR')
                    ->alignment('right')
                    ->weight('bold')
                    ->color(fn ($record): string => $record->category?->type === 'IN' ? 'success' : 'danger'),

                TextColumn::make('note')
                    ->label('Keterangan')
                    ->limit(30)
                    ->tooltip(fn ($state) => $state),
            ])
            ->filters([
                SelectFilter::make('transaction_type')
                    ->label('Tipe')
                    ->options([
                        'IN' => 'Pemasukan',
                        'OUT' => 'Pengeluaran',
                    ])
                    ->query(function ($query, array $data) {
                        if ($data['value']) {
                            $query->whereHas('category', function ($q) use ($data) {
                                $q->where('type', $data['value']);
                            });
                        }
                    }),
            ])
            ->actions([
                EditAction::make()->modalWidth('xl'),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ])
            ->emptyStateHeading('Belum ada transaksi')
            ->emptyStateDescription('Tambahkan transaksi pemasukan atau pengeluaran untuk mulai mengisi Buku Kas.');
    }

    public static function getPages(): array
    {
        return [];
    }
}
