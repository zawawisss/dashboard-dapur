<div class="ops-home">
    <section class="ops-home__hero">
        <div>
            <p class="ops-home__eyebrow">Mulai dari sini</p>
            <h2 class="ops-home__title">Kelola kas harian dari satu tempat yang ringkas.</h2>
            <p class="ops-home__desc">
                Lihat ringkasan, telusuri transaksi, atur periode, dan cetak laporan sesuai kebutuhan.
            </p>
        </div>

        <div class="ops-home__actions">
            <a href="{{ $cashBookUrl }}" class="ops-home__action ops-home__action--primary">
                <span class="ops-home__action-title">Buka Buku Kas</span>
                <span class="ops-home__action-text">Catat transaksi, lihat ringkasan, ubah data, dan cetak laporan kas.</span>
            </a>

            @if ($categoryUrl)
                <a href="{{ $categoryUrl }}" class="ops-home__action">
                    <span class="ops-home__action-title">Atur Kategori Transaksi</span>
                    <span class="ops-home__action-text">Menu admin untuk menambah atau merapikan kategori, bukan bagian kerja harian.</span>
                </a>
            @endif

            <a href="{{ $profileUrl }}" class="ops-home__action">
                <span class="ops-home__action-title">Pengaturan Profil</span>
                <span class="ops-home__action-text">Ubah username atau password akun.</span>
            </a>
        </div>
    </section>

    <style>
        .ops-home {
            margin-bottom: 1.5rem;
        }

        .ops-home__hero {
            display: grid;
            gap: 1.25rem;
            padding: 1.5rem;
            border: 1px solid rgba(148, 163, 184, 0.18);
            border-radius: 1rem;
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.10), rgba(255, 255, 255, 0.96));
        }

        .dark .ops-home__hero {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.10), rgba(17, 24, 39, 0.96));
            border-color: rgba(255, 255, 255, 0.08);
        }

        .ops-home__eyebrow {
            margin: 0 0 0.5rem;
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: rgb(180 83 9);
        }

        .ops-home__title {
            margin: 0;
            font-size: 1.8rem;
            line-height: 1.15;
            font-weight: 800;
        }

        .ops-home__desc {
            margin: 0.75rem 0 0;
            max-width: 52rem;
            color: rgb(75 85 99);
        }

        .dark .ops-home__desc {
            color: rgb(209 213 219);
        }

        .ops-home__actions {
            display: grid;
            gap: 0.9rem;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .ops-home__action {
            display: block;
            text-decoration: none;
            color: inherit;
            border: 1px solid rgba(148, 163, 184, 0.18);
            border-radius: 0.9rem;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.8);
        }

        .dark .ops-home__action {
            background: rgba(17, 24, 39, 0.7);
            border-color: rgba(255, 255, 255, 0.08);
        }

        .ops-home__action--primary {
            border-color: rgba(245, 158, 11, 0.35);
            background: rgba(255, 251, 235, 0.95);
        }

        .dark .ops-home__action--primary {
            background: rgba(120, 53, 15, 0.18);
        }

        .ops-home__action-title {
            display: block;
            font-weight: 700;
        }

        .ops-home__action-text {
            display: block;
            margin-top: 0.35rem;
            font-size: 0.92rem;
            color: rgb(75 85 99);
        }

        .dark .ops-home__action-text {
            color: rgb(156 163 175);
        }

        @media (max-width: 768px) {
            .ops-home__actions {
                grid-template-columns: 1fr;
            }

            .ops-home__title {
                font-size: 1.45rem;
            }
        }
    </style>
</div>
