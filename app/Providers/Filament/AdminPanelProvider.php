<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use App\Filament\Admin\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\HtmlString;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ->path('admin')
            ->login(\App\Filament\Auth\Login::class)
            ->sidebarCollapsibleOnDesktop()
            ->brandLogo(new HtmlString(sprintf(
                '<div class="fi-logo-mark"><img src="%s" alt="SPPG JORESAN logo"><span class="fi-logo-text">SPPG JORESAN</span></div>',
                asset(file_exists(public_path('logobgn.png')) ? 'logobgn.png' : 'logo-sppg.png'),
            )))
            ->brandLogoHeight('3rem')
            ->brandName('SPPG JORESAN')
            ->userMenuItems([
                'profile' => \Filament\Navigation\MenuItem::make()
                    ->label('Pengaturan Profil')
                    ->url(fn (): string => \App\Filament\Admin\Pages\Setting::getUrl())
                    ->icon('heroicon-o-cog-6-tooth'),
            ])
            ->colors([
                'primary' => Color::Amber,
            ])
            ->renderHook(
                \Filament\View\PanelsRenderHook::HEAD_END,
                fn (): string => '<style>
                    /* ===== Desktop sidebar branding ===== */
                    .fi-sidebar-nav,
                    .fi-sidebar-header {
                        padding-left: 1.75rem !important;
                        padding-right: 1rem !important;
                    }
                    .fi-logo { align-items: center; gap: 0.75rem; }
                    .fi-logo-mark { display: inline-flex; align-items: center; gap: 0.75rem; }
                    .fi-logo img { width: 3rem; height: 3rem; object-fit: contain; }
                    .fi-logo-text { font-size: 1rem; font-weight: 800; letter-spacing: 0.08em; line-height: 1; }

                    /* ===== Mobile: hide sidebar, add bottom padding ===== */
                    @media (max-width: 1023px) {
                        /* Filament uses lg breakpoint (1024px) to toggle sidebar */
                        .fi-sidebar {
                            display: none !important;
                        }
                        .fi-topbar {
                            /* Keep topbar but give extra room */
                            padding-left: 0.75rem !important;
                            padding-right: 0.75rem !important;
                        }
                        /* Push main content above bottom nav */
                        .fi-main {
                            padding-bottom: 5.5rem !important;
                        }
                        /* Shrink page heading on mobile */
                        .fi-header-heading {
                            font-size: 1.125rem !important;
                        }
                        /* Make tables scroll horizontally */
                        .fi-ta-content {
                            overflow-x: auto;
                        }
                        /* FAB position adjustment on mobile */
                        .fab-btn {
                            bottom: 5.5rem !important;
                            width: 3rem !important;
                            height: 3rem !important;
                        }
                    }

                    /* ===== Mobile: modal fullscreen ===== */
                    @media (max-width: 1023px) {
                        /* Fullscreen overlay */
                        .fi-modal-window {
                            position: fixed !important;
                            inset: 0 !important;
                            max-width: 100vw !important;
                            width: 100vw !important;
                            height: 100dvh !important;
                            max-height: 100dvh !important;
                            border-radius: 0 !important;
                            margin: 0 !important;
                            display: flex !important;
                            flex-direction: column !important;
                            overflow: hidden !important;
                            z-index: 10000 !important;
                            padding-bottom: 0 !important;
                            background: var(--color-white, #fff);
                        }
                        /* Header sticky di atas */
                        .fi-modal-header {
                            flex-shrink: 0;
                            position: sticky;
                            top: 0;
                            z-index: 10001;
                        }
                        /* Footer jadi bar aksi tetap di bawah layar */
                        .fi-modal-footer {
                            flex-shrink: 0;
                            position: fixed !important;
                            left: 0;
                            right: 0;
                            bottom: 0;
                            z-index: 10002 !important;
                            padding: 0.75rem 1rem calc(env(safe-area-inset-bottom, 0.5rem) + 0.75rem) !important;
                            background: rgba(255, 255, 255, 0.98) !important;
                            border-top: 1px solid rgba(0, 0, 0, 0.08);
                            box-shadow: 0 -8px 24px rgba(0, 0, 0, 0.12);
                        }
                        /* Konten tengah bisa scroll */
                        .fi-modal-content {
                            flex: 1 1 auto;
                            overflow-y: auto;
                            -webkit-overflow-scrolling: touch;
                            padding-bottom: 7rem !important;
                        }
                        /* Backdrop fullscreen */
                        .fi-modal-backdrop {
                            display: none !important;
                        }
                        /* Sembunyikan bottom nav & FAB saat modal terbuka */
                        body.fi-modal-open .mobile-bottom-nav,
                        body.fi-modal-open .fab-btn,
                        body:has(.fi-modal-window) .mobile-bottom-nav,
                        body:has(.fi-modal-window) .fab-btn {
                            display: none !important;
                        }
                        .fi-modal-footer .fi-btn,
                        .fi-modal-footer button,
                        .fi-modal-footer [type="submit"] {
                            min-height: 3rem;
                        }
                    }

                    @media (max-width: 1023px) and (prefers-color-scheme: dark) {
                        .fi-modal-footer {
                            background: rgba(17, 24, 39, 0.98) !important;
                            border-top-color: rgba(255, 255, 255, 0.08);
                        }
                    }

                    /* ===== Bottom Nav Bar ===== */
                    .mobile-bottom-nav {
                        display: none;
                        position: fixed;
                        bottom: 0;
                        left: 0;
                        right: 0;
                        z-index: 9998;
                        height: 4.25rem;
                        background: rgba(255, 255, 255, 0.92);
                        backdrop-filter: blur(16px);
                        -webkit-backdrop-filter: blur(16px);
                        border-top: 1px solid rgba(0,0,0,0.08);
                        box-shadow: 0 -4px 24px rgba(0,0,0,0.07);
                        padding: 0 0.5rem;
                        align-items: center;
                        justify-content: space-around;
                    }
                    @media (prefers-color-scheme: dark) {
                        .mobile-bottom-nav {
                            background: rgba(17,24,39,0.92);
                            border-top-color: rgba(255,255,255,0.08);
                        }
                    }
                    @media (max-width: 1023px) {
                        .mobile-bottom-nav { display: flex; }
                    }
                    .mob-nav-item {
                        display: flex;
                        flex-direction: column;
                        align-items: center;
                        gap: 0.15rem;
                        padding: 0.45rem 0.75rem;
                        border-radius: 0.75rem;
                        text-decoration: none;
                        color: #6b7280;
                        font-size: 0.65rem;
                        font-weight: 500;
                        transition: color 0.2s, background 0.2s, transform 0.15s;
                        min-width: 3.5rem;
                        cursor: pointer;
                        border: none;
                        background: transparent;
                        letter-spacing: 0.01em;
                    }
                    .mob-nav-item svg {
                        width: 1.35rem;
                        height: 1.35rem;
                        flex-shrink: 0;
                    }
                    .mob-nav-item.active {
                        color: var(--primary-600, #d97706);
                        background: rgba(217,119,6,0.1);
                    }
                    .mob-nav-item:hover:not(.active) {
                        color: #374151;
                        background: rgba(0,0,0,0.05);
                    }
                    .mob-nav-item:active {
                        transform: scale(0.93);
                    }
                    @media (prefers-color-scheme: dark) {
                        .mob-nav-item { color: #9ca3af; }
                        .mob-nav-item:hover:not(.active) { color: #e5e7eb; background: rgba(255,255,255,0.06); }
                        .mob-nav-item.active { color: var(--primary-400, #fbbf24); background: rgba(251,191,36,0.12); }
                    }
                </style>',
            )
            ->renderHook(
                \Filament\View\PanelsRenderHook::BODY_END,
                fn (): string => (
                    auth()->check()
                    && auth()->user()?->isAdmin()
                    && ! request()->is('admin/categories*')
                    && ! request()->is('admin/login')
                    && ! request()->is('admin/setting')
                ? \Illuminate\Support\Facades\Blade::render('@livewire("floating-transaction-button")') : '') . '
                <nav class="mobile-bottom-nav" role="navigation" aria-label="Navigasi utama">

                    <a href="/admin" class="mob-nav-item" id="mob-nav-beranda"
                       x-data x-bind:class="window.location.pathname === \'\'/admin\'\' ? \'active\' : \'\'">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                        </svg>
                        Beranda
                    </a>

                    <a href="/admin/buku-kas" class="mob-nav-item" id="mob-nav-kas"
                       x-data x-bind:class="window.location.pathname.startsWith(\'\'/admin/buku-kas\'\') ? \'active\' : \'\'">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                        </svg>
                        Buku Kas
                    </a>

                    <a href="/admin/setting" class="mob-nav-item" id="mob-nav-profil"
                       x-data x-bind:class="window.location.pathname.startsWith(\'\'/admin/setting\'\') ? \'active\' : \'\'">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>
                        Profil
                    </a>

                </nav>
                <script>
                    // Highlight active nav item on page load
                    document.addEventListener("DOMContentLoaded", function() {
                        var path = window.location.pathname;
                        document.querySelectorAll(".mob-nav-item").forEach(function(el) {
                            var href = el.getAttribute("href");
                            var isActive = (href === "/admin" && path === "/admin")
                                        || (href !== "/admin" && path.startsWith(href));
                            if (isActive) el.classList.add("active");
                        });
                    });
                </script>',
            )
            ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\Filament\Admin\Resources')
            ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\Filament\Admin\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Admin/Widgets'), for: 'App\Filament\Admin\Widgets')
            ->widgets([
                // FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
