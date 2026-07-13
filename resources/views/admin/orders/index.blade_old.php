@extends('layouts.admin')

@section('content')

    <link
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=DM+Sans:wght@300;400;500;600&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --bronze: #A28051;
            --bronze-dark: #8B6B40;
            --bronze-light: #C4A06E;
            --bronze-pale: #F5EFE6;
            --bronze-tint: #EDE3D4;
            --charcoal: #1E1E1E;
            --slate: #3D3D3D;
            --muted: #7A7A7A;
            --border: #DDD4C4;
            --white: #FFFFFF;
            --surface: #FAFAF8;
            --danger: #C0392B;
            --success-text: #2D6A4F;
            --success-bg: #EAF4EE;
            --warn-text: #7D5A00;
            --warn-bg: #FDF5E0;
            --info-text: #1A4D6E;
            --info-bg: #E8F3FA;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: #F2EDE4;
            color: var(--charcoal);
        }

        /* ── Page Header ── */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            padding: 2rem 0 1.75rem;
            border-bottom: 1px solid var(--border);
            margin-bottom: 2rem;
        }

        .page-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 2.1rem;
            font-weight: 700;
            color: var(--charcoal);
            letter-spacing: -0.5px;
            margin: 0 0 4px;
            line-height: 1;
        }

        .page-subtitle {
            font-size: 0.82rem;
            color: var(--muted);
            font-weight: 400;
            letter-spacing: 0.6px;
            text-transform: uppercase;
            margin: 0;
        }

        /* ── Buttons ── */
        .btn-bronze {
            background: var(--bronze);
            color: #fff;
            border: none;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.8rem;
            font-weight: 500;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            padding: 0.55rem 1.25rem;
            border-radius: 3px;
            cursor: pointer;
            transition: background 0.2s ease, box-shadow 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            text-decoration: none;
        }

        .btn-bronze:hover {
            background: var(--bronze-dark);
            box-shadow: 0 2px 12px rgba(162, 128, 81, 0.35);
            color: #fff;
        }

        .btn-bronze-outline {
            background: transparent;
            color: var(--bronze);
            border: 1.5px solid var(--bronze);
            font-family: 'DM Sans', sans-serif;
            font-size: 0.8rem;
            font-weight: 500;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            padding: 0.5rem 1.2rem;
            border-radius: 3px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            text-decoration: none;
        }

        .btn-bronze-outline:hover {
            background: var(--bronze);
            color: #fff;
        }

        .btn-dispatch {
            background: var(--bronze);
            color: #fff;
            border: none;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.75rem;
            font-weight: 500;
            letter-spacing: 0.6px;
            text-transform: uppercase;
            padding: 0.42rem 1rem;
            border-radius: 3px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            white-space: nowrap;
        }

        .btn-dispatch:hover:not(:disabled) {
            background: var(--bronze-dark);
            box-shadow: 0 2px 10px rgba(162, 128, 81, 0.3);
        }

        .btn-dispatch:disabled {
            cursor: not-allowed;
            opacity: 0.55;
        }

        .btn-link-bronze {
            background: none;
            border: none;
            color: var(--bronze);
            font-size: 0.78rem;
            font-weight: 500;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            cursor: pointer;
            padding: 0;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            transition: color 0.2s;
            text-decoration: none;
        }

        .btn-link-bronze:hover {
            color: var(--bronze-dark);
        }

        /* ── Dropdown ── */
        .dropdown-menu {
            border: 1px solid var(--border);
            border-radius: 4px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 0.35rem 0;
            font-family: 'DM Sans', sans-serif;
        }

        .dropdown-item {
            font-size: 0.82rem;
            color: var(--slate);
            padding: 0.45rem 1.1rem;
            letter-spacing: 0.2px;
        }

        .dropdown-item:hover {
            background: var(--bronze-pale);
            color: var(--bronze-dark);
        }

        /* ── Order Card ── */
        .order-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 6px;
            overflow: hidden;
            margin-bottom: 1.5rem;
            transition: box-shadow 0.25s ease;
        }

        .order-card:hover {
            box-shadow: 0 4px 24px rgba(162, 128, 81, 0.13);
        }

        /* Card Header */
        .order-card-header {
            padding: 1.1rem 1.5rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--white);
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .order-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .order-number {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--charcoal);
            letter-spacing: -0.3px;
            margin: 0 0 2px;
        }

        .order-date {
            font-size: 0.75rem;
            color: var(--muted);
            font-weight: 400;
        }

        .order-right {
            display: flex;
            align-items: center;
            gap: 2.5rem;
        }

        .meta-block {
            text-align: right;
        }

        .meta-label {
            font-size: 0.68rem;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            color: var(--muted);
            display: block;
            margin-bottom: 2px;
        }

        .meta-value {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--charcoal);
        }

        /* Status Badge */
        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.28rem 0.75rem;
            border-radius: 2px;
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.9px;
            text-transform: uppercase;
        }

        .status-delivered {
            background: var(--success-bg);
            color: var(--success-text);
        }

        .status-processing {
            background: var(--warn-bg);
            color: var(--warn-text);
        }

        .status-pending,
        .status-default {
            background: #F0F0F0;
            color: #555;
        }

        .status-dispatched {
            background: var(--info-bg);
            color: var(--info-text);
        }

        /* Summary Strip */
        .summary-strip {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            background: var(--surface);
            border-bottom: 1px solid var(--border);
        }

        .summary-cell {
            padding: 0.9rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.85rem;
            border-right: 1px solid var(--border);
        }

        .summary-cell:last-child {
            border-right: none;
        }

        .summary-icon {
            width: 34px;
            height: 34px;
            border-radius: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.95rem;
            flex-shrink: 0;
        }

        .icon-total {
            background: rgba(162, 128, 81, 0.12);
            color: var(--bronze);
        }

        .icon-paid {
            background: var(--success-bg);
            color: var(--success-text);
        }

        .icon-due {
            background: var(--warn-bg);
            color: var(--warn-text);
        }

        .summary-figure-label {
            font-size: 0.67rem;
            letter-spacing: 0.9px;
            text-transform: uppercase;
            color: var(--muted);
            display: block;
            margin-bottom: 2px;
        }

        .summary-figure {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.15rem;
            font-weight: 700;
            line-height: 1;
            color: var(--charcoal);
        }

        .figure-paid {
            color: var(--success-text);
        }

        .figure-due {
            color: #A0740A;
        }

        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.82rem;
        }

        .items-table thead tr {
            background: var(--bronze-pale);
            border-bottom: 1px solid var(--border);
        }

        .items-table th {
            padding: 0.6rem 1.2rem;
            font-size: 0.67rem;
            font-weight: 600;
            letter-spacing: 0.9px;
            text-transform: uppercase;
            color: var(--bronze-dark);
            border: none;
            text-align: left;
        }

        .items-table th.text-center {
            text-align: center;
        }

        .items-table td {
            padding: 0.75rem 1.2rem;
            border-bottom: 1px solid #F0EAE0;
            vertical-align: middle;
            color: var(--slate);
        }

        .items-table td.text-center {
            text-align: center;
        }

        .items-table tbody tr:last-child td {
            border-bottom: none;
        }

        .items-table tbody tr:hover {
            background: #FDFAF5;
        }

        .product-cell {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .product-thumb {
            width: 38px;
            height: 38px;
            border-radius: 3px;
            background: var(--bronze-pale);
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            flex-shrink: 0;
            color: var(--bronze);
            font-size: 1rem;
        }

        .product-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-name {
            font-weight: 600;
            color: var(--charcoal);
            font-size: 0.83rem;
            margin-bottom: 1px;
        }

        .product-sku {
            font-size: 0.7rem;
            color: var(--muted);
            letter-spacing: 0.4px;
        }

        /* Qty Badges */
        .qty-badge {
            display: inline-block;
            padding: 0.22rem 0.65rem;
            border-radius: 2px;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.3px;
        }

        .qty-remaining {
            background: var(--warn-bg);
            color: var(--warn-text);
        }

        .qty-done {
            background: var(--success-bg);
            color: var(--success-text);
        }

        .qty-stock {
            background: var(--info-bg);
            color: var(--info-text);
        }

        .qty-nostock {
            background: #FDECEA;
            color: var(--danger);
        }

        /* Dispatch Input */
        .dispatch-input {
            width: 76px;
            height: 32px;
            border: 1.5px solid var(--border);
            border-radius: 3px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.8rem;
            color: var(--charcoal);
            padding: 0 0.5rem;
            background: var(--white);
            transition: border-color 0.2s;
            outline: none;
        }

        .dispatch-input:focus {
            border-color: var(--bronze);
        }

        .dispatch-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            justify-content: center;
        }

        /* Status Tags (action column) */
        .tag-dispatched {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.7px;
            text-transform: uppercase;
            color: var(--success-text);
            background: var(--success-bg);
            padding: 0.28rem 0.75rem;
            border-radius: 2px;
        }

        .tag-pending {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.7px;
            text-transform: uppercase;
            color: var(--warning-text, #944100);
            background: var(--warning-bg, #FFF4E5);
            padding: 0.28rem 0.75rem;
            border-radius: 2px;
        }

        .tag-nostock {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.7px;
            text-transform: uppercase;
            color: var(--danger);
            background: #FDECEA;
            padding: 0.28rem 0.75rem;
            border-radius: 2px;
        }

        /* Card Footer */
        .order-card-footer {
            padding: 0.75rem 1.5rem;
            border-top: 1px solid var(--border);
            background: var(--surface);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .footer-meta {
            font-size: 0.75rem;
            color: var(--muted);
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 6px;
        }

        .empty-state-icon {
            font-size: 3rem;
            color: var(--border);
            margin-bottom: 1rem;
        }

        .empty-state h4 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--charcoal);
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            font-size: 0.83rem;
            color: var(--muted);
            margin: 0;
        }

        /* Loading Overlay */
        #loadingOverlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(30, 30, 30, 0.6);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }

        .loading-box {
            background: var(--white);
            padding: 2rem 2.5rem;
            border-radius: 5px;
            text-align: center;
            border-top: 3px solid var(--bronze);
        }

        .loading-box .spinner {
            width: 32px;
            height: 32px;
            border: 3px solid var(--bronze-tint);
            border-top-color: var(--bronze);
            border-radius: 50%;
            animation: spin 0.75s linear infinite;
            margin: 0 auto 1rem;
        }

        .loading-box h6 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--charcoal);
            margin: 0 0 4px;
        }

        .loading-box p {
            font-size: 0.78rem;
            color: var(--muted);
            margin: 0;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Toast */
        .toast-notification {
            position: fixed;
            top: 1.25rem;
            right: 1.25rem;
            min-width: 300px;
            z-index: 10000;
            background: var(--white);
            border-radius: 4px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.14);
            padding: 0.9rem 1.1rem;
            display: flex;
            align-items: center;
            gap: 0.7rem;
            font-size: 0.82rem;
            font-family: 'DM Sans', sans-serif;
            animation: slideInRight 0.3s ease;
            border-left: 3px solid var(--bronze);
        }

        .toast-notification.toast-error {
            border-left-color: var(--danger);
        }

        .toast-notification.toast-success {
            border-left-color: var(--success-text);
        }

        .toast-icon {
            font-size: 1rem;
            flex-shrink: 0;
        }

        .toast-msg {
            flex: 1;
            color: var(--slate);
        }

        .toast-close {
            background: none;
            border: none;
            color: var(--muted);
            cursor: pointer;
            font-size: 0.85rem;
            padding: 0;
            line-height: 1;
        }

        @keyframes slideInRight {
            from {
                transform: translateX(20px);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* Pagination */
        .pagination .page-link {
            color: var(--bronze);
            border-color: var(--border);
            font-size: 0.8rem;
            font-family: 'DM Sans', sans-serif;
            padding: 0.4rem 0.8rem;
        }

        .pagination .page-item.active .page-link {
            background: var(--bronze);
            border-color: var(--bronze);
            color: #fff;
        }

        .pagination .page-link:hover {
            background: var(--bronze-pale);
            border-color: var(--bronze);
            color: var(--bronze-dark);
        }

        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .summary-strip {
                grid-template-columns: 1fr;
            }

            .summary-cell {
                border-right: none;
                border-bottom: 1px solid var(--border);
            }

            .order-right {
                gap: 1.25rem;
            }

            .order-card-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>

    <div class="container-fluid px-4">

        {{-- Page Header --}}
        <div class="page-header">
            <div>
                <h1 class="page-title">Order Management</h1>
                <p class="page-subtitle">Dispatch Centre &nbsp;·&nbsp; Customer Orders</p>
            </div>
            <div style="display:flex; gap:0.6rem; align-items:center;">
                <form method="GET" action="{{ url()->current() }}" style="display:flex; gap:0.5rem; align-items:center;">
                    <select name="account_type" onchange="this.form.submit()"
                        style="height:36px; padding:0 10px; border:1px solid #DDD4C4; border-radius:4px; background:#fff; color:var(--charcoal);">
                        <option value="">All Accounts</option>
                        <option value="b2c" {{ request('account_type') === 'b2c' ? 'selected' : '' }}>B2C</option>
                        <option value="b2b" {{ request('account_type') === 'b2b' ? 'selected' : '' }}>B2B</option>
                    </select>
                    <noscript>
                        <button type="submit" class="btn-bronze-outline">Filter</button>
                    </noscript>
                </form>
                <button class="btn-bronze">
                    <i class="bi bi-funnel-fill"></i> Filter
                </button>
            </div>
        </div>

        {{-- Orders --}}
        @forelse ($orders as $order)
            @php
                $statusClass = match ($order->order_status) {
                    'delivered' => 'status-delivered',
                    'processing' => 'status-processing',
                    'dispatched' => 'status-dispatched',
                    default => 'status-default',
                };
            @endphp

            <div class="order-card">

                {{-- Card Header --}}
                <div class="order-card-header">
                    <div class="order-left">
                        <span class="status-pill {{ $statusClass }}">
                            {{ ucfirst($order->order_status) }}
                        </span>
                        <div>
                            <div class="order-number">Order #{{ $order->order_number }}</div>
                            <div class="order-date">Placed {{ $order->created_at->format('d M Y, h:i A') }}</div>
                        </div>
                    </div>
                    <div class="order-right">
                        <div class="meta-block">
                            <span class="meta-label">Customer</span>

                            <span class="meta-value">
                                {{ $order->user->full_name }}
                            </span>
                            </br>
                            <small>
                                {{ strtoupper($order->user->account_type) }}
                                |
                                {{ $order->user->account_type === 'b2b' ? 'Proforma Invoice' : 'Invoice' }}
                            </small>
                        </div>
                        <div class="meta-block">
                            <span class="meta-label">Contact</span>
                            <span class="meta-value">{{ $order->user->email ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Summary Strip --}}
                <div class="summary-strip">
                    <div class="summary-cell">
                        <div class="summary-icon icon-total">
                            <i class="bi bi-currency-rupee"></i>
                        </div>
                        <div>
                            <span class="summary-figure-label">Total Amount</span>
                            <span class="summary-figure">₹{{ number_format($order->total_amount, 2) }}</span>
                        </div>
                    </div>
                    <div class="summary-cell">
                        <div class="summary-icon icon-paid">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <div>
                            <span class="summary-figure-label">Paid Amount</span>
                            <span class="summary-figure figure-paid">₹{{ number_format($order->paid_amount, 2) }}</span>
                        </div>
                    </div>
                    <div class="summary-cell">
                        <div class="summary-icon icon-due">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <div>
                            <span class="summary-figure-label">Due Amount</span>
                            <span class="summary-figure figure-due">₹{{ number_format($order->due_amount, 2) }}</span>
                        </div>
                    </div>
                </div>

                {{-- Items Table --}}
                <div style="overflow-x:auto;">
                    <table class="items-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th class="text-center">Ordered</th>
                                <th class="text-center">Dispatched</th>
                                <th class="text-center">Remaining (quantity)</th>
                                <th class="text-center">Stock</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->items as $item)
                                @php
                                    $remaining = $item->quantity - $item->dispatched_qty;
                                    $maxDispatch = min($remaining, $item->item->quantity);
                                    $isFullyDisp = $remaining == 0;
                                    $isOutOfStock = $item->item->quantity == 0;
                                    $hasPendingAllocation = !empty($pendingAllocations[$item->id] ?? 0);
                                @endphp
                                <tr>
                                    <td>
                                        <div class="product-cell">
                                            <div class="product-thumb">
                                                @if ($item->item->image)
                                                    <img src="{{ asset('storage/' . $item->item->image) }}"
                                                        alt="{{ $item->item->title }}">
                                                @else
                                                    <i class="bi bi-box"></i>
                                                @endif
                                            </div>
                                            <div>
                                                <div class="product-name">{{ $item->item->title }}</div>
                                                <div class="product-sku">SKU: {{ $item->item->sku ?? 'N/A' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center" style="font-weight:600; color:var(--charcoal);">
                                        {{ $item->quantity }}
                                    </td>
                                    <td class="text-center" style="color:var(--muted);">
                                        {{ $item->dispatched_qty }}
                                    </td>
                                    <td class="text-center">
                                        <span class="qty-badge {{ $remaining > 0 ? 'qty-remaining' : 'qty-done' }}">
                                            {{ $remaining }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span
                                            class="qty-badge {{ $item->item->quantity > 0 ? 'qty-stock' : 'qty-nostock' }}">
                                            {{ $item->item->quantity }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if ($isFullyDisp)
                                            <span class="tag-dispatched">
                                                <i class="bi bi-check-circle-fill"></i> Fulfilled
                                            </span>
                                        @elseif ($hasPendingAllocation)
                                            <span class="tag-pending">
                                                <i class="bi bi-clock-history"></i> Allocation Sent to Manager
                                            </span>
                                            <button class="btn-dispatch dispatch-btn" disabled style="margin-left:8px;">
                                                <i class="bi bi-clock"></i> Pending
                                            </button>
                                        @elseif ($isOutOfStock)
                                            <span class="tag-nostock">
                                                <i class="bi bi-x-circle-fill"></i> Out of Stock
                                            </span>
                                        @else
                                            <div class="dispatch-group" data-order-item-id="{{ $item->id }}"
                                                data-item-id="{{ $item->item_id }}" data-remaining="{{ $remaining }}">
                                                <div class="allocations-list">
                                                    <div class="allocation-row" data-index="0"
                                                        style="display:flex; gap:0.5rem; align-items:center;">
                                                        <select class="dispatch-warehouse"
                                                            style="height:32px; padding:0 8px; border:1.5px solid var(--border); border-radius:3px; background:#fff;">
                                                            <option value="">Select Warehouse</option>
                                                            @foreach ($warehouses as $warehouse)
                                                                @php $available = $warehouseStocks[$item->item_id][$warehouse->id] ?? 0; @endphp
                                                                <option value="{{ $warehouse->id }}"
                                                                    data-available="{{ $available }}">
                                                                    {{ $warehouse->name }}@if ($warehouse->code)
                                                                        ({{ $warehouse->code }})
                                                                    @endif — Available:
                                                                    {{ $available }}</option>
                                                            @endforeach
                                                        </select>
                                                        <button type="button" class="btn-bronze-outline add-allocation"
                                                            style="padding:6px 8px; font-size:0.85rem; font-weight:bold; margin-left:4px;">+</button>
                                                        <button type="button" class="btn-bronze-outline remove-allocation"
                                                            style="padding:6px 8px; font-size:0.85rem; font-weight:bold; margin-left:4px; display:none;">-</button>
                                                        <input type="number" class="dispatch-input allocation-qty"
                                                            placeholder="Qty" min="1" value="{{ $maxDispatch }}"
                                                            style="width:76px;" />

                                                    </div>
                                                </div>
                                                <button class="btn-dispatch dispatch-btn" data-id="{{ $item->id }}"
                                                    style="margin-left:8px;">
                                                    <i class="bi bi-truck"></i> Dispatch
                                                </button>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Card Footer --}}
                <div class="order-card-footer">
                    <span class="footer-meta">
                        <i class="bi bi-clock" style="color:var(--bronze);"></i>
                        Last updated {{ $order->updated_at->diffForHumans() }}
                    </span>
                    <a href="{{ route('admin.orders.show', $order->id) }}" class="btn-link-bronze">
                        View Details <i class="bi bi-arrow-right"></i>
                    </a>
                </div>

            </div>

            @empty
                <div class="empty-state">
                    <div class="empty-state-icon"><i class="bi bi-inbox"></i></div>
                    <h4>No Orders Found</h4>
                    <p>There are no orders to display at the moment.</p>
                </div>
            @endforelse

            {{-- Pagination --}}
            @if (method_exists($orders, 'links'))
                <div class="d-flex justify-content-center mt-4">
                    {{ $orders->links() }}
                </div>
            @endif

        </div>

        {{-- Loading Overlay --}}
        <div id="loadingOverlay">
            <div class="loading-box">
                <div class="spinner"></div>
                <h6>Processing allocation request</h6>
                <p>Please wait a moment</p>
            </div>
        </div>

        <script>
            // Allocation select change: update available and max quantity
            document.querySelectorAll('.dispatch-group').forEach(group => {
                group.addEventListener('change', function(e) {
                    if (e.target.classList.contains('dispatch-warehouse')) {
                        const select = e.target;
                        const row = select.closest('.allocation-row');
                        const available = parseInt(select.selectedOptions[0]?.dataset.available || 0);
                        const qtyInput = row.querySelector('.allocation-qty');
                        const remaining = parseInt(group.dataset.remaining || 0);
                        const maxVal = Math.min(available, remaining);
                        if (qtyInput) {
                            qtyInput.setAttribute('max', maxVal);
                            if (!qtyInput.value || parseInt(qtyInput.value) > maxVal) qtyInput.value = maxVal;
                        }
                    }
                });

                // add / remove allocation rows
                group.addEventListener('click', function(e) {
                    if (e.target.classList.contains('add-allocation')) {
                        const list = group.querySelector('.allocations-list');
                        const template = list.querySelector('.allocation-row');
                        const clone = template.cloneNode(true);
                        const whSelect = clone.querySelector('.dispatch-warehouse');
                        if (whSelect) whSelect.value = '';
                        const qty = clone.querySelector('.allocation-qty');
                        if (qty) {
                            qty.value = '';
                            qty.removeAttribute('max');
                        }
                        const removeBtn = clone.querySelector('.remove-allocation');
                        if (removeBtn) removeBtn.style.display = 'inline-block';
                        list.appendChild(clone);

                    } else if (e.target.classList.contains('remove-allocation')) {
                        const rows = group.querySelectorAll('.allocation-row');
                        if (rows.length <= 1) {
                            showToast('At least one allocation is required.', 'error');
                            return;
                        }
                        e.target.closest('.allocation-row').remove();
                    }
                });
            });

            // Dispatch button handler: gather allocations and post to server
            document.querySelectorAll('.dispatch-btn').forEach(btn => {
                btn.addEventListener('click', async function() {
                    const orderItemId = this.dataset.id;
                    const button = this;
                    const group = button.closest('.dispatch-group');
                    const remaining = parseInt(group.dataset.remaining || 0);
                    const rows = group.querySelectorAll('.allocation-row');
                    const allocations = [];
                    let total = 0;

                    for (const row of rows) {
                        const select = row.querySelector('.dispatch-warehouse');
                        const qtyInput = row.querySelector('.allocation-qty');
                        const warehouseId = select ? select.value : null;
                        const qty = qtyInput ? parseInt(qtyInput.value) : 0;
                        if (!warehouseId || !qty || qty <= 0) {
                            showToast('Please select warehouse and enter quantity for each allocation.',
                                'error');
                            return;
                        }
                        allocations.push({
                            warehouse_id: parseInt(warehouseId),
                            quantity: qty
                        });
                        total += qty;
                    }

                    if (total > remaining) {
                        showToast(`Total allocation (${total}) exceeds remaining quantity (${remaining}).`,
                            'error');
                        return;
                    }

                    const originalHTML = button.innerHTML;
                    button.disabled = true;
                    button.innerHTML =
                        '<span style="display:inline-block;width:12px;height:12px;border:2px solid rgba(255,255,255,0.4);border-top-color:#fff;border-radius:50%;animation:spin 0.7s linear infinite;vertical-align:middle;margin-right:4px;"></span> Dispatching…';
                    document.getElementById('loadingOverlay').style.display = 'flex';

                    try {
                        const resp = await fetch("{{ route('admin.dispatch.item') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                order_item_id: orderItemId,
                                allocations: allocations
                            })
                        });
                        const data = await resp.json();

                        if (data.status) {
                            showToast('Allocation request sent to warehouse manager.', 'success');

                            // Immediately disable the dispatch button and show pending state
                            const actionCell = button.closest('td');
                            actionCell.innerHTML = `
                                <span class="tag-pending">
                                    <i class="bi bi-clock-history"></i> Allocation Sent to Manager
                                </span>
                                <button class="btn-dispatch dispatch-btn" disabled style="margin-left:8px;">
                                    <i class="bi bi-clock"></i> Pending
                                </button>
                            `;

                            document.getElementById('loadingOverlay').style.display = 'none';
                            setTimeout(() => location.reload(), 2000);
                        } else {
                            showToast(data.message || 'Allocation request failed. Please try again.',
                                'error');
                            button.disabled = false;
                            button.innerHTML = originalHTML;
                            document.getElementById('loadingOverlay').style.display = 'none';
                        }
                    } catch (err) {
                        console.error(err);
                        showToast('A network error occurred. Please try again.', 'error');
                        button.disabled = false;
                        button.innerHTML = originalHTML;
                        document.getElementById('loadingOverlay').style.display = 'none';
                    }
                });
            });

            function showToast(message, type = 'success') {
                const icons = {
                    success: 'bi-check-circle-fill',
                    error: 'bi-exclamation-triangle-fill'
                };
                const colors = {
                    success: '#2D6A4F',
                    error: '#C0392B'
                };
                const t = document.createElement('div');
                t.className = `toast-notification toast-${type}`;
                t.innerHTML = `
            <i class="bi ${icons[type]} toast-icon" style="color:${colors[type]}"></i>
            <span class="toast-msg">${message}</span>
            <button class="toast-close" onclick="this.parentElement.remove()">✕</button>
        `;
                document.body.appendChild(t);
                setTimeout(() => t.remove(), 4500);
            }
        </script>

    @endsection
