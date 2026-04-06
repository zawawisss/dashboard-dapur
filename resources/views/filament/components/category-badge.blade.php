<div class="flex items-center justify-between w-full">
    <span>{{ $record->name }}</span>
    <x-filament::badge :color="$record->type === 'IN' ? 'success' : 'danger'" size="sm">
        {{ $record->type === 'IN' ? 'Pemasukan' : 'Pengeluaran' }}
    </x-filament::badge>
</div>
