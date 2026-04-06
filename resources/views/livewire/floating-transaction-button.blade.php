<div>
    {{-- FAB: Floating Action Button untuk catat transaksi cepat --}}
    <button
        wire:click="mountAction('createTransaction')"
        title="Catat Transaksi Baru"
        class="fab-btn"
    >
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width: 1.75rem; height: 1.75rem;">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
    </button>

    {{-- Mount modal Filament --}}
    <x-filament-actions::modals />

    <style>
        .fab-btn {
            position: fixed;
            bottom: 1.75rem;
            right: 1.75rem;
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 3.5rem;
            height: 3.5rem;
            background: linear-gradient(135deg, var(--primary-600, #d97706), var(--primary-500, #f59e0b));
            color: white;
            border-radius: 9999px;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 20px rgba(217, 119, 6, 0.45), 0 2px 8px rgba(0,0,0,0.15);
            transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
            outline: none;
        }

        .fab-btn:hover {
            transform: scale(1.1) translateY(-2px);
            box-shadow: 0 8px 28px rgba(217, 119, 6, 0.55), 0 4px 12px rgba(0,0,0,0.2);
            background: linear-gradient(135deg, var(--primary-500, #f59e0b), var(--primary-400, #fbbf24));
        }

        .fab-btn:active {
            transform: scale(0.97);
            box-shadow: 0 2px 10px rgba(217, 119, 6, 0.35);
        }
    </style>
    <script>
        document.addEventListener('livewire:init', () => {
            // Logika Radar: Cegat klik di fase awal (Capture Phase)
            document.addEventListener('click', (e) => {
                const header = e.target.closest('.fi-fo-repeater-item-header');
                if (!header) return;

                const currentItem = header.closest('.fi-fo-repeater-item');
                if (!currentItem) return;

                // Jika baris yang diklik SEDANG TERTUTUP (ingin dibuka)
                // Di fase Capture, class 'fi-collapsed' masih menempel karena belum diproses Alpine
                if (currentItem.classList.contains('fi-collapsed')) {
                    // Cari SEMUA baris di grup yang sama
                    const allItems = currentItem.parentElement.querySelectorAll('.fi-fo-repeater-item');
                    
                    allItems.forEach(item => {
                        // Lipat semua KECUALI yang baru saja diklik
                        if (item !== currentItem && !item.classList.contains('fi-collapsed')) {
                            const otherHeader = item.querySelector('.fi-fo-repeater-item-header');
                            if (otherHeader) otherHeader.click();
                        }
                    });
                }
            }, true); // TRUE = Capture Phase (Cegat sebelum sistem lain merespon)

            // Logika untuk Accordion saat menambah baris baru (Sinyal dari Server)
            Livewire.on('repeater::collapseAll', () => {
                setTimeout(() => {
                    const items = document.querySelectorAll('.fi-fo-repeater-item');
                    if (items.length > 1) {
                        for (let i = 0; i < items.length - 1; i++) {
                            const item = items[i];
                            const header = item.querySelector('.fi-fo-repeater-item-header');
                            if (header && !item.classList.contains('fi-collapsed')) {
                                header.click();
                            }
                        }
                    }
                }, 100);
            });
        });
    </script>
</div>
