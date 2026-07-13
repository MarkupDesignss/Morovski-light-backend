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
        padding: 50px 80px 40px;
    }

    .cover-logo {
        max-width: 180px;
        height: auto;
        margin-bottom: 32px;
    }

    .cover-logo img {
        width: 100%;
        height: auto;
        display: block;
    }

    .cover-eyebrow {
        font-size: 11px;
        letter-spacing: 0.3em;
        text-transform: uppercase;
        color: var(--gold);
        margin-bottom: 12px;
        font-weight: 400;
    }

    .cover-title {
        font-family: 'Fraunces', serif;
        font-weight: 600;
        font-size: 68px;
        line-height: 1;
        letter-spacing: -0.01em;
        margin-bottom: 6px;
    }

    .cover-title .accent {
        color: var(--gold);
        font-style: italic;
        font-weight: 500;
    }

    .cover-tagline {
        font-size: 13px;
        letter-spacing: 0.22em;
        text-transform: uppercase;
        color: var(--muted);
        margin-top: 8px;
        font-weight: 300;
    }

    .cover-divider {
        width: 48px;
        height: 1px;
        background: var(--line);
        margin: 32px 0;
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
        font-size: 9px;
        padding: 0 10px;
    }

    .stat-row {
        display: flex;
        width: 100%;
        max-width: 520px;
        background: var(--paper);
        border: 1px solid var(--line);
        border-radius: 4px;
        overflow: hidden;
    }

    .stat {
        flex: 1;
        padding: 24px 28px;
        text-align: left;
    }

    .stat+.stat {
        border-left: 1px solid var(--line);
    }

    .stat-value {
        font-family: 'Fraunces', serif;
        font-weight: 600;
        font-size: 30px;
        color: var(--ink);
        display: flex;
        align-items: baseline;
        gap: 2px;
    }

    .stat-value .currency {
        font-size: 20px;
        color: var(--gold);
        font-weight: 500;
    }

    .stat-label {
        margin-top: 6px;
        font-size: 10px;
        letter-spacing: 0.16em;
        text-transform: uppercase;
        color: var(--muted);
    }

    .stat-note {
        margin-top: 4px;
        font-size: 9.5px;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--gold);
    }

    /* ==================== PAGE 2 — PRODUCTS ==================== */
    .products-main {
        flex: 1;
        padding: 25px 45px 25px;
        display: flex;
        flex-direction: column;
    }

    .products-head {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        margin-bottom: 8px;
        padding-bottom: 22px;
        border-bottom: 1px solid var(--line);
    }

    .products-head .cover-eyebrow {
        margin-bottom: 6px;
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
        gap: 12px;
        margin-top: 8px;
    }

    .product-card {
        background: var(--paper);
        border: 1px solid var(--line);
        border-radius: 4px;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .product-category {
        padding: 10px 12px 0;
        font-size: 12.5px;
        font-weight: 500;
        text-transform: uppercase;
        color: var(--gold);
    }

    .product-image {
        width: 100%;
        aspect-ratio: 4/3;
        margin: 8px 0 0;
        background: #eee6d3;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .product-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .product-image .no-image {
        color: var(--muted);
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }

    .product-info {
        padding: 12px 12px 16px;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .product-sku {
        font-size: 11px;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: var(--muted);
    }

    .product-name {
        font-family: 'Fraunces', serif;
        font-weight: 500;
        font-size: 18px;
        line-height: 1.3;
        color: var(--ink);
        margin-bottom: 2px;
    }

    .product-description {
        font-size: 13px;
        line-height: 1.4;
        color: var(--muted);
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        overflow: hidden;
    }

    .product-bottom {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 6px;
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

    .stock-badge {
        font-size: 9.5px;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        padding: 5px 10px;
        border-radius: 20px;
        font-weight: 500;
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

    /* hide/show controls */
    .hide-price .product-price {
        display: none;
    }

    .hide-description .product-description {
        display: none;
    }

    @media print {
        body {
            background: none;
        }

        .page {
            box-shadow: none;
            margin: 0;
            width: auto;
            min-height: 100vh;
            page-break-after: always;
        }

        @page {
            size: auto;
            margin: 0mm;
        }

        html,
        body {
            margin: 0px;
            padding: 0px;
            border: none !important;
        }
    }
</style>