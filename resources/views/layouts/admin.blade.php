<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    @section('title', 'Sport gems')
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!--<link rel="icon" type="image/png" href="{{ asset('logo/MORVOSKI-logo.png') }}">-->

    @php
        $favicon = \App\Models\HeaderMenu::where('type', 'logo')->value('favicon');
    @endphp

    <link rel="icon" type="image/png"
        href="{{ $favicon ? asset('storage/' . $favicon) : asset('logo/MORVOSKI-logo.png') }}">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Summernote CSS -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons (optional but recommended for icons) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    <!-- Font Awesome (for the icons used in the design) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
        }

        /* ── Layout Shell ── */
        .main-header {
            position: fixed;
            top: 0;
            left: 240px;
            right: 0;
            height: 64px;
            background: linear-gradient(160deg, #fdf8f0 0%, #faf3e8 100%);
            border-bottom: 1px solid rgba(162, 128, 81, 0.25);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.5rem;
            z-index: 100;
        }

        .main-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(162, 128, 81, 0.35), transparent);
        }

        /* ── Welcome text ── */
        .welcome-text h4 {
            font-size: 14px;
            font-weight: 600;
            color: #2a1a05;
            letter-spacing: 0.3px;
            margin: 0;
            font-family: Georgia, serif;
        }

        .welcome-text p {
            font-size: 10px;
            color: #A28051;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin: 2px 0 0;
        }

        /* ── Header actions ── */
        .header-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* ── Search ── */
        .search-trigger {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.6);
            border: 1px solid rgba(162, 128, 81, 0.28);
            border-radius: 9px;
            padding: 7px 13px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .search-trigger:focus-within {
            border-color: #A28051;
            box-shadow: 0 0 0 3px rgba(162, 128, 81, 0.1);
        }

        .search-trigger .fas.fa-search {
            font-size: 12px;
            color: rgba(162, 128, 81, 0.6);
        }

        .search-input {
            border: none;
            outline: none;
            background: transparent;
            font-size: 13px;
            color: #2a1a05;
            width: 160px;
            font-family: inherit;
        }

        .search-input::placeholder {
            color: rgba(120, 88, 42, 0.4);
        }

        .search-results {
            position: absolute;
            top: 64px;
            background: #fdf8f0;
            border: 1px solid rgba(162, 128, 81, 0.25);
            border-top: none;
            border-radius: 0 0 10px 10px;
            width: 240px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            z-index: 200;
            display: none;
        }

        /* ── Menu toggle ── */
        .menu-toggle {
            width: 36px;
            height: 36px;
            border-radius: 9px;
            border: 1px solid rgba(162, 128, 81, 0.28);
            background: rgba(255, 255, 255, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: border-color 0.2s, background 0.2s;
            padding: 0;
        }

        .menu-toggle:hover {
            border-color: #A28051;
            background: rgba(255, 255, 255, 0.8);
        }

        /* ── User info ── */
        .user-info {
            display: flex;
            align-items: center;
            gap: 9px;
        }

        .user-name {
            font-size: 13px;
            font-weight: 500;
            color: #5a3c14;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, #2a1a05 0%, #1a0f00 100%);
            border: 1.5px solid rgba(162, 128, 81, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 600;
            color: #d4b483;
            cursor: pointer;
            transition: border-color 0.2s, box-shadow 0.2s;
            font-family: Georgia, serif;
        }

        .user-avatar:hover {
            border-color: #A28051;
            box-shadow: 0 0 0 3px rgba(162, 128, 81, 0.15);
        }

        /* ── Sidebar Overlay ── */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.35);
            z-index: 149;
        }

        /* ── Sidebar ── */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: 240px;
            background: linear-gradient(175deg, #160c00 0%, #2a1508 60%, #1a0e03 100%);
            border-right: 1px solid rgba(162, 128, 81, 0.2);
            display: flex;
            flex-direction: column;
            z-index: 150;
            transition: transform 0.3s ease;
        }

        .sidebar::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            width: 1px;
            background: linear-gradient(180deg, transparent, rgba(162, 128, 81, 0.4), transparent);
            pointer-events: none;
        }

        /* ── Logo ── */
        .logo-container {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 1.1rem 1.1rem 1rem;
            border-bottom: 1px solid rgba(162, 128, 81, 0.15);
            text-decoration: none;
        }

        .logo-container img {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(145deg, #f0e4cc, #e8d5b4);
            border: 1px solid rgba(162, 128, 81, 0.4);
            /*padding: 4px;*/
            object-fit: cover;
        }

        .logo-text {
            font-size: 15px !important;
            font-weight: 600;
            letter-spacing: 2.5px;
            color: #e8c98a !important;
            font-family: Georgia, serif;
        }

        /* ── Sidebar Nav ── */
        .sidebar-nav {
            flex: 1;
            padding: 0.6rem 0;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: rgba(162, 128, 81, 0.2) transparent;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 18px;
            color: rgba(255, 255, 255, 0.62);
            font-size: 12.5px;
            letter-spacing: 0.3px;
            cursor: pointer;
            transition: all 0.2s;
            border-left: 2px solid transparent;
            text-decoration: none;
            border-radius: 0;
        }

        .nav-item:hover {
            color: #d4b483;
            background: rgba(162, 128, 81, 0.08);
            border-left-color: rgba(162, 128, 81, 0.35);
        }

        .nav-item.active {
            color: #e8c98a;
            background: rgba(162, 128, 81, 0.13);
            border-left-color: #A28051;
        }

        .nav-icon {
            font-size: 14px;
            width: 16px;
            text-align: center;
            flex-shrink: 0;
        }

        .nav-divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(162, 128, 81, 0.2), transparent);
            margin: 8px 18px;
        }

        /* Logout button reset */
        .sidebar-nav form.nav-item {
            padding: 0;
            background: none;
            border: none;
        }

        .sidebar-nav form.nav-item .btn.nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 18px;
            width: 100%;
            text-align: left;
            color: rgba(210, 100, 100, 0.7);
            font-size: 12.5px;
            letter-spacing: 0.3px;
            border-left: 2px solid transparent;
            text-decoration: none;
            transition: all 0.2s;
            border-radius: 0;
        }

        .sidebar-nav form.nav-item .btn.nav-link:hover {
            color: rgba(230, 110, 110, 0.9);
            background: rgba(200, 80, 80, 0.07);
            border-left-color: rgba(200, 80, 80, 0.3);
        }

        /* ── Main Content ── */
        .main-content {
            margin-left: 240px;
            margin-top: 44px;
            min-height: calc(100vh - 64px);
            background: linear-gradient(145deg, #fdf5e8 0%, #f8edd6 100%);
            padding: 1.5rem;
        }

        .content-card {
            background: rgba(255, 255, 255, 0.65);
            border: 1px solid rgba(162, 128, 81, 0.18);
            border-radius: 16px;
            padding: 1.5rem;
            min-height: calc(100vh - 64px - 3rem);
        }

        /* ── Animations ── */
        .fade-in {
            animation: fadeInUp 0.4s ease both;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ── Notification Icon ── */
        .notification-icon-btn {
            position: relative;
            width: 36px;
            height: 36px;
            border-radius: 9px;
            border: 1px solid rgba(162, 128, 81, 0.28);
            background: rgba(255, 255, 255, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            padding: 0;
            color: #2a1a05;
            font-size: 16px;
        }

        .notification-icon-btn:hover {
            border-color: #A28051;
            background: rgba(255, 255, 255, 0.8);
        }

        .notification-badge {
            position: absolute;
            top: -6px;
            right: -6px;
            background: #ef4444;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: bold;
            border: 2px solid white;
        }

        .notification-dropdown {
            position: absolute;
            top: 64px;
            right: 0;
            width: 380px;
            background: white;
            border: 1px solid rgba(162, 128, 81, 0.25);
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            z-index: 300;
            display: none;
            flex-direction: column;
            max-height: 500px;
        }

        .notification-dropdown.show {
            display: flex;
        }

        .notification-dropdown-header {
            padding: 12px 16px;
            border-bottom: 1px solid rgba(162, 128, 81, 0.15);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .notification-dropdown-header h6 {
            margin: 0;
            font-size: 14px;
            font-weight: 600;
            color: #2a1a05;
        }

        .notification-header-actions {
            display: flex;
            gap: 8px;
        }

        .notification-header-action-btn {
            background: none;
            border: none;
            padding: 4px 8px;
            cursor: pointer;
            color: #A28051;
            font-size: 12px;
            transition: color 0.2s;
        }

        .notification-header-action-btn:hover {
            color: #2a1a05;
        }

        .notification-dropdown-list {
            flex: 1;
            overflow-y: auto;
            max-height: 400px;
        }

        .notification-group {
            margin-bottom: 8px;
        }

        .notification-group-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 16px;
            background: rgba(162, 128, 81, 0.08);
            color: #2a1a05;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.02em;
        }

        .notification-group-count {
            background: #ef4444;
            color: white;
            border-radius: 999px;
            padding: 2px 8px;
            font-size: 10px;
            font-weight: 700;
        }

        .notification-item {
            padding: 12px 16px;
            border-bottom: 1px solid rgba(162, 128, 81, 0.1);
            cursor: pointer;
            transition: background 0.2s;
            display: flex;
            gap: 12px;
        }

        .notification-item:hover {
            background: rgba(162, 128, 81, 0.05);
        }

        .notification-item.unread {
            background: rgba(162, 128, 81, 0.08);
        }

        .notification-item-icon {
            flex-shrink: 0;
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
        }

        .notification-item-icon.high-priority {
            background: rgba(239, 68, 68, 0.1);
            color: #dc2626;
        }

        .notification-item-icon.medium-priority {
            background: rgba(245, 158, 11, 0.1);
            color: #d97706;
        }

        .notification-item-icon.low-priority {
            background: rgba(34, 197, 94, 0.1);
            color: #16a34a;
        }

        .notification-item-content {
            flex: 1;
            min-width: 0;
        }

        .notification-item-title {
            font-size: 13px;
            font-weight: 600;
            color: #2a1a05;
            margin: 0 0 2px;
        }

        .notification-item-message {
            font-size: 12px;
            color: #666;
            margin: 0 0 4px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .notification-item-time {
            font-size: 11px;
            color: #999;
            margin: 0;
        }

        .notification-item-actions {
            display: flex;
            gap: 4px;
            flex-shrink: 0;
            opacity: 0;
            transition: opacity 0.2s;
        }

        .notification-item:hover .notification-item-actions {
            opacity: 1;
        }

        .notification-item-action-btn {
            background: none;
            border: none;
            cursor: pointer;
            padding: 4px;
            color: #A28051;
            font-size: 12px;
            transition: color 0.2s;
        }

        .notification-item-action-btn:hover {
            color: #2a1a05;
        }

        .notification-dropdown-footer {
            padding: 12px 16px;
            border-top: 1px solid rgba(162, 128, 81, 0.15);
            text-align: center;
        }

        .notification-dropdown-footer a {
            font-size: 12px;
            color: #A28051;
            text-decoration: none;
            transition: color 0.2s;
        }

        .notification-dropdown-footer a:hover {
            color: #2a1a05;
            text-decoration: underline;
        }

        .notification-empty {
            padding: 32px 16px;
            text-align: center;
            color: #999;
        }

        .notification-empty i {
            font-size: 32px;
            margin-bottom: 8px;
            opacity: 0.5;
        }

        .btn.btn-collapse {
            display: none;
        }

        .close-sidebar {
            display: none;
        }

        /* ── Mobile ── */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .sidebar-overlay.open {
                display: block;
            }

            .main-header {
                left: 0;
            }

            .main-content {
                margin-left: 0;
            }

            .welcome-text h4,
            .welcome-text p {
                display: none;
            }

            .search-input {
                width: 100px;
            }

            .content-card {
                padding: 0;
            }

            .btn.btn-collapse {
                display: block;
            }

            .sidebar.active {
                transform: translateX(0%);
            }

            .sidebar-overlay.active,
            .close-sidebar {
                display: block;
            }

            .notification-dropdown {
                position: fixed;
                top: 64px;
                right: 20px;
                width: calc(100% - 40px);
                max-width: 380px;
            }

            .dash-wrap {
                padding: 0.25rem 0.25rem 2rem;
            }
        }
    </style>


</head>

<body>


    <header class="main-header">
        <div class="header-left">
            <div class="welcome-text">
                <button class="btn btn-collapse">
                    <i class="fas fa-bars"></i>
                </button>
                <h4>{{ __('admin.welcome_back', ['name' => Auth::guard('admin')->user()->name ?? 'Admin User']) }}</h4>
                <p>{{ __('admin.collection_status') }}</p>
            </div>
        </div>

        <div class="header-actions">
            <!--<div class="search-action" style="position:relative;">-->
            <!--    <div class="search-trigger" id="searchMockTrigger">-->
            <!--        <i class="fas fa-search"></i>-->
            <!--        <input type="text" id="adminSearchInput" class="search-input"-->
            <!--            placeholder="{{ __('admin.search_placeholder') }}" autocomplete="off">-->
            <!--    </div>-->
            <!--    <div id="adminSearchResults" class="search-results text-black"></div>-->
            <!--</div>-->

            <!--<button class="menu-toggle" id="menuToggle" aria-label="Toggle Menu" aria-expanded="false">-->
            <!--    <svg class="menu-icon icon-expand" width="18" height="18" viewBox="0 0 24 24" fill="none"-->
            <!--        xmlns="http://www.w3.org/2000/svg" aria-hidden="true">-->
            <!--        <path d="M3 7V3h4" stroke="#78581e" stroke-width="1.6" stroke-linecap="round"-->
            <!--            stroke-linejoin="round" />-->
            <!--        <path d="M17 3h4v4" stroke="#78581e" stroke-width="1.6" stroke-linecap="round"-->
            <!--            stroke-linejoin="round" />-->
            <!--        <path d="M21 17v4h-4" stroke="#78581e" stroke-width="1.6" stroke-linecap="round"-->
            <!--            stroke-linejoin="round" />-->
            <!--        <path d="M7 21H3v-4" stroke="#78581e" stroke-width="1.6" stroke-linecap="round"-->
            <!--            stroke-linejoin="round" />-->
            <!--    </svg>-->
            <!--    <svg class="menu-icon icon-collapse" width="18" height="18" viewBox="0 0 24 24" fill="none"-->
            <!--        xmlns="http://www.w3.org/2000/svg" aria-hidden="true" style="display:none;">-->
            <!--        <path d="M7 3H3v4" stroke="#78581e" stroke-width="1.6" stroke-linecap="round"-->
            <!--            stroke-linejoin="round" />-->
            <!--        <path d="M21 7v4h-4" stroke="#78581e" stroke-width="1.6" stroke-linecap="round"-->
            <!--            stroke-linejoin="round" />-->
            <!--        <path d="M17 21h-4v-4" stroke="#78581e" stroke-width="1.6" stroke-linecap="round"-->
            <!--            stroke-linejoin="round" />-->
            <!--        <path d="M3 17v-4h4" stroke="#78581e" stroke-width="1.6" stroke-linecap="round"-->
            <!--            stroke-linejoin="round" />-->
            <!--    </svg>-->
            <!--</button>-->

            <!-- Notification Icon -->
            <div style="position: relative;">
                <button class="notification-icon-btn" id="notificationBtn" title="Notifications">
                    <i class="bi bi-bell-fill"></i>
                    <span class="notification-badge" id="notificationBadge" style="display: none;">0</span>
                </button>

                <!-- Notification Dropdown -->
                <div class="notification-dropdown" id="notificationDropdown">
                    <div class="notification-dropdown-header">
                        <h6>Notifications</h6>
                        <div class="notification-header-actions">
                            <button class="notification-header-action-btn" id="markAllReadBtn"
                                title="Mark all as read">
                                <i class="bi bi-check-all"></i> Mark All
                            </button>
                        </div>
                    </div>
                    <div class="notification-dropdown-list" id="notificationList">
                        <div class="notification-empty">
                            <div><i class="bi bi-bell-slash"></i></div>
                            <small>No notifications</small>
                        </div>
                    </div>
                    <div class="notification-dropdown-footer">
                        <a href="{{ route('admin.notifications.index') }}">View All Notifications →</a>
                    </div>
                </div>
            </div>

            <div class="user-info">
                <div class="user-details">
                    <div class="user-name">{{ Auth::guard('admin')->user()->name ?? 'Admin User' }}</div>
                </div>
                <div class="user-avatar" data-bs-toggle="modal" data-bs-target="#adminProfileModal">
                    {{ substr(Auth::guard('admin')->user()->name ?? 'A', 0, 1) }}
                </div>
            </div>
        </div>
    </header>

    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="logo-container justify-content-between">
            <a class="logo-link" href="{{ route('admin.dashboard') }}" id="dashboardLink">
                <!--<img src="{{ asset('logo/MORVOSKI-logo.png') }}" alt="{{ __('messages.site_logo_alt') }}">-->
                <span class="logo-text">Morovski</span>
            </a>
            <button class="close-sidebar border-0 bg-transparent text-white fs-2">
                <i class="bi bi-x"></i>
            </button>
        </div>

        <div class="sidebar-nav">
            <a href="{{ route('admin.dashboard') }}"
                class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="nav-icon bi bi-speedometer2"></i>
                <span class="nav-label">{{ __('admin.dashboard') }}</span>
            </a>

            <a href="{{ route('admin.users.index') }}"
                class="nav-item {{ request()->routeIs('admin.users.*') || request()->routeIs('admin.business-users.*') || request()->routeIs('admin.rejected.*') || request()->routeIs('admin.standard_users.*') ? 'active' : '' }}">
                <i class="nav-icon bi bi-person"></i>
                <span class="nav-label">{{ __('admin.user_management') }}</span>
            </a>

            <!-- Warehouse Management (Super Admin only) -->
            <a href="{{ route('admin.warehouses.index') }}"
                class="nav-item {{ request()->routeIs('admin.warehouses.*') ? 'active' : '' }}">
                <i class="nav-icon bi bi-building"></i>
                <span class="nav-label">Warehouse Management</span>
            </a>

            <a href="{{ route('admin.staff.index') }}"
                class="nav-item {{ request()->routeIs('admin.staff.*') ? 'active' : '' }}">
                <i class="nav-icon bi bi-person-badge"></i>
                <span class="nav-label">Staff Management</span>
            </a>


            <a href="{{ route('admin.categories.index') }}"
                class="nav-item {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                <i class="nav-icon bi bi-tags"></i>
                <span class="nav-label">{{ __('admin.category_management') }}</span>
            </a>

            <a href="{{ route('admin.items.index') }}"
                class="nav-item {{ request()->routeIs('admin.items.*') ? 'active' : '' }}">
                <i class="nav-icon bi bi-bag"></i>
                <span class="nav-label">Items Management</span>
            </a>

            <a href="{{ route('admin.orders.index') }}"
                class="nav-item {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                <i class="nav-icon bi bi-cart-check"></i>
                <span class="nav-label">Orders</span>
            </a>
            <a href="{{ route('admin.coupons.index') }}"
                class="nav-item {{ request()->routeIs('admin.coupons.*') ? 'active' : '' }}">

                <i class="nav-icon bi bi-ticket-perforated"></i>

                <span class="nav-label">Coupons</span>
            </a>
            <a href="{{ route('admin.replacement_requests.index') }}"
                class="nav-item {{ request()->routeIs('admin.replacement_requests.*') ? 'active' : '' }}">
                <i class="nav-icon bi bi-arrow-repeat"></i>
                <span class="nav-label">Replacement Requests</span>
            </a>


            <a href="{{ route('admin.support-tickets.index') }}"
                class="nav-item {{ request()->routeIs('admin.support-tickets.*') ? 'active' : '' }}">
                <i class="nav-icon bi bi-headset"></i>
                <span class="nav-label">Support Tickets</span>
            </a>
            <a href="{{ route('admin.contact_requests.index') }}"
                class="nav-item {{ request()->routeIs('admin.contact_requests.*') ? 'active' : '' }}">
                <i class="nav-icon bi bi-envelope-paper"></i>
                <span class="nav-label">Contact Requests</span>
            </a>
            <a href="{{ route('admin.contact-settings.index') }}"
                class="nav-item {{ request()->routeIs('admin.contact-settings.*') ? 'active' : '' }}">
                <i class="nav-icon bi bi-person-lines-fill"></i>
                <span class="nav-label">Contact Settings</span>
            </a>

            <!--{{-- <a href="{{ route('admin.blogs.index') }}"-->
            <!--    class="nav-item {{ request()->routeIs('admin.blogs.*') ? 'active' : '' }}">-->
            <!--    <i class="nav-icon bi bi-journal-text"></i>-->
            <!--    <span class="nav-label">{{ __('admin.blogs') }}</span>-->
            <!--</a> --}}-->
            <a href="{{ route('admin.header.index') }}"
                class="nav-item {{ request()->routeIs('admin.header.*') ? 'active' : '' }}">
                <i class="nav-icon bi bi-list"></i>
                <span class="nav-label">{{ __('admin.menu') }}</span>
            </a>

            <a href="{{ route('admin.faq.index') }}"
                class="nav-item {{ request()->routeIs('admin.faq.*') ? 'active' : '' }}">
                <i class="nav-icon bi bi-question-circle"></i>
                <span class="nav-label">FAQ</span>
            </a>
            <a href="{{ route('admin.pages.index') }}"
                class="nav-item {{ request()->routeIs('admin.pages.*') ? 'active' : '' }}">
                <i class="nav-icon bi bi-file-earmark-text"></i>
                <span class="nav-label">{{ __('admin.cms_management') }}</span>
            </a>
            <a href="{{ route('admin.footer.index') }}"
                class="nav-item {{ request()->routeIs('admin.footer.*') ? 'active' : '' }}">
                <i class="nav-icon bi bi-layout-text-window-reverse"></i>
                <span class="nav-label">Footer Settings</span>
            </a>
            <a href="{{ route('admin.deleted-users.index') }}"
                class="nav-item {{ request()->routeIs('admin.deleted-users.*') ? 'active' : '' }}">
                <i class="nav-icon bi bi-person-x"></i>
                <span class="nav-label">Deleted Users</span>
            </a>

            <div class="nav-divider"></div>

            <form id="logout-form" method="POST" action="{{ route('admin.logout') }}" class="nav-item">
                @csrf
                <button type="button" class="text-danger btn btn-link nav-link" onclick="confirmLogout()">
                    <i class="nav-icon bi bi-box-arrow-right"></i>
                    <span class="nav-label">{{ __('admin.logout') }}</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content" id="mainContent">
        <div class="content-card fade-in">
            <div class="fade-in" style="animation-delay: 0.1s">
                @yield('content')
            </div>
        </div>
    </main>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Summernote JS -->
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

    {{-- CKE editor --}}
    @stack('scripts')
    <script src="https://cdn.ckeditor.com/ckeditor5/41.3.1/classic/ckeditor.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Toggle sidebar on hamburger click
        document.getElementById('menuToggle').addEventListener('click', function() {
            this.classList.toggle('active');
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const overlay = document.getElementById('sidebarOverlay');

            if (window.innerWidth < 993) {
                sidebar.classList.toggle('active');
                overlay.classList.toggle('active');
                if (sidebar.classList.contains('active')) {
                    document.body.style.overflow = 'hidden';
                } else {
                    document.body.style.overflow = 'auto';
                }
            } else {
                sidebar.classList.toggle('hidden');
                mainContent.classList.toggle('full-width');
            }
        });

        document.getElementById('sidebarOverlay').addEventListener('click', function() {
            if (window.innerWidth < 993) {
                document.getElementById('menuToggle').classList.remove('active');
                document.getElementById('sidebar').classList.remove('active');
                this.classList.remove('active');
                document.body.style.overflow = 'auto';
            }
        });

        window.addEventListener('resize', function() {
            const menuToggle = document.getElementById('menuToggle');
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const mainContent = document.getElementById('mainContent');

            if (window.innerWidth >= 993) {
                overlay.classList.remove('active');
                document.body.style.overflow = 'auto';
                if (sidebar.classList.contains('active')) {
                    sidebar.classList.remove('active');
                    menuToggle.classList.remove('active');
                }
            } else {
                if (sidebar.classList.contains('hidden')) {
                    sidebar.classList.remove('hidden');
                    mainContent.classList.remove('full-width');
                }
            }
        });

        const observerOptions = {
            threshold: 0.1
        };
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.stat-card, .content-card').forEach(el => {
            observer.observe(el);
        });
        document.querySelectorAll('.has-submenu > .nav-link').forEach(item => {
            item.addEventListener('click', function() {
                this.parentElement.classList.toggle('open');
            });
        });

        document.querySelectorAll('.has-submenu > .sidebar-link').forEach(link => {
            link.addEventListener('click', () => {
                link.parentElement.classList.toggle('open');
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            if (!sidebar) return;
            const activeItem = sidebar.querySelector('.nav-item.active');
            if (activeItem) {
                try {
                    activeItem.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                } catch (e) {
                    const offsetTop = activeItem.offsetTop - (sidebar.clientHeight / 2) + (activeItem.clientHeight /
                        2);
                    sidebar.scrollTop = offsetTop;
                }
            }

            sidebar.querySelectorAll('.nav-item').forEach(item => {
                item.addEventListener('click', function() {
                    setTimeout(() => {
                        try {
                            this.scrollIntoView({
                                behavior: 'smooth',
                                block: 'nearest'
                            });
                        } catch (e) {
                            const offsetTop = this.offsetTop - (sidebar.clientHeight / 2) +
                                (this.clientHeight / 2);
                            sidebar.scrollTop = offsetTop;
                        }
                    }, 120);
                });
            });
        });
    </script>

    <!-- Admin Profile Modal -->
    <div class="modal fade" id="adminProfileModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border shadow">
                <div class="modal-header text-white"
                    style="background: linear-gradient(135deg, #2a1a05 0%, #1a0f00 100%);">
                    <div class="d-flex
                    align-items-center">
                        <div
                            class="avatar-circle bg-white text-primary d-flex align-items-center justify-content-center me-3">
                            <i class="fas fa-user-cog"></i>
                        </div>
                        <div>
                            <h5 class="modal-title mb-0 fw-semibold">{{ __('admin.admin_profile') }}</h5>
                            <small class="opacity-75">{{ __('admin.update_account_info') }}</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <form method="POST" action="{{ route('admin.profile.update') }}" novalidate>
                    @csrf
                    @method('PUT')

                    <div class="modal-body p-4 bg-light">
                        <div class="alert alert-info d-flex align-items-center mb-4">
                            <i class="fas fa-info-circle me-2"></i>
                            <small>{{ __('admin.update_account_info') }}</small>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold"><i class="fas fa-user me-1"></i>
                                {{ __('admin.full_name') }}</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text"><i class="fas fa-id-card text-muted"></i></span>
                                <input type="text" class="form-control" name="name"
                                    value="{{ Auth::guard('admin')->user()->name }}" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold"><i class="fas fa-envelope me-1"></i>
                                {{ __('admin.email') }}</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text"><i class="fas fa-at text-muted"></i></span>
                                <input type="email" class="form-control" name="email"
                                    value="{{ Auth::guard('admin')->user()->email }}" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold"><i class="fas fa-key me-1"></i>
                                {{ __('admin.new_password') }}</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text"><i class="fas fa-lock text-muted"></i></span>
                                <input type="password" class="form-control" name="password" id="passwordField"
                                    placeholder="{{ __('admin.leave_blank') }}">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fas fa-eye-slash"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-user me-1"></i> Account Name
                            </label>
                            <input type="text" class="form-control form-control-lg" name="account_name"
                                value="{{ Auth::guard('admin')->user()->account_name }}">
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-university me-1"></i> Bank Name
                            </label>
                            <input type="text" class="form-control form-control-lg" name="bank_name"
                                value="{{ Auth::guard('admin')->user()->bank_name }}">
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-credit-card me-1"></i> Account Number
                            </label>
                            <input type="text" class="form-control form-control-lg" name="account_number"
                                value="{{ Auth::guard('admin')->user()->account_number }}">
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-code me-1"></i> IFSC Code
                            </label>
                            <input type="text" class="form-control form-control-lg" name="ifsc_code"
                                value="{{ Auth::guard('admin')->user()->ifsc_code }}">
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-map-marker-alt me-1"></i> Branch Name
                            </label>
                            <input type="text" class="form-control form-control-lg" name="branch_name"
                                value="{{ Auth::guard('admin')->user()->branch_name }}">
                        </div>
                    </div>

                    <div class="modal-footer bg-white border-top">
                        <button type="button" class="btn btn-outline-secondary px-4"
                            data-bs-dismiss="modal">{{ __('admin.cancel') }}</button>
                        <button style="background: linear-gradient(135deg, #2a1a05 0%, #1a0f00 100%);" type="submit"
                            class="btn btn-primary px-4">{{ __('admin.update_profile') }}</button>
                    </div>


                </form>
            </div>
        </div>
    </div>

    <style>
        .avatar-circle {
            width: 46px;
            height: 46px;
            border-radius: 50%;
        }

        .modal-content {
            border-radius: 12px;
        }

        .input-group-text {
            background-color: #f8f9fa;
        }

        .form-control:focus {
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, .25);
        }
    </style>

    <script>
        // Global search (unchanged, keep your existing implementation)
        (function() {
            let searchInput = document.getElementById('adminSearchInput');
            let resultsEl = document.getElementById('adminSearchResults');
            let searchTimeout = null;

            function renderSection(title, items, renderItemFn) {
                if (!items || items.length === 0) return '';
                let html = '<div class="mb-3">';
                html += '<h6 class="fw-semibold">' + title + '</h6>';
                html += '<div class="list-group">';
                items.forEach(it => {
                    html += renderItemFn(it);
                });
                html += '</div></div>';
                return html;
            }

            function renderUserItem(u) {
                return `<a class="list-group-item list-group-item-action" href="${u.url}">
                    <div class="d-flex justify-content-between">
                        <h6 class="mb-1">${u.name || 'Untitled'}</h6>
                        <small class="text-muted">User</small>
                    </div>
                    <p class="mb-0">${u.email || ''}</p>
                </a>`;
            }

            function renderItem(it) {
                function getImageUrl(path) {
                    if (!path) return '';
                    if (path.startsWith('http')) return path;
                    return window.location.origin + '/storage/' + path.replace(/^\//, '');
                }
                const imgUrl = getImageUrl(it.image) || (window.location.origin + '/logo/SportGemsLogo.png');
                const price = it.price ? `<span class="badge bg-danger ms-2">${it.price}</span>` : '';
                return `<a class="list-group-item list-group-item-action py-2" href="${it.url}" style="border:none;">
                    <div class="d-flex align-items-start gap-3">
                        <div style="flex:0 0 72px;"><img src="${imgUrl}" alt="${(it.title||'Item')}" style="width:72px;height:72px;object-fit:cover;border-radius:8px;border:1px solid rgba(0,0,0,0.06);"></div>
                        <div style="flex:1; min-width:0;">
                            <div class="d-flex justify-content-between align-items-start">
                                <h6 class="mb-1 text-truncate" style="max-width:360px;">${it.title || 'Untitled'}</h6>
                                ${price}
                            </div>
                            <div class="text-muted small" style="max-width:420px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">${it.description ? it.description : ''}</div>
                        </div>
                    </div>
                </a>`;
            }

            function renderCategory(c) {
                return `<a class="list-group-item list-group-item-action" href="${c.url}">
                    <h6 class="mb-1">${c.name || ''}</h6>
                    <small class="text-muted">Category</small>
                </a>`;
            }

            function doSearch(q) {
                if (!q || q.trim() === '') {
                    resultsEl.innerHTML = '';
                    return;
                }

                $.getJSON("{{ route('admin.search') }}", {
                    q: q
                }, function(data) {
                    let out = '';
                    out += renderSection('Users', data.users || [], renderUserItem);
                    out += renderSection('Items', data.items || [], renderItem);
                    out += renderSection('Categories', data.categories || [], renderCategory);
                    if (!out) out = '<div class="text-muted p-2">No results found</div>';
                    resultsEl.innerHTML = out;
                }).fail(function() {
                    resultsEl.innerHTML = '<div class="text-danger p-2">Search failed. Try again.</div>';
                });
            }

            if (searchInput) {
                searchInput.addEventListener('input', function(e) {
                    document.getElementById('adminSearchResults').style.display = 'flex';
                    const v = e.target.value;
                    if (searchTimeout) clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => doSearch(v), 250);
                });
                searchInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        if (searchTimeout) clearTimeout(searchTimeout);
                        doSearch(e.target.value);
                    }
                });
            }

            document.addEventListener('click', function(e) {
                document.getElementById('adminSearchResults').style.display = 'none';
                if (!searchInput || !resultsEl) return;
                if (!searchInput.contains(e.target)) resultsEl.innerHTML = '';
            });
        })();

        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.getElementById('togglePassword');
            const passwordField = document.getElementById('passwordField');
            if (togglePassword && passwordField) {
                togglePassword.addEventListener('click', function() {
                    const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordField.setAttribute('type', type);
                    this.innerHTML = type === 'password' ? '<i class="fas fa-eye-slash"></i>' :
                        '<i class="fas fa-eye"></i>';
                });
            }
            const forms = document.querySelectorAll('.needs-validation');
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        });

        // Logout confirmation with translated strings
        function confirmLogout() {
            Swal.fire({
                title: '{{ __('admin.confirm_logout') }}',
                text: '{{ __('admin.logout_question') }}',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '{{ __('admin.yes_logout') }}',
                cancelButtonText: '{{ __('admin.no_stay') }}',
                background: '#ffffff',
                color: '#065f56',
                confirmButtonColor: '#065f56',
                cancelButtonColor: '#808080',
                iconColor: '#065f56',
                customClass: {
                    popup: 'rounded-xl shadow-lg',
                    confirmButton: 'px-4 py-2',
                    cancelButton: 'px-4 py-2'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('logout-form').submit();
                }
            });
        }

        // ── Admin Notifications ──
        class AdminNotifications {
            constructor() {
                this.notificationBtn = document.getElementById('notificationBtn');
                this.notificationDropdown = document.getElementById('notificationDropdown');
                this.notificationBadge = document.getElementById('notificationBadge');
                this.notificationList = document.getElementById('notificationList');
                this.markAllReadBtn = document.getElementById('markAllReadBtn');

                this.init();
            }

            init() {
                // Toggle dropdown
                this.notificationBtn.addEventListener('click', () => this.toggleDropdown());

                // Close dropdown when clicking outside
                document.addEventListener('click', (e) => {
                    if (!e.target.closest('#notificationBtn') && !e.target.closest('#notificationDropdown')) {
                        this.closeDropdown();
                    }
                });

                // Mark all as read
                this.markAllReadBtn.addEventListener('click', () => this.markAllAsRead());

                // Click handler for notification items
                this.notificationList.addEventListener('click', (e) => {
                    if (e.target.closest('.notification-item-action-btn')) {
                        return;
                    }

                    const item = e.target.closest('.notification-item');
                    if (item) {
                        const notifId = item.dataset.id;
                        this.goToNotification(notifId);
                    }
                });

                // Load notifications
                this.loadNotifications();

                // Refresh notifications every 30 seconds
                setInterval(() => this.loadNotifications(), 30000);
            }

            toggleDropdown() {
                this.notificationDropdown.classList.toggle('show');
            }

            closeDropdown() {
                this.notificationDropdown.classList.remove('show');
            }

            loadNotifications() {
                fetch('{{ route('admin.notifications.unread_list') }}')
                    .then(response => response.json())
                    .then(data => {
                        this.renderNotifications(data.notifications);
                        this.updateBadge(data.unread_count);
                    })
                    .catch(error => console.error('Error loading notifications:', error));
            }

            renderNotifications(notifications) {
                if (!notifications || notifications.length === 0) {
                    this.notificationList.innerHTML = `
                        <div class="notification-empty">
                            <div><i class="bi bi-bell-slash"></i></div>
                            <small>No notifications</small>
                        </div>
                    `;
                    return;
                }

                const groups = this.groupNotificationsByDate(notifications);
                this.notificationList.innerHTML = groups.map(group => `
                    <div class="notification-group">
                        <div class="notification-group-header">
                            <span>${group.label}</span>
                            <span class="notification-group-count">${group.items.length}</span>
                        </div>
                        ${group.items.map(notif => this.renderNotificationItem(notif)).join('')}
                    </div>
                `).join('');

            }

            groupNotificationsByDate(notifications) {
                const today = new Date();
                today.setHours(0, 0, 0, 0);

                const yesterday = new Date(today);
                yesterday.setDate(today.getDate() - 1);

                const groups = [{
                        label: 'Today',
                        items: []
                    },
                    {
                        label: 'Yesterday',
                        items: []
                    },
                    {
                        label: 'Earlier',
                        items: []
                    }
                ];

                notifications.forEach(notif => {
                    const createdAt = new Date(notif.created_at);
                    if (createdAt >= today) {
                        groups[0].items.push(notif);
                    } else if (createdAt >= yesterday) {
                        groups[1].items.push(notif);
                    } else {
                        groups[2].items.push(notif);
                    }
                });

                return groups.filter(group => group.items.length > 0);
            }

            renderNotificationItem(notif) {
                return `
                    <div class="notification-item ${notif.is_read ? '' : 'unread'}" data-id="${notif.id}">
                        <div class="notification-item-icon ${notif.priority}-priority">
                            <i class="${this.getIconForType(notif.type)}"></i>
                        </div>
                        <div class="notification-item-content">
                            <p class="notification-item-title">${notif.title}</p>
                            <p class="notification-item-message">${notif.message}</p>
                            <p class="notification-item-time">${this.formatTime(notif.created_at)}</p>
                        </div>
                        <div class="notification-item-actions">
                            <button class="notification-item-action-btn"
                                    onclick="adminNotifications.markAsRead('${notif.id}')"
                                    title="Mark as read">
                                <i class="bi bi-check-circle"></i>
                            </button>
                            <button class="notification-item-action-btn"
                                    onclick="adminNotifications.deleteNotification('${notif.id}')"
                                    title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                `;
            }

            updateBadge(count) {
                if (count > 0) {
                    this.notificationBadge.textContent = count > 9 ? '9+' : count;
                    this.notificationBadge.style.display = 'flex';
                } else {
                    this.notificationBadge.style.display = 'none';
                }
            }

            markAsRead(notificationId) {

                fetch(`{{ url('admin/notifications') }}/${notificationId}/read`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(() => this.loadNotifications())
                    .catch(error => console.error('Error marking notification as read:', error));
            }

            markAllAsRead() {
                fetch('{{ route('admin.notifications.mark_all_read') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(() => {
                        this.loadNotifications();
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'All notifications marked as read',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    })
                    .catch(error => console.error('Error marking all as read:', error));
            }

            deleteNotification(notificationId) {
                Swal.fire({
                    title: 'Delete Notification?',
                    text: 'This action cannot be undone',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Delete',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#ef4444'
                }).then(result => {
                    if (result.isConfirmed) {
                        fetch(`{{ url('admin/notifications') }}/${notificationId}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                        ?.content,
                                    'Content-Type': 'application/json'
                                }
                            })
                            .then(() => {
                                this.loadNotifications();
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted',
                                    text: 'Notification deleted',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            })
                            .catch(error => console.error('Error deleting notification:', error));
                    }
                });
            }

            goToNotification(notificationId) {
                window.location.href = `{{ url('admin/notifications') }}/${notificationId}`;
            }

            getIconForType(type) {
                const icons = {
                    'new_order': 'bi-shopping-bag',
                    'new_user': 'bi-person-plus',
                    'new_b2c_registration': 'bi-person-check',
                    'report': 'bi-flag',
                    'ticket': 'bi-ticket-detailed',
                    'contact': 'bi-envelope',
                    'payment': 'bi-credit-card',
                    'default': 'bi-bell'
                };
                return icons[type] || icons.default;
            }

            formatTime(dateString) {
                const date = new Date(dateString);
                const now = new Date();
                const diffMs = now - date;
                const diffMins = Math.floor(diffMs / 60000);
                const diffHours = Math.floor(diffMs / 3600000);
                const diffDays = Math.floor(diffMs / 86400000);

                if (diffMins < 1) return 'Just now';
                if (diffMins < 60) return `${diffMins}m ago`;
                if (diffHours < 24) return `${diffHours}h ago`;
                if (diffDays < 7) return `${diffDays}d ago`;

                return date.toLocaleDateString();
            }
        }

        // Initialize notifications
        let adminNotifications;
        document.addEventListener('DOMContentLoaded', () => {
            adminNotifications = new AdminNotifications();
        });

        // Open sidebar and overlay
        document.querySelector('.btn-collapse').addEventListener('click', () => {
            document.querySelector('.sidebar').classList.add('active');
            document.querySelector('.sidebar-overlay').classList.add('active');
        });

        // Close sidebar and overlay (via close button)
        document.querySelector('.close-sidebar').addEventListener('click', () => {
            document.querySelector('.sidebar').classList.remove('active');
            document.querySelector('.sidebar-overlay').classList.remove('active');
        });

        // Close sidebar and overlay (via clicking outside on the overlay)
        document.querySelector('.sidebar-overlay').addEventListener('click', () => {
            document.querySelector('.sidebar').classList.remove('active');
            document.querySelector('.sidebar-overlay').classList.remove('active');
        });
    </script>
</body>

</html>
