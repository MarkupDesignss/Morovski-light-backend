<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Morovski — Luxury Catalogue</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,500;0,9..144,600;1,9..144,400&family=Jost:wght@300;400;500;600&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --ink: #1c1a16;
            --cream: #faf6ec;
            --paper: #ffffff;
            --gold: #a9812f;
            --gold-deep: #8a6a24;
            --muted: #8b8371;
            --line: #e4dcc7;
            --stock-low: #a9812f;
            --stock-out: #b34a3c;
            --stock-ok: #4d7a5c;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #d8d2c2;
            font-family: 'Jost', sans-serif;
            color: var(--ink);
            -webkit-font-smoothing: antialiased;
        }

        .page {
            width: 900px;
            min-height: 1035px;
            margin: 40px auto;
            background: var(--cream);
            display: flex;
            flex-direction: column;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25);
            position: relative;
            overflow: hidden;
        }

        /* ---------- TOP BLACK BAR (every page) ---------- */
        .topbar {
            background: var(--ink);
            color: #cdbf9a;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 48px;
            font-family: 'Jost', sans-serif;
            font-size: 12px;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            flex-shrink: 0;
        }

        .topbar .date {
            color: #cdbf9a;
        }

        .topbar .pageno {
            color: #c9a35a;
        }

        /* ---------- FOOTER (every page) ---------- */
        .pagefooter {
            border-top: 1px solid var(--line);
            padding: 10px 45px 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 11px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--muted);
            flex-shrink: 0;
            position: absolute;
            bottom: 0;
            width: 100%;
            left: 0;
            background: var(--cream);
        }

        .pagefooter .brand {
            color: var(--ink);
            font-weight: 500;
            letter-spacing: 0.1em;
        }

        /* ==================== PAGE 1 — COVER ==================== */
        .cover-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 40px 80px;
        }

        .cover-logo {
            width: 220px;
            height: auto;
            margin-bottom: 44px;
            max-height: 150px;
            object-fit: contain;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-size: 12px;
            letter-spacing: 0.28em;
            text-transform: uppercase;
            color: var(--gold);
            margin-bottom: 18px;
        }

        .eyebrow::before {
            content: "";
            width: 28px;
            height: 1px;
            background: var(--gold);
            display: inline-block;
        }

        .cover-title {
            font-family: 'Fraunces', serif;
            font-weight: 600;
            font-size: 74px;
            line-height: 0.98;
            letter-spacing: -0.01em;
        }

        .cover-title .accent {
            color: var(--gold);
            font-style: italic;
            font-weight: 500;
        }

        .cover-subtitle {
            margin-top: 26px;
            font-size: 13px;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: var(--muted);
        }

        .cover-divider {
            width: 64px;
            height: 1px;
            background: var(--line);
            margin: 44px 0;
            position: relative;
        }

        .cover-divider::after {
            content: "◆";
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            background: var(--cream);
            color: var(--gold);
            font-size: 10px;
            padding: 0 10px;
        }

        .stat-row {
            display: flex;
            width: 100%;
            max-width: 600px;
            background: var(--paper);
            border: 1px solid var(--line);
            border-radius: 4px;
            overflow: hidden;
        }

        .stat {
            flex: 1;
            padding: 30px 36px;
            text-align: left;
        }

        .stat+.stat {
            border-left: 1px solid var(--line);
        }

        .stat-value {
            font-family: 'Fraunces', serif;
            font-weight: 600;
            font-size: 34px;
            color: var(--ink);
            display: flex;
            align-items: baseline;
            gap: 2px;
        }

        .stat-value .currency {
            font-size: 22px;
            color: var(--gold);
            font-weight: 500;
        }

        .stat-label {
            margin-top: 10px;
            font-size: 11px;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: var(--muted);
        }

        .stat-note {
            margin-top: 6px;
            font-size: 10.5px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--gold);
        }

        /* ==================== PAGE 2 — PRODUCTS ==================== */
        .products-main {
            flex: 1;
            max-height: calc(100% - 37px);
            padding: 15px 45px 15px;
            display: flex;
            flex-direction: column;
        }

        .products-head {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            margin-bottom: 18px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--line);
        }

        .products-head .eyebrow {
            margin-bottom: 8px;
        }

        .products-heading {
            font-family: 'Fraunces', serif;
            font-weight: 600;
            font-size: 36px;
        }

        .products-count {
            font-size: 11px;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--muted);
            text-align: right;
        }

        .product-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            flex: 1;
            align-content: start;
        }

        .product-card {
            background: var(--paper);
            border: 1px solid var(--line);
            border-radius: 4px;
            display: flex;
            flex-direction: column;
            height: 100%;
            min-height: 0;
            overflow: hidden;
        }

        .product-category {
            padding: 10px 14px 0;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            color: var(--gold);
            letter-spacing: 0.08em;
            flex-shrink: 0;
        }

        .product-image-wrapper {
            padding: 8px 14px 0;
            background: #f5f0e6;
            flex-shrink: 0;
        }

        .product-image {
            width: 100%;
            aspect-ratio: 4/3;
            background: #eee6d3;
            display: block;
            object-fit: cover;
            border-radius: 2px;
        }

        .no-image-placeholder {
            width: 100%;
            aspect-ratio: 4/3;
            background: #e8e0d0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--muted);
            font-size: 11px;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }

        .product-info {
            padding: 12px 14px 16px;
            display: flex;
            flex-direction: column;
            gap: 6px;
            flex: 1;
            justify-content: space-between;
        }

        .product-name {
            font-family: 'Fraunces', serif;
            font-weight: 500;
            font-size: 16px;
            color: var(--ink);
            line-height: 1.2;
            flex-shrink: 0;
        }

        .product-sku {
            font-size: 10px;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--muted);
            flex-shrink: 0;
        }

        .product-bottom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 4px;
            flex-shrink: 0;
        }

        .product-price {
            font-family: 'Fraunces', serif;
            font-weight: 600;
            font-size: 22px;
            color: var(--ink);
        }

        .product-price .currency {
            color: var(--gold);
            font-size: 15px;
            margin-right: 2px;
        }

        .product-description {
            font-size: 12px;
            line-height: 1.4;
            color: var(--muted);
            flex-shrink: 0;
        }

        .product-description p {
            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 2;
            overflow: hidden;
            margin: 0;
        }

        .stock-badge {
            font-size: 9px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            padding: 4px 10px;
            border-radius: 20px;
            font-weight: 600;
            white-space: nowrap;
        }

        .stock-badge.out {
            color: var(--stock-out);
            background: rgba(179, 74, 60, 0.1);
        }

        .stock-badge.low {
            color: var(--stock-low);
            background: rgba(169, 129, 47, 0.12);
        }

        .stock-badge.ok {
            color: var(--stock-ok);
            background: rgba(77, 122, 92, 0.12);
        }

        .product-qty {
            font-size: 10px;
            color: var(--muted);
            margin-top: 2px;
            flex-shrink: 0;
        }

        .text-logo {
            font-family: 'Fraunces', serif;
            font-size: 52px;
            font-weight: 600;
            margin-bottom: 44px;
            color: var(--ink);
            letter-spacing: 0.02em;
        }

        @media print {
            body {
                background: none;
            }

            .page {
                box-shadow: none;
                margin: 0;
                width: 100%;
                min-height: 100vh;
                page-break-after: always;
                border-radius: 0;
            }

            .page:last-child {
                page-break-after: avoid;
            }

            @page {
                size: A4;
                margin: 0mm;
            }

            html,
            body {
                margin: 0px;
                padding: 0px;
                border: none !important;
            }

            .product-card {
                break-inside: avoid;
                page-break-inside: avoid;
            }
        }

        @media screen and (max-width: 768px) {
            .page {
                width: 100%;
                min-height: auto;
                margin: 20px auto;
            }

            .cover-main {
                padding: 30px 20px;
            }

            .cover-title {
                font-size: 42px;
            }

            .stat-row {
                flex-direction: column;
            }

            .stat+.stat {
                border-left: none;
                border-top: 1px solid var(--line);
            }

            .product-grid {
                grid-template-columns: 1fr;
            }

            .products-main {
                padding: 15px 20px;
            }
        }
    </style>
