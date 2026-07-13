<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="x-apple-disable-message-reformatting" />
    <title>Morovski Lights | Refined Document</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=Jost:wght@200;300;400;500;600&display=swap" rel="stylesheet" />
    <style type="text/css">
        /* RESET & GLOBAL */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            background-color: #0d0c0b;
             font-family: 'Alegreya', serif;
            color: #e8dfd0;
            -webkit-font-smoothing: antialiased;
            margin: 0;
            padding: 0;
        }
        .email-wrapper {
            max-width: 640px;
            margin: 0 auto;
            background-color: #0d0c0b;
            width: 100%;
        }
        /* RESPONSIVE */
        @media only screen and (max-width: 620px) {
            .email-wrapper {
                max-width: 100%;
            }
            .content, .hero, .footer {
                padding-left: 24px !important;
                padding-right: 24px !important;
            }
            .features {
                display: block !important;
                width: 100% !important;
            }
            .feature-col {
                display: block !important;
                width: 100% !important;
                border-right: none !important;
                border-bottom: 1px solid #2a2318;
                padding: 28px 16px !important;
            }
            .feature-col:last-child {
                border-bottom: none;
            }
            .invoice-table-wrapper {
                overflow-x: auto;
                display: block;
            }
            .hero-title {
                font-size: 32px !important;
            }
            .summary-section table,
            .customer-card table {
                width: 100%;
            }
        }

        /* HEADER STYLES */
        .header {
            background: linear-gradient(180deg, #0d0c0b 0%, #13110e 100%);
            padding: 28px 28px 26px;
            text-align: center;
            border-bottom: 1px solid #2a2318;
            position: relative;
            overflow: hidden;
        }
        .header::before {
            content: "";
            position: absolute;
            top: -60px;
            left: 50%;
            transform: translateX(-50%);
            width: 320px;
            height: 200px;
            background: radial-gradient(ellipse at center, rgba(184,140,60,0.12) 0%, transparent 70%);
            pointer-events: none;
        }
        .logo-img {
            max-width: 120px;
            border-radius: 50%;
            height: auto;
            display: block;
            margin: 0 auto;
            filter: brightness(1.05);
        }
        .tagline {
            font-family: 'Jost', sans-serif;
            font-weight: 200;
            font-size: 10px;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: #b88c3c;
            margin-top: 10px;
        }

        /* HERO / DOCUMENT BANNER */
        .hero {
            background: #13110e;
            padding: 48px 48px 44px;
            text-align: center;
            position: relative;
            border-bottom: 1px solid #2a2318;
        }
        .hero::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 1px;
            background: #b88c3c;
        }
        .hero-eyebrow {
            font-family: 'Jost', sans-serif;
            font-weight: 300;
            font-size: 10px;
            letter-spacing: 5px;
            text-transform: uppercase;
            color: #b88c3c;
            margin-bottom: 20px;
        }
        .hero-title {
            font-family: 'Cormorant Garamond', serif;
            font-weight: 300;
            font-size: 42px;
            line-height: 1.2;
            color: #f0e8d8;
            margin-bottom: 18px;
        }
        .hero-title em {
            font-style: italic;
            color: #d4a84b;
        }
        .hero-body {
            font-family: 'Jost', sans-serif;
            font-weight: 300;
            font-size: 14px;
            line-height: 1.8;
            color: #9e9284;
            max-width: 460px;
            margin: 0 auto;
        }

        /* CONTENT CORE */
        .content {
            background: #0d0c0b;
            padding: 0 48px 48px;
        }

        /* CUSTOMER CARD (elevated) */
        .customer-card {
            background: rgba(19, 17, 14, 0.9);
            border-radius: 28px;
            border: 0.5px solid rgba(184,140,60,0.35);
            padding: 28px 32px;
            margin: 28px 0 32px;
            backdrop-filter: blur(2px);
        }
        .customer-salute {
            font-family: 'Cormorant Garamond', serif;
            font-size: 24px;
            font-weight: 400;
            color: #ecd9a4;
            margin-bottom: 8px;
        }
        .gold-divider {
            width: 40px;
            height: 1px;
            background: #d4af37;
            margin: 12px 0 18px;
        }
        .info-grid {
            width: 100%;
            font-size: 14px;
            font-family: 'Jost', sans-serif;
            color: #e2d3b0;
        }
        .info-grid td {
            padding: 6px 0;
        }
        .status-badge {
            background: rgba(212,175,55,0.15);
            padding: 4px 14px;
            border-radius: 40px;
            font-size: 12px;
            font-weight: 500;
            color: #ecd9a4;
            letter-spacing: 0.5px;
            display: inline-block;
        }

        /* FEATURE ROWS (brand elements) */
        .features {
            display: table;
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin: 40px 0 28px;
        }
        .feature-col {
            display: table-cell;
            width: 33.33%;
            padding: 28px 20px;
            text-align: center;
            vertical-align: top;
            border-top: 1px solid #2a2318;
        }
        .feature-col:not(:last-child) {
            border-right: 1px solid #1c1913;
        }
        .feature-icon {
            font-size: 22px;
            color: #b88c3c;
            margin-bottom: 12px;
            line-height: 1;
        }
        .feature-title {
            font-family: 'Cormorant Garamond', serif;
            font-weight: 500;
            font-size: 16px;
            color: #e8dfd0;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }
        .feature-desc {
            font-family: 'Jost', sans-serif;
            font-weight: 300;
            font-size: 12px;
            line-height: 1.8;
            color: #6e6256;
        }

        /* INVOICE TABLE */
        .invoice-table-wrapper {
            margin: 16px 0 24px;
            border-radius: 20px;
            overflow: hidden;
        }
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            font-family: 'Jost', sans-serif;
            font-size: 13px;
            background: #111014;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 14px rgba(0,0,0,0.3);
        }
        .invoice-table thead {
            background: #16130d;
            border-bottom: 1px solid rgba(212,175,55,0.3);
        }
        .invoice-table th {
            color: #d4af37;
            font-weight: 500;
            padding: 14px 12px;
            text-align: left;
        }
        .invoice-table td {
            padding: 14px 12px;
            color: #f5e8c0;
            border-bottom: 1px solid rgba(212,175,55,0.08);
        }
        .product-cell {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        .product-img {
            width: 52px;
            height: 52px;
            border-radius: 10px;
            object-fit: cover;
            border: 1px solid rgba(212,175,55,0.35);
            background: #0a0804;
        }
        .product-name {
            font-weight: 500;
            font-size: 14px;
            color: #f5e8c0;
        }
        .product-sku {
            font-size: 10px;
            color: rgba(212,175,55,0.65);
            margin-top: 4px;
        }

        /* SUMMARY SECTION */
        .summary-section {
            margin-top: 16px;
            padding-top: 24px;
            border-top: 1px solid rgba(212,175,55,0.25);
        }
        .summary-table {
            width: 100%;
            font-family: 'Jost', sans-serif;
            font-size: 15px;
        }
        .summary-table td {
            padding: 6px 0;
        }
        .summary-label {
            text-align: right;
            color: rgba(245,232,192,0.8);
        }
        .summary-value {
            text-align: right;
            color: #f5e8c0;
            font-weight: 500;
            padding-left: 28px;
        }

        /* QUOTE BLOCK (luxury testimonial) */
        .quote-block {
            padding: 44px 32px;
            text-align: center;
            border-top: 1px solid rgba(212,175,55,0.12);
            border-bottom: 1px solid rgba(212,175,55,0.12);
            margin: 36px 0 24px;
            background: linear-gradient(135deg, #0c0b07 0%, #12100a 50%, #0c0b07 100%);
        }
        .quote-mark {
            font-family: 'Cormorant Garamond', serif;
            font-size: 68px;
            color: rgba(212,175,55,0.15);
            line-height: 0.5;
            margin-bottom: 20px;
        }
        .quote-text {
            margin: 0 0 20px;
            font-family: 'Cormorant Garamond', serif;
            font-size: 20px;
            font-weight: 300;
            font-style: italic;
            color: #f5e8c0;
            line-height: 1.65;
        }
        .quote-author {
            margin: 0;
            font-family: 'Jost', sans-serif;
            font-size: 10px;
            font-weight: 400;
            color: rgba(212,175,55,0.6);
            letter-spacing: 4px;
            text-transform: uppercase;
        }

        /* SECONDARY CTA */
        .secondary-cta {
            text-align: center;
            padding: 32px 0 16px;
        }
        .secondary-cta a {
            font-family: 'Jost', sans-serif;
            font-weight: 300;
            font-size: 11px;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #b88c3c;
            text-decoration: none;
            border-bottom: 1px solid #3a2e18;
            padding-bottom: 3px;
        }

        /* FOOTER BRAND */
        .footer {
            background: #090807;
            padding: 40px 48px 36px;
            border-top: 1px solid #1c1913;
            text-align: center;
        }
        .footer-logo {
            font-family: 'Cormorant Garamond', serif;
            font-weight: 300;
            font-size: 22px;
            letter-spacing: 8px;
            text-transform: uppercase;
            color: #5a4e3a;
            margin-bottom: 28px;
        }
        .footer-links {
            margin-bottom: 28px;
        }
        .footer-links a {
            font-family: 'Jost', sans-serif;
            font-weight: 300;
            font-size: 10px;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            color: #4a4033;
            text-decoration: none;
            margin: 0 14px;
        }
        .footer-links a:hover {
            color: #b88c3c;
        }
        .footer-separator {
            width: 32px;
            height: 1px;
            background: #2a2318;
            margin: 0 auto 24px;
        }
        .footer-address {
            font-family: 'Jost', sans-serif;
            font-weight: 300;
            font-size: 11px;
            line-height: 1.9;
            color: #3a3020;
            letter-spacing: 0.5px;
        }
        .footer-address a {
            color: #4a4033;
            text-decoration: none;
        }
        .footer-unsubscribe {
            margin-top: 20px;
            font-family: 'Jost', sans-serif;
            font-size: 10px;
            color: #2e2820;
            letter-spacing: 1px;
        }
        .footer-unsubscribe a {
            color: #3a3020;
            text-decoration: underline;
        }
        .gold-accent-bar {
            height: 1px;
            background: linear-gradient(90deg, transparent, #d4af37, #f5e8c0, #d4af37, transparent);
            margin: 20px 0;
        }
    </style>
</head>
<body>
    @php
        /*
        |--------------------------------------------------------------------------
        | DYNAMIC DATA HANDLE (PRESERVED ORIGINAL LOGIC)
        |--------------------------------------------------------------------------
        */
        if ($type == 'invoice') {
            $customer = $order->user;
            $items = $order->items;
            $invoiceNumber = $order->order_number ?? $order->id;
            $createdDate = $order->created_at;
            $paymentStatus = $order->payment_status ?? 'paid';
            $subtotal = $order->total_amount ?? ($order->grand_total ?? 0);
            $paidAmount = $order->paid_amount ?? $subtotal;
        } else {
            $customer = $invoice->client;
            $items = $invoice->items;
            $invoiceNumber = $invoice->id;
            $createdDate = $invoice->created_at;
            $paymentStatus = $invoice->pi_status ?? 'paid';
            $subtotal = $invoice->total_amount ?? 0;
            $paidAmount = $invoice->amount_paid ?? 0;
        }
    @endphp

    <div class="email-wrapper">
        <!-- HEADER (brand consistent) -->
        <div class="header">
            <a href="https://www.markupdesigns.net/morovski-light-web/">
                <img src="https://www.markupdesigns.net/morovski-light/logo/MORVOSKI-logo.png" alt="MOROVSKI" class="logo-img" />
            </a>
            <p class="tagline">Crafted Illumination</p>
        </div>

        <!-- HERO BANNER (document type & number) -->
        <div class="hero">
            <div class="hero-eyebrow">
                @if ($type == 'proforma')
                    PROFORMA INVOICE
                @elseif ($type == 'dispatch')
                    DISPATCH CONFIRMATION
                @elseif ($type == 'invoice')
                    TAX INVOICE
                @else
                    ORDER RECEIPT
                @endif
            </div>
            <div class="hero-title">
                #{{ $invoiceNumber }} <em>✦</em>
            </div>
            <div class="hero-body">
                @if ($type == 'proforma')
                    ✦ Thank you for choosing Morovski. Below is your proforma invoice. ✦
                @elseif ($type == 'dispatch')
                    ✦ Your luminous pieces are on their way. Dispatch details attached. ✦
                @elseif ($type == 'invoice')
                    ✦ Payment received with grace. Your invoice is confirmed. ✦
                @else
                    ✦ A record of your transaction with Morovski Lights. ✦
                @endif
            </div>
        </div>

        <!-- CORE CONTENT -->
        <div class="content">
            <!-- CUSTOMER CARD refined -->
            <div class="customer-card">
                <div class="customer-salute">
                    Dear <strong style="font-weight:500; color:#f5e8c0;">{{ $customer->full_name ?? ($customer->name ?? 'Valued Patron') }}</strong>
                </div>
                <div class="gold-divider"></div>
                <table class="info-grid" cellpadding="0" cellspacing="0">
                    <tr>
                        <td width="120" style="opacity:0.8;">📅 DATE</td>
                        <td style="font-weight:500;">{{ $createdDate->format('d F, Y') }}</td>
                    </tr>
                    <tr>
                        <td style="opacity:0.8;">⚡ STATUS</td>
                        <td><span class="status-badge">{{ ucfirst($paymentStatus) }}</span></td>
                    </tr>
                    <tr>
                        <td style="opacity:0.8;">📄 DOCUMENT</td>
                        <td style="font-weight:500;">#{{ $invoiceNumber }}</td>
                    </tr>
                </table>
            </div>

            <!-- FEATURE ROWS (brand touch) -->
            <div class="features">
                <div class="feature-col">
                    <div class="feature-icon">✦</div>
                    <div class="feature-title">Artisan Craft</div>
                    <div class="feature-desc">Each piece meticulously hand-finished by our lighting artisans.</div>
                </div>
                <div class="feature-col">
                    <div class="feature-icon">◈</div>
                    <div class="feature-title">Timeless Design</div>
                    <div class="feature-desc">Silhouettes conceived to endure beyond the transience of trend.</div>
                </div>
                <div class="feature-col">
                    <div class="feature-icon">⌘</div>
                    <div class="feature-title">Smart Light</div>
                    <div class="feature-desc">Seamless integration with modern intelligent home systems.</div>
                </div>
            </div>

            <!-- INVOICE ITEMS SECTION -->
            <div class="invoice-table-wrapper">
                <table class="invoice-table" cellpadding="0" cellspacing="0">
                    <thead>
                        <tr>
                            <th align="left">Product</th>
                            <th align="center">Qty</th>
                            <th align="right">Unit Price</th>
                            <th align="right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $item)
                            @php
                                $product = $item->item;
                                $image = $product?->images?->first();
                            @endphp
                            <tr>
                                <td>
                                    <div style="display:flex; align-items:center; gap:12px;">
                                        @if ($image)
                                            <img src="{{ asset('storage/' . $image->image) }}" class="product-img" alt="{{ $product->name ?? 'Artisan Piece' }}" />
                                        @endif
                                        <div>
                                            <div class="product-name">{{ $product->name ?? 'Artisan Piece' }}</div>
                                            @if ($product?->sku)
                                                <div class="product-sku">SKU: {{ $product->sku }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td align="center" style="color:#f5e8c0;">{{ $item->quantity }}</td>
                                <td align="right" style="color:#f5e8c0;">₹{{ number_format($product->price ?? 0, 2) }}</td>
                                <td align="right" style="font-weight:500; color:#f5e8c0;">₹{{ number_format($item->quantity * ($product->price ?? 0), 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- SUMMARY CARD -->
            <div class="summary-section">
                <table class="summary-table">
                    <tr>
                        <td class="summary-label"><strong>Subtotal:</strong></td>
                        <td class="summary-value">₹{{ number_format($subtotal, 2) }}</td>
                    </tr>
                    @if ($subtotal > $paidAmount)
                        <tr>
                            <td class="summary-label"><strong>Pending Balance:</strong></td>
                            <td class="summary-value" style="border-top:1px dashed rgba(212,175,55,0.2);">₹{{ number_format($subtotal - $paidAmount, 2) }}</td>
                        </tr>
                    @endif
                </table>
            </div>

            <!-- LUXURY QUOTE BLOCK (testimonial) -->
            <div class="quote-block">
                <div class="quote-mark">&ldquo;</div>
                <div class="quote-text">Light is not merely something to see by — it is something to see with. Morovski made us understand the difference.</div>
                <div class="quote-author">— Elana V., Interior Architect, Milan</div>
            </div>

            <!-- SECONDARY CTA (visit website) -->
            <div class="secondary-cta">
                <a href="https://www.markupdesigns.net/morovski-light-web/">Visit morovski.com →</a>
            </div>
        </div>

        <!-- FOOTER (brand aligned) -->
        <div class="footer">
            <div class="footer-logo">MOROVSKI</div>
            <div class="footer-links">
                <a href="https://www.markupdesigns.net/morovski-light-web/terms-and-condition">Terms &amp; Conditions</a>
                <a href="https://www.markupdesigns.net/morovski-light-web/privacy-policy">Privacy Policy</a>
                <a href="https://www.markupdesigns.net/morovski-light-web/cookies-policy">Cookies Policy</a>
            </div>
            <div class="footer-separator"></div>
            <div class="footer-address">
                MOROVSKI Lighting Co. &nbsp;·&nbsp; Studio &amp; Showroom<br />
                You are receiving this because you requested a transaction document.<br />
                <a href="https://www.markupdesigns.net/morovski-light-web/">morovski-light-web.com</a>
            </div>
            <div class="footer-unsubscribe">
                <a href="#">Unsubscribe</a> &nbsp;·&nbsp;
                <a href="#">Manage Preferences</a>
            </div>
        </div>
    </div>
</body>
</html>