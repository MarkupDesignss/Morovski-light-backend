@extends('layouts.admin')

<style>
    * {
        box-sizing: border-box;
    }

    .page-wrap {
        min-height: 100vh;
        padding: 2rem;
        background: #faf6f0;
    }

    /* ── Document Container ── */
    .document-container {
        max-width: 900px;
        margin: 0 auto;
        background: #fff;
        border: 1px solid rgba(162, 128, 81, 0.2);
        border-radius: 4px;
        box-shadow: 0 2px 20px rgba(0, 0, 0, 0.08);
        position: relative;
    }

    /* ── Document Header / Letterhead ── */
    .document-header {
        padding: 2.5rem 3rem 2rem;
        border-bottom: 3px solid #A28051;
        position: relative;
        background: linear-gradient(180deg, #fdfbf7 0%, #fff 100%);
    }

    .document-header::after {
        content: '';
        position: absolute;
        bottom: -6px;
        left: 0;
        right: 0;
        height: 1px;
        background: rgba(162, 128, 81, 0.3);
    }

    .header-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1.5rem;
    }

    .company-brand {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .company-logo {
        width: 48px;
        height: 48px;
        background: linear-gradient(135deg, #A28051, #C5A672);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 20px;
    }

    .company-name {
        font-family: Georgia, serif;
        font-size: 22px;
        font-weight: 700;
        color: #2a1a05;
        letter-spacing: 0.5px;
    }

    .company-tagline {
        font-size: 10px;
        color: #8a6a3a;
        letter-spacing: 2px;
        text-transform: uppercase;
        margin-top: 2px;
    }

    .document-title-block {
        text-align: right;
    }

    .document-title {
        font-family: Georgia, serif;
        font-size: 28px;
        font-weight: 700;
        color: #2a1a05;
        letter-spacing: 1px;
        margin: 0;
    }

    .document-subtitle {
        font-size: 11px;
        color: #8a6a3a;
        letter-spacing: 2px;
        text-transform: uppercase;
        margin-top: 4px;
    }

    .order-meta-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.5rem;
        padding-top: 1.5rem;
        border-top: 1px dashed rgba(162, 128, 81, 0.3);
    }

    .meta-item {
        text-align: center;
    }

    .meta-label {
        font-size: 9px;
        font-weight: 700;
        letter-spacing: 2px;
        text-transform: uppercase;
        color: #8a6a3a;
        margin-bottom: 4px;
    }

    .meta-value {
        font-size: 13px;
        font-weight: 600;
        color: #2a1a05;
    }

    .meta-value.highlight {
        color: #A28051;
        font-weight: 700;
    }

    /* ── Status Banner ── */
    .status-banner {
        padding: 0.85rem 3rem;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    .status-banner-pending {
        background: linear-gradient(90deg, rgba(162, 128, 81, 0.12), rgba(162, 128, 81, 0.05));
        color: #7a5e30;
        border-bottom: 1px solid rgba(162, 128, 81, 0.2);
    }

    .status-banner-completed {
        background: linear-gradient(90deg, rgba(40, 120, 70, 0.1), rgba(40, 120, 70, 0.03));
        color: #2a6040;
        border-bottom: 1px solid rgba(40, 120, 70, 0.2);
    }

    .status-banner i {
        font-size: 14px;
    }

    /* ── Document Body ── */
    .document-body {
        padding: 2.5rem 3rem;
    }

    /* ── Section Styles ── */
    .doc-section {
        margin-bottom: 2.5rem;
        page-break-inside: avoid;
    }

    .doc-section:last-child {
        margin-bottom: 0;
    }

    .section-header {
        display: flex;
        align-items: center;
        gap: 10px;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid #A28051;
        margin-bottom: 1.25rem;
    }

    .section-icon {
        width: 28px;
        height: 28px;
        background: linear-gradient(135deg, #f5ebe0, #efe3d3);
        border: 1px solid rgba(162, 128, 81, 0.3);
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #A28051;
        font-size: 12px;
    }

    .section-title {
        font-family: Georgia, serif;
        font-size: 14px;
        font-weight: 700;
        color: #2a1a05;
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    /* ── Two Column Layout ── */
    .two-col {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2.5rem;
    }

    @media (max-width: 700px) {
        .two-col {
            grid-template-columns: 1fr;
            gap: 2rem;
        }
    }

    /* ── Info Table Style ── */
    .info-table {
        width: 100%;
    }

    .info-table tr {
        border-bottom: 1px solid rgba(162, 128, 81, 0.1);
    }

    .info-table tr:last-child {
        border-bottom: none;
    }

    .info-table th {
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        color: #8a6a3a;
        padding: 10px 0;
        text-align: left;
        width: 40%;
        vertical-align: top;
    }

    .info-table td {
        font-size: 13px;
        color: #2a1a05;
        padding: 10px 0;
        vertical-align: top;
    }

    .info-table td.muted {
        color: rgba(90, 60, 20, 0.5);
    }

    /* ── Address Block ── */
    .address-block {
        background: rgba(162, 128, 81, 0.04);
        border: 1px solid rgba(162, 128, 81, 0.15);
        border-left: 3px solid #A28051;
        padding: 1rem 1.25rem;
        font-size: 13px;
        color: #3a2510;
        line-height: 1.8;
    }

    .address-block .name {
        font-weight: 700;
        font-size: 14px;
        margin-bottom: 4px;
    }

    /* ── Items Table ── */
    .items-table {
        width: 100%;
        border-collapse: collapse;
    }

    .items-table thead {
        background: linear-gradient(90deg, rgba(162, 128, 81, 0.1), rgba(162, 128, 81, 0.05));
    }

    .items-table th {
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        color: #5a4020;
        padding: 12px 14px;
        text-align: left;
        border-bottom: 2px solid rgba(162, 128, 81, 0.25);
    }

    .items-table th:last-child {
        text-align: right;
    }

    .items-table td {
        padding: 16px 14px;
        border-bottom: 1px solid rgba(162, 128, 81, 0.12);
        vertical-align: top;
    }

    .items-table tbody tr:last-child td {
        border-bottom: none;
    }

    .items-table td:last-child {
        text-align: right;
    }

    .item-cell {
        display: flex;
        gap: 14px;
        align-items: flex-start;
    }

    .item-thumb {
        width: 64px;
        height: 64px;
        border-radius: 6px;
        object-fit: cover;
        border: 1px solid rgba(162, 128, 81, 0.25);
        flex-shrink: 0;
    }

    .item-thumb-placeholder {
        width: 64px;
        height: 64px;
        border-radius: 6px;
        background: rgba(162, 128, 81, 0.08);
        border: 1px dashed rgba(162, 128, 81, 0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        color: rgba(162, 128, 81, 0.4);
        font-size: 20px;
        flex-shrink: 0;
    }

    .item-info {
        flex: 1;
    }

    .item-name {
        font-family: Georgia, serif;
        font-size: 14px;
        font-weight: 700;
        color: #2a1a05;
        margin-bottom: 4px;
    }

    .item-desc {
        font-size: 11px;
        color: rgba(90, 60, 20, 0.6);
        line-height: 1.6;
    }

    .item-price {
        font-size: 15px;
        font-weight: 700;
        color: #A28051;
        font-family: Georgia, serif;
    }

    .item-images-row {
        display: flex;
        gap: 6px;
        margin-top: 10px;
    }

    .item-mini-img {
        width: 40px;
        height: 40px;
        border-radius: 4px;
        object-fit: cover;
        border: 1px solid rgba(162, 128, 81, 0.25);
        cursor: pointer;
        transition: transform 0.2s, border-color 0.2s;
    }

    .item-mini-img:hover {
        transform: scale(1.08);
        border-color: #A28051;
    }

    /* ── Payment Summary Box ── */
    .payment-summary {
        background: linear-gradient(135deg, #fdfbf7, #f9f3eb);
        border: 1px solid rgba(162, 128, 81, 0.2);
        border-radius: 6px;
        padding: 1.5rem;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        font-size: 13px;
    }

    .summary-row.total {
        border-top: 2px solid rgba(162, 128, 81, 0.3);
        margin-top: 8px;
        padding-top: 14px;
    }

    .summary-label {
        color: #5a4020;
    }

    .summary-value {
        font-weight: 600;
        color: #2a1a05;
    }

    .summary-row.total .summary-label {
        font-size: 14px;
        font-weight: 700;
        color: #2a1a05;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .summary-row.total .summary-value {
        font-size: 20px;
        font-weight: 700;
        color: #A28051;
        font-family: Georgia, serif;
    }

    /* ── Status Badge ── */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 12px;
        border-radius: 4px;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    .status-badge-paid {
        background: rgba(40, 120, 70, 0.1);
        color: #1e7a48;
        border: 1px solid rgba(40, 120, 70, 0.25);
    }

    .status-badge-pending {
        background: rgba(162, 128, 81, 0.12);
        color: #7a5e30;
        border: 1px solid rgba(162, 128, 81, 0.3);
    }

    .status-badge-failed {
        background: rgba(190, 50, 50, 0.1);
        color: #a03030;
        border: 1px solid rgba(190, 50, 50, 0.25);
    }

    /* ── Document Footer ── */
    .document-footer {
        padding: 1.5rem 3rem;
        border-top: 1px solid rgba(162, 128, 81, 0.15);
        background: linear-gradient(180deg, #fff, #fdfbf7);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .footer-note {
        font-size: 10px;
        color: #8a6a3a;
        letter-spacing: 0.5px;
    }

    .footer-ornament {
        color: rgba(162, 128, 81, 0.4);
        font-size: 11px;
        letter-spacing: 4px;
    }

    /* ── Action Bar (outside document) ── */
    .action-bar {
        max-width: 900px;
        margin: 0 auto 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }


    .btn-back:hover {
        border-color: #A28051;
        color: #e8c98a;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .btn-print {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: 0.5px;
        text-decoration: none;
        background: #fff;
        border: 1px solid rgba(162, 128, 81, 0.3);
        color: #5a4020;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-print:hover {
        background: rgba(162, 128, 81, 0.08);
        border-color: #A28051;
    }

    /* ── No Data State ── */
    .no-data {
        text-align: center;
        padding: 2rem;
        color: rgba(120, 88, 42, 0.5);
        font-size: 13px;
    }

    .no-data i {
        font-size: 28px;
        display: block;
        margin-bottom: 10px;
        color: rgba(162, 128, 81, 0.3);
    }

    /* ── Print Styles ── */
    @media print {
        .page-wrap {
            padding: 0;
            background: #fff;
        }

        .action-bar {
            display: none;
        }

        .document-container {
            box-shadow: none;
            border: none;
        }

        .status-banner {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    }

    /* ── Modal ── */
    .modal-content {
        border-radius: 8px;
        border: 1px solid rgba(162, 128, 81, 0.3);
        background: #fdf8f0;
    }

    .modal-header {
        border-bottom: 1px solid rgba(162, 128, 81, 0.2);
        padding: 1rem 1.5rem;
    }

    .modal-title {
        font-family: Georgia, serif;
        font-size: 15px;
        color: #2a1a05;
        font-weight: 700;
    }

    @media (max-width: 900px) {
        .page-wrap {
            padding: 1rem;
        }

        .action-bar {
            flex-direction: column;
            align-items: stretch;
            gap: 1rem;
        }

        .document-container {
            margin: 0;
        }
    }

    @media (max-width: 700px) {

        .document-header,
        .document-body,
        .document-footer {
            padding-left: 1.25rem;
            padding-right: 1.25rem;
        }

        .order-meta-row {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .meta-item {
            text-align: left;
        }

        .header-top {
            flex-direction: column;
            gap: 1rem;
            align-items: stretch;
        }

        .document-title-block {
            text-align: left;
        }

        .status-banner {
            flex-direction: column;
            align-items: flex-start;
        }

        .two-col {
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        .items-table {
            display: block;
            width: 100%;
            overflow-x: auto;
        }

        .items-table th,
        .items-table td {
            white-space: nowrap;
        }

        .item-cell {
            flex-direction: column;
            align-items: stretch;
        }

        .item-thumb,
        .item-thumb-placeholder {
            width: 100%;
            max-width: 220px;
            height: auto;
        }

        .payment-summary {
            padding: 1rem;
        }

        .summary-row {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.35rem;
        }

        .summary-row.total {
            padding-top: 12px;
        }
    }

    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        margin-top: 2rem;
        padding: 0.7rem 1.6rem;
        background: transparent;
        border: 1.5px solid #d4c4b4;
        border-radius: 40px;
        color: #5c4b3a;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s;
    }
</style>
@section('content')
    <div class="page-wrap">

        {{-- ACTION BAR --}}
        <div class="action-bar">
            <a href="{{ route('admin.orders.index') }}" class="btn-back">
                <i class="fas fa-arrow-left"></i> {{ __('admin.back_to_orders') }}
            </a>

        </div>

        {{-- DOCUMENT --}}
        <div class="document-container">

            {{-- DOCUMENT HEADER --}}
            <div class="document-header">
                <div class="header-top">
                    <div class="company-brand">
                        <div class="company-logo">
                            <i class="fas fa-store"></i>
                        </div>
                        <div>
                            <div class="company-name">Your Store</div>
                            <div class="company-tagline">Premium Quality Products</div>
                        </div>
                    </div>

                    <div class="document-title-block">
                        <h1 class="document-title">ORDER DETAILS</h1>
                        <p class="document-subtitle">
                            {{ __('admin.view_and_manage_order_info') }}
                        </p>
                    </div>
                </div>

                <div class="order-meta-row">

                    <div class="meta-item">
                        <div class="meta-label">Order ID</div>
                        <div class="meta-value highlight">
                            #{{ $order->order_number ?? '—' }}
                        </div>
                    </div>

                    <div class="meta-item">
                        <div class="meta-label">Order Date</div>
                        <div class="meta-value">
                            {{ $order->created_at ? $order->created_at->format('d M Y') : '—' }}
                        </div>
                    </div>

                    <div class="meta-item">
                        <div class="meta-label">Status</div>
                        <div class="meta-value">
                            {{ ucfirst($order->order_status ?? 'N/A') }}
                        </div>
                    </div>

                    <div class="meta-item">
                        <div class="meta-label">Grand Total</div>
                        <div class="meta-value highlight">
                            ₹{{ number_format($grandTotal ?? 0, 2) }}
                        </div>
                    </div>

                </div>
            </div>

            {{-- STATUS BANNER --}}
            @if ($order->order_status == 'pending')
                <div class="status-banner status-banner-pending">
                    <i class="fas fa-hourglass-half"></i>
                    <span>
                        <strong>{{ __('admin.pending_order') }}:</strong>
                        {{ __('admin.pending_order_message') }}
                    </span>
                </div>
            @elseif($order->order_status == 'completed')
                <div class="status-banner status-banner-completed">
                    <i class="fas fa-check-circle"></i>
                    <span>
                        <strong>{{ __('admin.order_completed') }}:</strong>
                        {{ __('admin.order_completed_message') }}
                    </span>
                </div>
            @endif

            {{-- DOCUMENT BODY --}}
            <div class="document-body">

                {{-- CUSTOMER & SHIPPING --}}
                <div class="doc-section">

                    <div class="two-col">

                        {{-- Customer Information --}}
                        <div>

                            <div class="section-header">
                                <div class="section-icon">
                                    <i class="fas fa-user"></i>
                                </div>

                                <span class="section-title">
                                    Customer Information
                                </span>
                            </div>

                            <table class="info-table">

                                <tr>
                                    <th>{{ __('admin.full_name') }}</th>
                                    <td>{{ $order->user->full_name ?? __('admin.na') }}</td>
                                </tr>

                                <tr>
                                    <th>{{ __('admin.email_address') }}</th>
                                    <td>{{ $order->user->email ?? __('admin.na') }}</td>
                                </tr>

                                <tr>
                                    <th>{{ __('admin.phone_number') }}</th>
                                    <td>{{ $order->user->phone ?? __('admin.na') }}</td>
                                </tr>

                                <tr>
                                    <th>Account Type</th>
                                    <td>
                                        {{ strtoupper($order->user->account_type ?? 'B2C') }}
                                    </td>
                                </tr>

                            </table>

                        </div>

                        {{-- Shipping Address --}}
                        <div>

                            <div class="section-header">
                                <div class="section-icon">
                                    <i class="fas fa-truck"></i>
                                </div>

                                <span class="section-title">
                                    Shipping Address
                                </span>
                            </div>

                            @if ($order->address)
                                <div class="address-block">

                                    <div class="name">
                                        {{ $order->user->full_name ?? '' }}
                                    </div>

                                    <div>
                                        {{ $order->address->address_line_1 ?? '' }}
                                    </div>

                                    @if (!empty($order->address->address_line_2))
                                        <div>
                                            {{ $order->address->address_line_2 }}
                                        </div>
                                    @endif

                                    <div>
                                        {{ $order->address->city ?? '' }},
                                        {{ $order->address->state ?? '' }}
                                        {{ $order->address->postal_code ?? '' }}
                                    </div>

                                    <div>
                                        {{ $order->address->country ?? '' }}
                                    </div>

                                </div>
                            @else
                                <div class="no-data">
                                    <i class="fas fa-map-marker-alt"></i>
                                    No shipping address provided
                                </div>
                            @endif

                        </div>

                    </div>

                </div>

                {{-- ORDER ITEMS --}}
                <div class="doc-section">

                    <div class="section-header">
                        <div class="section-icon">
                            <i class="fas fa-box"></i>
                        </div>

                        <span class="section-title">
                            {{ __('admin.item_details') }}
                        </span>
                    </div>

                    <table class="items-table">

                        <thead>
                            <tr>
                                <th style="width: 60%;">Item</th>
                                <th>Qty</th>
                                <th>Unit Price</th>
                                <th>Total</th>
                            </tr>
                        </thead>

                        <tbody>

                            @forelse ($order->items as $orderItem)
                                <tr>

                                    <td>

                                        <div class="item-cell">

                                            @if ($orderItem->item && $orderItem->item->images->first())
                                                <img src="{{ asset('storage/' . $orderItem->item->images->first()->image) }}"
                                                    class="item-thumb" alt="Item">
                                            @else
                                                <div class="item-thumb-placeholder">
                                                    <i class="fas fa-image"></i>
                                                </div>
                                            @endif

                                            <div class="item-info">

                                                <div class="item-name">
                                                    {{ $orderItem->item->name ?? __('admin.na') }}
                                                </div>

                                                <div class="item-desc">
                                                    {{ Str::limit($orderItem->item->description ?? 'No description', 120) }}
                                                </div>

                                                @if ($orderItem->item && $orderItem->item->images->count() > 1)
                                                    <div class="item-images-row">

                                                        @foreach ($orderItem->item->images->skip(1)->take(4) as $img)
                                                            <img src="{{ asset('storage/' . $img->image) }}"
                                                                class="item-mini-img" data-bs-toggle="modal"
                                                                data-bs-target="#imageModal"
                                                                onclick="showImage('{{ asset('storage/' . $img->image) }}')">
                                                        @endforeach

                                                    </div>
                                                @endif

                                            </div>

                                        </div>

                                    </td>

                                    <td>
                                        {{ $orderItem->quantity ?? 1 }}
                                    </td>

                                    <td>
                                        <span class="item-price">
                                            ₹{{ number_format($orderItem->unit_price ?? 0, 2) }}
                                        </span>
                                    </td>

                                    <td>
                                        <span class="item-price">
                                            ₹{{ number_format(($orderItem->quantity ?? 1) * ($orderItem->unit_price ?? 0), 2) }}
                                        </span>
                                    </td>

                                </tr>

                            @empty

                                <tr>
                                    <td colspan="4">

                                        <div class="no-data">
                                            <i class="fas fa-box-open"></i>
                                            No items in this order
                                        </div>

                                    </td>
                                </tr>
                            @endforelse

                        </tbody>

                    </table>

                </div>

                {{-- PAYMENT DETAILS --}}
                <div class="doc-section">

                    <div class="two-col">

                        {{-- Payment Information --}}
                        <div>

                            <div class="section-header">

                                <div class="section-icon">
                                    <i class="fas fa-credit-card"></i>
                                </div>

                                <span class="section-title">
                                    {{ __('admin.payment_details') }}
                                </span>

                            </div>

                            @if ($order->payment)
                                <table class="info-table">

                                    <tr>
                                        <th>{{ __('admin.payment_status') }}</th>

                                        <td>

                                            @php
                                                $ps = $order->payment->status;
                                            @endphp

                                            <span
                                                class="status-badge {{ $ps == 'paid' ? 'status-badge-paid' : ($ps == 'pending' ? 'status-badge-pending' : 'status-badge-failed') }}">

                                                <i
                                                    class="fas fa-{{ $ps == 'paid' ? 'check' : ($ps == 'pending' ? 'clock' : 'exclamation-triangle') }}"></i>

                                                {{ ucfirst($ps) }}

                                            </span>

                                        </td>
                                    </tr>

                                    <tr>
                                        <th>{{ __('admin.payment_method') }}</th>

                                        <td>

                                            <i class="fas fa-{{ $order->payment->payment_method == 'stripe' ? 'cc-stripe' : 'wallet' }}"
                                                style="color:#8b7355; margin-right:6px;"></i>

                                            {{ strtoupper($order->payment->payment_method ?? 'N/A') }}

                                        </td>
                                    </tr>

                                    <tr>
                                        <th>{{ __('admin.paid_at') }}</th>

                                        <td>

                                            @if ($order->payment->paid_at)
                                                {{ \Carbon\Carbon::parse($order->payment->paid_at)->format('d M Y, h:i A') }}
                                            @else
                                                <span class="muted">—</span>
                                            @endif

                                        </td>
                                    </tr>

                                    <tr>
                                        <th>{{ __('admin.transaction_id') }}</th>

                                        <td style="font-size:11px; word-break:break-all; color:#555;">

                                            {{ $order->payment->transaction_id ?? __('admin.na') }}

                                        </td>
                                    </tr>

                                </table>
                            @else
                                <div class="no-data">
                                    <i class="fas fa-credit-card"></i>
                                    {{ __('admin.no_payment_record') }}
                                </div>
                            @endif

                        </div>

                        {{-- ORDER SUMMARY --}}
                        <div>

                            <div class="section-header">

                                <div class="section-icon">
                                    <i class="fas fa-receipt"></i>
                                </div>

                                <span class="section-title">
                                    Order Summary
                                </span>

                            </div>

                            <div class="payment-summary">

                                {{-- Subtotal --}}
                                <div class="summary-row">
                                    <span class="summary-label">
                                        Subtotal
                                    </span>

                                    <span class="summary-value">
                                        ₹{{ number_format($subtotal ?? 0, 2) }}
                                    </span>
                                </div>

                                {{-- B2B Discount --}}
                                @if (($businessDiscount ?? 0) > 0)
                                    <div class="summary-row">

                                        <span class="summary-label">
                                            Business Discount
                                        </span>

                                        <span class="summary-value text-success">
                                            - ₹{{ number_format($businessDiscount, 2) }}
                                        </span>

                                    </div>
                                @endif

                                {{-- Promo Discount --}}
                                @if (($promocodeDiscount ?? 0) > 0)
                                    <div class="summary-row">

                                        <span class="summary-label">

                                            Promo Discount

                                            @if ($order->promocode)
                                                ({{ $order->promocode->code }})
                                            @endif

                                        </span>

                                        <span class="summary-value text-success">
                                            - ₹{{ number_format($promocodeDiscount, 2) }}
                                        </span>

                                    </div>
                                @endif

                                {{-- Shipping --}}
                                <div class="summary-row">

                                    <span class="summary-label">
                                        Shipping Charges
                                    </span>

                                    <span class="summary-value">
                                        ₹{{ number_format($shippingCharges ?? 0, 2) }}
                                    </span>

                                </div>

                                {{-- Grand Total --}}
                                <div class="summary-row total">

                                    <span class="summary-label">
                                        Grand Total
                                    </span>

                                    <span class="summary-value">
                                        ₹{{ number_format($grandTotal ?? 0, 2) }}
                                    </span>

                                </div>

                                {{-- Paid --}}
                                <div class="summary-row">

                                    <span class="summary-label">
                                        Paid Amount
                                    </span>

                                    <span class="summary-value text-success">
                                        ₹{{ number_format($order->paid_amount ?? 0, 2) }}
                                    </span>

                                </div>

                                {{-- Due --}}
                                @if (($order->due_amount ?? 0) > 0)
                                    <div class="summary-row">

                                        <span class="summary-label">
                                            Due Amount
                                        </span>

                                        <span class="summary-value text-danger">
                                            ₹{{ number_format($order->due_amount ?? 0, 2) }}
                                        </span>

                                    </div>
                                @endif

                            </div>

                        </div>

                    </div>

                </div>

            </div>

            {{-- DOCUMENT FOOTER --}}
            <div class="document-footer">

                <div class="footer-note">

                    <i class="fas fa-info-circle" style="margin-right:4px;"></i>

                    This document was generated on
                    {{ now()->format('d M Y, h:i A') }}

                </div>

                <div class="footer-ornament">
                    ✦ &nbsp; ✦ &nbsp; ✦
                </div>

            </div>

        </div>

    </div>

    {{-- IMAGE MODAL --}}
    <div class="modal fade" id="imageModal" tabindex="-1">

        <div class="modal-dialog modal-dialog-centered">

            <div class="modal-content">

                <div class="modal-header">

                    <h5 class="modal-title">
                        {{ __('admin.product_image') }}
                    </h5>

                    <button type="button" class="btn-close" data-bs-dismiss="modal">
                    </button>

                </div>

                <div class="modal-body text-center" style="padding:1.25rem;">

                    <img id="modalImage" src="" class="img-fluid"
                        style="border-radius:8px; border:1px solid rgba(162,128,81,0.25);" alt="Product Image">

                </div>

            </div>

        </div>

    </div>

@endsection
@push('scripts')
    <script>
        function showImage(src) {
            document.getElementById('modalImage').src = src;
        }

        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(el) {
                return new bootstrap.Tooltip(el);
            });
        });
    </script>
@endpush
