<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Transaction;
use Filament\Actions\Action as FilamentAction;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Livewire\Component;

class FloatingTransactionButton extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    public function createTransactionAction(): Action
    {
        return Action::make('createTransaction')
            ->label('Catat Transaksi')
            ->form([
                Repeater::make('transactions')
                    ->label(false)
                    ->schema([
                        Grid::make(['default' => 1, 'md' => 2])
                            ->schema([
                                Radio::make('transaction_type')
                                    ->label('Tipe')
                                    ->options([
                                        'IN' => 'Pemasukan',
                                        'OUT' => 'Pengeluaran',
                                    ])
                                    ->inline()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(fn ($set) => $set('category_id', null)),

                                Select::make('category_id')
                                    ->label('Kategori')
                                    ->required()
                                    ->options(function (Get $get) {
                                        $type = $get('transaction_type');
                                        return Category::where('type', $type ?: 'OUT')->pluck('name', 'id');
                                    })
                                    ->searchable(),
                            ]),

                        Grid::make(['default' => 1, 'md' => 2])
                            ->schema([
                                TextInput::make('amount')
                                    ->label('Nominal (Rp)')
                                    ->required()
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->placeholder('0'),

                                DatePicker::make('date')
                                    ->label('Tanggal')
                                    ->required()
                                    ->default(now())
                                    ->native(false),
                            ]),

                        TextInput::make('note')
                            ->label('Keterangan')
                            ->placeholder('Misal: Belanja sayur, penjualan soto...')
                            ->columnSpanFull(),

                        FileUpload::make('receipt')
                            ->label('Foto Nota / Struk (Opsional)')
                            ->image()
                            ->directory('receipts')
                            ->visibility('public')
                            ->imageEditor()
                            ->maxSize(2048)
                            ->columnSpanFull(),
                    ])
                    ->addActionLabel('+ Tambah Baris Transaksi')
                    ->minItems(1)
                    ->defaultItems(1)
                    ->reorderable(false)
                    ->cloneable()
                    ->collapsible()
                    ->collapseAllAction(fn ($action) => $action->label('Tutup Semua'))
                    ->expandAllAction(fn ($action) => $action->label('Buka Semua'))
                    ->addAction(fn (\Filament\Actions\Action $action) => $action
                        ->label('+ Tambah Transaksi Lagi')
                        ->action(function (Repeater $component, $livewire) {
                            // 1. Kirim sinyal ke skrip JS di tampilan
                            $livewire->dispatch('repeater::collapseAll');

                            // 2. Tambah baris secara manual (Jurus Stabil)
                            $newUuid = (string) \Illuminate\Support\Str::uuid();
                            $items = $component->getRawState() ?? [];
                            $items[$newUuid] = [];
                            $component->rawState($items);
                            $component->getChildSchema($newUuid)->fill();
                        })
                    )
                    ->itemLabel(function (array $state, $uuid, $component): string {
                        $type = $state['transaction_type'] ?? null;
                        $amount = isset($state['amount']) && $state['amount'] !== ''
                            ? 'Rp ' . number_format((float) $state['amount'], 0, ',', '.')
                            : null;

                        $indicators = [
                            'IN' => '🟩 PEMASUKAN',
                            'OUT' => '🟥 PENGELUARAN',
                            '' => '⬜ MASUKKAN DATA',
                        ];

                        $labelType = $indicators[$type] ?? $indicators[''];

                        $items = $component->getState();
                        $keys = array_keys($items);
                        $index = array_search($uuid, $keys);
                        $num = $index + 1;

                        return "{$labelType} #{$num}" . ($amount ? " — {$amount}" : "");
                    })
            ->columnSpanFull(),
            ])
            ->action(function (array $data) {
                abort_unless(auth()->user()?->isAdmin(), 403);

                $rows = $data['transactions'] ?? [];

                foreach ($rows as $item) {
                    // Set 'name' secara cerdas: Pakai Note jika ada, atau nama Kategori
                    $categoryName = Category::find($item['category_id'])?->name;
                    $item['name'] = $item['note'] ?: ($categoryName ?: 'Transaksi Tanpa Nama');

                    unset($item['transaction_type']);
                    $item['user_id'] = auth()->id();
                    Transaction::create($item);
                }

                $count = count($rows);

                Notification::make()
                    ->title("{$count} transaksi berhasil dicatat.")
                    ->success()
                    ->send();

                return redirect(request()->header('Referer'));
            })
            ->modalWidth('2xl')
            ->modalHeading('Catat Transaksi')
            ->modalDescription('Tambahkan satu atau beberapa transaksi sekaligus, lalu simpan dalam sekali klik.')
            ->modalSubmitActionLabel('Simpan Semua Transaksi')
            ->visible(fn (): bool => auth()->user()?->isAdmin() === true);
    }

    public function render()
    {
        return view('livewire.floating-transaction-button');
    }
}
