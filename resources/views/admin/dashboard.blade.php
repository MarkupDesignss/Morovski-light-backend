@extends('layouts.admin')

@section('title', __('admin.dashboard_title'))
<style>
    * {
        box-sizing: border-box;
    }

    .dash-wrap {
        min-height: 100vh;
        padding: 0.25rem 0 2rem;
    }

    /* ── Success Banner ── */
    .success-banner {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: linear-gradient(135deg, #2a1a05 0%, #1a0f00 100%);
        border: 1px solid rgba(162, 128, 81, 0.4);
        border-radius: 14px;
        padding: 14px 18px;
        margin-bottom: 1.5rem;
        position: relative;
        overflow: hidden;
    }

    .success-banner::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: linear-gradient(90deg, transparent, #A28051, #C5A672, #A28051, transparent);
    }

    .success-banner-left {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .success-icon-wrap {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: rgba(162, 128, 81, 0.15);
        border: 1px solid rgba(162, 128, 81, 0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .success-icon-wrap svg {
        color: #A28051;
        width: 18px;
        height: 18px;
    }

    .success-title {
        font-size: 14px;
        font-weight: 600;
        color: #e8c98a;
        font-family: Georgia, serif;
    }

    .success-sub {
        font-size: 11px;
        color: rgba(212, 180, 131, 0.6);
        letter-spacing: 0.5px;
        margin-top: 2px;
    }

    .success-close {
        background: none;
        border: none;
        color: rgba(162, 128, 81, 0.5);
        cursor: pointer;
        padding: 4px;
        transition: color 0.2s;
    }

    .success-close:hover {
        color: #A28051;
    }

    .success-progress {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: rgba(162, 128, 81, 0.15);
    }

    .success-progress-bar {
        height: 100%;
        background: linear-gradient(90deg, #A28051, #C5A672);
        width: 100%;
        animation: shrink 3s linear forwards;
    }

    @keyframes shrink {
        from {
            width: 100%;
        }

        to {
            width: 0%;
        }
    }

    /* ── Page Header ── */
    .dash-header {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.5rem 1.75rem;
        background: linear-gradient(160deg, #fdf8f0 0%, #faf3e8 100%);
        border: 1px solid rgba(162, 128, 81, 0.22);
        border-radius: 18px;
        margin-bottom: 1.75rem;
        position: relative;
        overflow: hidden;
    }

    .dash-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: linear-gradient(90deg, transparent, #A28051, #C5A672, #A28051, transparent);
    }

    .dash-header-title {
        font-size: 26px;
        font-weight: 700;
        color: #2a1a05;
        letter-spacing: 1.5px;
        font-family: Georgia, serif;
        margin: 0;
    }

    .dash-header-title span {
        color: #A28051;
    }

    .dash-header-sub {
        font-size: 11px;
        color: #8a6a3a;
        letter-spacing: 2px;
        text-transform: uppercase;
        margin-top: 4px;
    }

    .dash-date-pill {
        background: linear-gradient(135deg, #2a1a05 0%, #1a0f00 100%);
        border: 1px solid rgba(162, 128, 81, 0.4);
        border-radius: 10px;
        padding: 9px 18px;
        font-size: 12px;
        font-weight: 600;
        color: #d4b483;
        letter-spacing: 1px;
        font-family: Georgia, serif;
        white-space: nowrap;
    }

    /* ── Stats Grid ── */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1.25rem;
        margin-bottom: 1.75rem;
    }

    .stat-card {
        background: linear-gradient(160deg, #fdf8f0 0%, #faf3e8 100%);
        border: 1px solid rgba(162, 128, 81, 0.2);
        border-radius: 18px;
        padding: 1.5rem;
        position: relative;
        overflow: hidden;
        transition: transform 0.25s, box-shadow 0.25s, border-color 0.25s;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: linear-gradient(90deg, transparent, var(--card-accent), transparent);
        opacity: 0;
        transition: opacity 0.3s;
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 32px rgba(162, 128, 81, 0.15);
        border-color: rgba(162, 128, 81, 0.4);
    }

    .stat-card:hover::before {
        opacity: 1;
    }

    .stat-card-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.1rem;
    }

    .stat-icon-wrap {
        width: 50px;
        height: 50px;
        border-radius: 14px;
        background: linear-gradient(145deg, #f0e4cc, #e8d5b4);
        border: 1px solid rgba(162, 128, 81, 0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .stat-icon-wrap svg {
        width: 24px;
        height: 24px;
        color: #A28051;
    }

    .stat-badge {
        font-size: 10px;
        font-weight: 600;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        padding: 5px 11px;
        border-radius: 20px;
        background: rgba(162, 128, 81, 0.1);
        color: #8a6a3a;
        border: 1px solid rgba(162, 128, 81, 0.25);
    }

    .stat-number {
        font-size: 44px;
        font-weight: 800;
        color: #2a1a05;
        letter-spacing: -1px;
        line-height: 1;
        margin-bottom: 4px;
        font-family: Georgia, serif;
    }

    .stat-label {
        font-size: 10px;
        color: #8a6a3a;
        letter-spacing: 2px;
        text-transform: uppercase;
        font-weight: 500;
    }

    .stat-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 1.1rem;
        padding-top: 1rem;
        border-top: 1px solid rgba(162, 128, 81, 0.15);
    }

    .stat-pulse-wrap {
        display: flex;
        align-items: center;
        gap: 7px;
    }

    .stat-pulse {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #A28051;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {

        0%,
        100% {
            opacity: 1;
            transform: scale(1);
        }

        50% {
            opacity: 0.5;
            transform: scale(0.85);
        }
    }

    .stat-pulse-label {
        font-size: 11px;
        color: rgba(120, 88, 42, 0.6);
    }

    .stat-link {
        font-size: 12px;
        font-weight: 600;
        color: #A28051;
        text-decoration: none;
        letter-spacing: 0.3px;
        display: flex;
        align-items: center;
        gap: 4px;
        transition: color 0.2s, gap 0.2s;
    }

    .stat-link:hover {
        color: #7a5e30;
        gap: 7px;
    }

    /* ── Bottom Grid ── */
    .bottom-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.25rem;
    }

    .panel {
        background: linear-gradient(160deg, #fdf8f0 0%, #faf3e8 100%);
        border: 1px solid rgba(162, 128, 81, 0.2);
        border-radius: 18px;
        overflow: hidden;
    }

    .panel-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid rgba(162, 128, 81, 0.15);
        background: linear-gradient(90deg, rgba(162, 128, 81, 0.06), transparent);
        display: flex;
        align-items: center;
        gap: 9px;
    }

    .panel-header svg {
        width: 18px;
        height: 18px;
        color: #A28051;
    }

    .panel-title {
        font-size: 13px;
        font-weight: 600;
        color: #2a1a05;
        letter-spacing: 1px;
        font-family: Georgia, serif;
    }

    .panel-body {
        padding: 1rem;
    }

    .quick-action-btn {
        display: flex;
        align-items: center;
        gap: 12px;
        width: 100%;
        padding: 12px 14px;
        border-radius: 12px;
        background: rgba(162, 128, 81, 0.06);
        border: 1px solid rgba(162, 128, 81, 0.18);
        text-decoration: none;
        transition: all 0.2s;
        margin-bottom: 10px;
    }

    .quick-action-btn:last-child {
        margin-bottom: 0;
    }

    .quick-action-btn:hover {
        background: rgba(162, 128, 81, 0.12);
        border-color: rgba(162, 128, 81, 0.35);
        transform: translateX(3px);
    }

    .quick-action-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: linear-gradient(145deg, #f0e4cc, #e8d5b4);
        border: 1px solid rgba(162, 128, 81, 0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .quick-action-icon svg {
        width: 16px;
        height: 16px;
        color: #A28051;
    }

    .quick-action-text {
        font-size: 13px;
        font-weight: 500;
        color: #2a1a05;
    }

    .quick-action-arrow {
        margin-left: auto;
        font-size: 14px;
        color: rgba(162, 128, 81, 0.5);
        transition: color 0.2s;
    }

    .quick-action-btn:hover .quick-action-arrow {
        color: #A28051;
    }

    .ornament-line {
        text-align: center;
        color: rgba(162, 128, 81, 0.3);
        font-size: 11px;
        letter-spacing: 6px;
        padding: 1rem 0 0;
    }
</style>
@section('content')
    <div class="dash-wrap">

        {{-- SUCCESS MESSAGE --}}
        @if (session('success'))
            <div id="success-message" class="success-banner">
                <div class="success-banner-left">
                    <div class="success-icon-wrap">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <div class="success-title">{{ session('success') }}</div>
                        <div class="success-sub">{{ __('admin.success') }}</div>
                    </div>
                </div>
                <button class="success-close" onclick="this.closest('.success-banner').remove()">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                <div class="success-progress">
                    <div class="success-progress-bar"></div>
                </div>
            </div>
        @endif

        {{-- PAGE HEADER --}}
        <div class="dash-header">
            <div>
                <h2 class="dash-header-title">
                    {{ __('admin.dashboard_overview') }} <span>✦</span>
                </h2>
                <p class="dash-header-sub">{{ __('admin.welcome_back') }}</p>
            </div>
            <div class="dash-date-pill">{{ now()->format('l, F j, Y') }}</div>
        </div>

        {{-- STATS CARDS --}}
        <div class="stats-grid">

            {{-- Users --}}
            <div class="stat-card" style="--card-accent: #A28051;">
                <div class="stat-card-top">
                    <div class="stat-icon-wrap">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 10a6 6 0 00-6 6v1h12v-1a6 6 0 00-6-6z" />
                        </svg>
                    </div>
                    <span class="stat-badge">{{ __('admin.accounts') }}</span>
                </div>
                <div class="stat-number">{{ $totalUsers }}</div>
                <div class="stat-label">{{ __('admin.total_users') }}</div>
                <div class="stat-footer">
                    <div class="stat-pulse-wrap">
                        <div class="stat-pulse"></div>
                        <span class="stat-pulse-label">{{ __('admin.active_members') }}</span>
                    </div>
                    <a href="{{ route('admin.users.index') }}" class="stat-link">
                        {{ __('admin.manage') }} <span>→</span>
                    </a>
                </div>
            </div>

            {{-- Categories --}}
            <div class="stat-card" style="--card-accent: #C5A672;">
                <div class="stat-card-top">
                    <div class="stat-icon-wrap">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <span class="stat-badge">{{ __('admin.collections') }}</span>
                </div>
                <div class="stat-number">{{ $Category }}</div>
                <div class="stat-label">{{ __('admin.total_categories') }}</div>
                <div class="stat-footer">
                    <div class="stat-pulse-wrap">
                        <div class="stat-pulse"></div>
                        <span class="stat-pulse-label">{{ __('admin.active_organized') }}</span>
                    </div>
                    <a href="{{ route('admin.categories.index') }}" class="stat-link">
                        {{ __('admin.explore') }} <span>→</span>
                    </a>
                </div>
            </div>

            {{-- Items --}}
            <div class="stat-card" style="--card-accent: #d4b483;">
                <div class="stat-card-top">
                    <div class="stat-icon-wrap">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <span class="stat-badge">{{ __('admin.offerings') }}</span>
                </div>
                <div class="stat-number">{{$totalItems ?? '0'}}</div>
                <div class="stat-label">{{ __('admin.total_items') }}</div>
                <div class="stat-footer">
                    <div class="stat-pulse-wrap">
                        <div class="stat-pulse"></div>
                        <span class="stat-pulse-label">{{ __('admin.premium_quality') }}</span>
                    </div>
                    <a href="{{ route('admin.items.index') }}" class="stat-link">
                        {{ __('admin.manage') }} <span>→</span>
                    </a>
                </div>
            </div>

            {{-- Orders --}}
            <div class="stat-card" style="--card-accent: #b8975a;">
                <div class="stat-card-top">
                    <div class="stat-icon-wrap">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                    <span class="stat-badge">{{ __('admin.sales') }}</span>
                </div>
                <div class="stat-number">{{$totalOrders ?? '0'}}</div>
                <div class="stat-label">{{ __('admin.total_orders') }}</div>
                <div class="stat-footer">
                    <div class="stat-pulse-wrap">
                        <div class="stat-pulse"></div>
                        <span class="stat-pulse-label">{{ __('admin.revenue_tracker') }}</span>
                    </div>
                    <a href="{{ route('admin.orders.index') }}" class="stat-link">
                        {{ __('admin.view_all') }} <span>→</span>
                    </a>
                </div>
            </div>

        </div>

        {{-- BOTTOM PANELS --}}
        <div class="bottom-grid">

            {{-- Quick Actions --}}
            <div class="panel">
                <div class="panel-header">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <span class="panel-title">{{ __('admin.quick_actions') }}</span>
                </div>
                <div class="panel-body">
                    <a href="{{ route('admin.categories.create') }}" class="quick-action-btn">
                        <div class="quick-action-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l5 5a2 2 0 01.586 1.414V19a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z" />
                            </svg>
                        </div>
                        <span class="quick-action-text">{{ __('admin.add_category') }}</span>
                        <span class="quick-action-arrow">→</span>
                    </a>
                    <a href="{{ route('admin.items.index') }}" class="quick-action-btn">
                        <div class="quick-action-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                        </div>
                        <span class="quick-action-text">{{ __('admin.total_items') }}</span>
                        <span class="quick-action-arrow">→</span>
                    </a>
                    <a href="{{ route('admin.orders.index') }}" class="quick-action-btn">
                        <div class="quick-action-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                        </div>
                        <span class="quick-action-text">{{ __('admin.total_orders') }}</span>
                        <span class="quick-action-arrow">→</span>
                    </a>
                </div>
            </div>

        </div>

        <div class="ornament-line">✦ &nbsp; &nbsp; ✦ &nbsp; &nbsp; ✦</div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const msg = document.getElementById('success-message');
            if (msg) {
                setTimeout(function() {
                    msg.style.transition = 'opacity 0.5s ease';
                    msg.style.opacity = '0';
                    setTimeout(() => msg.style.display = 'none', 500);
                }, 3000);
            }
        });
    </script>
@endsection