</head>

<body>

    <!-- ==================== PAGE 1 : COVER ==================== -->
    <div class="page">
        <div class="cover-main">
            <!-- LOGO SECTION -->
            @if (isset($showLogo) && $showLogo)
                @php
                    $logoDisplayed = false;
                    $logoPath = $logoPath ?? null;
                @endphp

                @if ($logoPath)
                    @if (filter_var($logoPath, FILTER_VALIDATE_URL))
                        <!-- External URL Logo -->
                        <img class="cover-logo" src="{{ $logoPath }}" alt="Company Logo"
                            onerror="this.style.display='none'">
                        @php $logoDisplayed = true; @endphp
                    @elseif(file_exists($logoPath))
                        <!-- Local File Logo -->
                        <img class="cover-logo" src="{{ $logoPath }}" alt="Company Logo">
                        @php $logoDisplayed = true; @endphp
                    @elseif(file_exists(public_path($logoPath)))
                        <!-- Public path logo -->
                        <img class="cover-logo" src="{{ asset($logoPath) }}" alt="Company Logo">
                        @php $logoDisplayed = true; @endphp
                    @endif
                @endif

                @if (!$logoDisplayed)
                    <!-- Fallback Text Logo -->
                    <div class="text-logo">MOROVSKI</div>
                @endif
            @else
                <!-- Logo Disabled - Show Text -->
                <div class="text-logo">MOROVSKI</div>
            @endif

            <div class="eyebrow">Luxury Lighting Collection</div>

            <div class="cover-title">
                The <span class="accent">Catalogue</span>
            </div>

            <div class="cover-subtitle">Handcrafted Illumination · Since 1972</div>

            <div class="cover-divider"></div>

            <div class="stat-row">
                <div class="stat">
                    <div class="stat-value">
                        <span class="currency">₹</span>{{ number_format($totalAmount ?? 0) }}
                    </div>
                    <div class="stat-label">Collection Value</div>
                </div>
                <div class="stat">
                    <div class="stat-value">{{ $totalItems ?? 0 }}</div>
                    <div class="stat-label">Pieces</div>
                    <div class="stat-note">Across {{ $items->groupBy('category.name')->count() ?? 0 }} Categories</div>
                </div>
            </div>
        </div>

        <div class="pagefooter">
            <span class="brand">© {{ date('Y') }} Morovski Lighting Pvt. Ltd.</span>
            <span>Generated: {{ $generatedDate ?? now()->format('d M Y H:i') }}</span>
        </div>
    </div>

    <!-- ==================== PRODUCT PAGES ==================== -->
    @php
        $itemsPerPage = 6;
        $chunkedItems = $items->chunk($itemsPerPage);
    @endphp

    @foreach ($chunkedItems as $chunkIndex => $chunk)
        <div class="page">
            <div class="products-main">
                <!-- Products Header -->
                <div class="products-head">
                    <div>
                        <div class="eyebrow">Product Catalogue</div>
                        <div class="products-heading">
                            Collection
                        </div>
                    </div>
                    <div class="products-count">
                        Page {{ $chunkIndex + 1 }} of {{ $chunkedItems->count() }}<br>
                        {{ $chunk->count() }} items
                    </div>
                </div>

                <!-- Product Grid -->
                <div class="product-grid">
                    @foreach ($chunk as $item)
                        <!-- Product Card -->
                        <div class="product-card">
                            <div class="product-category">{{ $item->category->name ?? 'Uncategorized' }}</div>

                            <!-- Product Image -->
                            <div class="product-image-wrapper">
                                @if ($item->images->isNotEmpty())
                                    <img class="product-image"
                                        src="{{ asset('storage/' . $item->images->first()->image) }}"
                                        alt="{{ $item->name ?? $item->sku }}" onerror="this.style.display='none'">
                                @else
                                    <div class="no-image-placeholder">
                                        No Image Available
                                    </div>
                                @endif
                            </div>

                            <!-- Product Info -->
                            <div class="product-info">
                                <div>
                                    <div class="product-name">{{ $item->name ?? 'Unnamed Product' }}</div>
                                    <div class="product-sku">SKU: {{ $item->sku }}</div>

                                    <!-- Description -->
                                    @if (isset($showDescription) && $showDescription && isset($item->description))
                                        <div class="product-description">
                                            <p>{{ Str::limit($item->description, 80) }}</p>
                                        </div>
                                    @endif
                                </div>

                                <!-- Price & Stock -->
                                <div>
                                    <div class="product-bottom">
                                        @if (isset($showPrice) && $showPrice && isset($item->price))
                                            <div class="product-price">
                                                <span class="currency">₹</span>{{ number_format($item->price) }}
                                            </div>
                                        @else
                                            <div class="product-price"
                                                style="font-size:14px;color:var(--muted);letter-spacing:0.08em;">
                                                Price Hidden
                                            </div>
                                        @endif

                                        @php
                                            $availableQty = ($item->quantity ?? 0) - ($item->damaged_quantity ?? 0);
                                            $stockClass =
                                                $availableQty <= 0 ? 'out' : ($availableQty <= 5 ? 'low' : 'ok');
                                            $stockLabel =
                                                $availableQty <= 0
                                                    ? 'Out of Stock'
                                                    : ($availableQty <= 5
                                                        ? 'Low Stock'
                                                        : 'In Stock');
                                        @endphp
                                        <div class="stock-badge {{ $stockClass }}">{{ $stockLabel }}</div>
                                    </div>

                                    <!-- Additional Info: Quantity -->
                                    @if (isset($item->quantity) && $availableQty > 0)
                                        <div class="product-qty">
                                            Qty: {{ $availableQty }} available
                                            @if (isset($item->damaged_quantity) && $item->damaged_quantity > 0)
                                                ({{ $item->damaged_quantity }} damaged)
                                            @endif
                                        </div>
                                    @elseif(isset($item->quantity))
                                        <div class="product-qty">
                                            Qty: 0 available
                                            @if (isset($item->damaged_quantity) && $item->damaged_quantity > 0)
                                                ({{ $item->damaged_quantity }} damaged)
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="pagefooter">
                <span class="brand">© {{ date('Y') }} Morovski Lighting Pvt. Ltd.</span>
                <span>Page {{ $chunkIndex + 1 }} of {{ $chunkedItems->count() }}</span>
            </div>
        </div>
    @endforeach

</body>

</html>
