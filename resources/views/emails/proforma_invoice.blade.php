<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Proforma Invoice – MOROVSKI</title>
    <link
        href="https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400;0,600;0,700;1,400&family=DM+Mono:wght@400;500&display=swap"
        rel="stylesheet" />
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --ink: #1a1510;
            --ink-soft: #4a4035;
            --rule: #c8b89a;
            --rule-dark: #8a7660;
            --cream: #fdf8f0;
            --accent: #8b1a1a;
            --gold: #c49a2a;
            --bg: #f5ede0;
            --cell-bg: #fefaf4;
            --font-body: 'EB Garamond', Georgia, serif;
            --font-mono: 'DM Mono', 'Courier New', monospace;
        }

        body {
            font-family: var(--font-body);
            font-size: 14px;
            color: var(--ink);
            background: var(--bg);
            padding: 20px 12px 40px;
            min-height: 100vh;
        }

        .action-bar {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 12px;
            max-width: 780px;
            margin: 0 auto 14px;
        }

        .btn-download {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: var(--accent);
            color: #fff;
            font-family: var(--font-mono);
            font-size: 11px;
            letter-spacing: .06em;
            text-transform: uppercase;
            padding: 8px 18px;
            border: none;
            cursor: pointer;
            border-radius: 2px;
            transition: background .2s, transform .1s;
            box-shadow: 0 2px 8px rgba(139, 26, 26, .25);
        }

        .btn-download:hover {
            background: #6e1414;
            transform: translateY(-1px);
        }

        .btn-download:active {
            transform: translateY(0);
        }

        .btn-download svg {
            flex-shrink: 0;
        }

        .invoice {
            max-width: 780px;
            margin: 0 auto;
            background: var(--cream);
            border: 1.5px solid var(--rule-dark);
            border-radius: 3px;
            overflow: hidden;
            box-shadow: 0 4px 32px rgba(100, 70, 20, .13), 0 1px 4px rgba(100, 70, 20, .08);
        }

        .band-top {
            height: 5px;
            background: repeating-linear-gradient(90deg, var(--accent) 0, var(--accent) 60px, var(--gold) 60px, var(--gold) 70px, var(--accent) 70px, var(--accent) 130px, transparent 130px, transparent 134px);
        }

        .gstin-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 7px 16px;
            border-bottom: 1px solid var(--rule);
            background: #fef6e8;
        }

        .gstin-row .gstin {
            font-family: var(--font-mono);
            font-size: 11.5px;
            letter-spacing: .03em;
            color: var(--ink-soft);
        }

        .gstin-row .label-pi {
            font-style: italic;
            font-size: 12.5px;
            color: var(--accent);
            letter-spacing: .04em;
        }

        .company-header {
            text-align: center;
            padding: 18px 16px 14px;
            border-bottom: 1.5px double var(--rule-dark);
            background: var(--cream);
            position: relative;
        }

        .company-header::before,
        .company-header::after {
            content: '◆';
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gold);
            font-size: 18px;
            opacity: .5;
        }

        .company-header::before {
            left: 16px;
        }

        .company-header::after {
            right: 16px;
        }

        .doc-type {
            font-family: var(--font-mono);
            font-size: 10px;
            letter-spacing: .18em;
            text-transform: uppercase;
            color: var(--accent);
            margin-bottom: 4px;
        }

        .company-name {
            font-size: 36px;
            font-weight: 700;
            letter-spacing: .12em;
            color: var(--ink);
            line-height: 1;
            margin-bottom: 6px;
        }

        .company-address {
            font-size: 12px;
            color: var(--ink-soft);
            margin-bottom: 2px;
        }

        .company-contact {
            font-size: 12px;
            color: var(--ink-soft);
        }

        .company-email {
            font-family: var(--font-mono);
            font-size: 11px;
            color: var(--ink-soft);
            margin-top: 3px;
        }

        .party-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            border-bottom: 1px solid var(--rule);
        }

        @media (max-width: 560px) {
            .party-meta {
                grid-template-columns: 1fr;
            }

            .party-block {
                border-right: none !important;
                border-bottom: 1px solid var(--rule);
            }
        }

        .party-block {
            padding: 12px 16px;
            border-right: 1px solid var(--rule);
        }

        .section-label {
            font-family: var(--font-mono);
            font-size: 10px;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: var(--accent);
            margin-bottom: 6px;
            padding-bottom: 4px;
            border-bottom: 1px solid var(--rule);
        }

        .party-block .party-name {
            font-weight: 600;
            font-size: 13.5px;
            margin-bottom: 3px;
        }

        .party-block .party-address {
            font-size: 12px;
            color: var(--ink-soft);
            line-height: 1.5;
        }

        .party-block .gstin-info {
            font-family: var(--font-mono);
            font-size: 11px;
            margin-top: 8px;
            color: var(--ink-soft);
        }

        .meta-block {
            padding: 12px 16px;
        }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
        }

        .meta-table td {
            padding: 3px 2px;
            font-size: 12.5px;
            vertical-align: top;
        }

        .meta-table .mk {
            color: var(--ink-soft);
            white-space: nowrap;
            width: 90px;
        }

        .meta-table .mc {
            padding: 0 6px;
            color: var(--rule-dark);
        }

        .meta-table .mv {
            font-family: var(--font-mono);
            font-size: 11.5px;
        }

        .items-wrap {
            border-bottom: 1px solid var(--rule);
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .items-table {
            width: 100%;
            min-width: 560px;
            border-collapse: collapse;
            font-size: 12px;
        }

        .items-table th {
            background: #f0e6d3;
            border-bottom: 1.5px solid var(--rule-dark);
            border-right: 1px solid var(--rule);
            padding: 7px 6px;
            font-family: var(--font-mono);
            font-size: 10px;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--ink);
            text-align: center;
            white-space: nowrap;
        }

        .items-table th:last-child {
            border-right: none;
        }

        .items-table th.left {
            text-align: left;
        }

        .items-table tbody tr {
            border-bottom: 1px solid #e8dccb;
        }

        .items-table tbody tr:nth-child(even) {
            background: #faf4ea;
        }

        .items-table tbody td {
            padding: 7px 6px;
            border-left: 1px solid #e8dccb;
            border-right: 1px solid #e8dccb;
            vertical-align: top;
            color: var(--ink);
        }

        .items-table tbody td:last-child {
            border-right: none;
        }

        .items-table td.center {
            text-align: center;
        }

        .items-table td.right {
            text-align: right;
            font-family: var(--font-mono);
            font-size: 11.5px;
        }

        .items-table td.sn {
            font-family: var(--font-mono);
            font-size: 11px;
            text-align: center;
            color: var(--ink-soft);
        }

        .items-table td.desc .desc-em {
            font-style: italic;
            font-size: 11px;
            color: var(--ink-soft);
            display: block;
            margin-top: 2px;
        }

        .items-table td.hsn {
            font-family: var(--font-mono);
            font-size: 11px;
            text-align: center;
            color: var(--ink-soft);
        }

        .items-table .empty-row td {
            height: 26px;
            background: var(--cell-bg);
        }

        .grand-total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 16px;
            border-bottom: 1px solid var(--rule);
            background: #f0e6d3;
        }

        .gt-label {
            font-weight: 700;
            font-size: 13px;
            letter-spacing: .04em;
        }

        .gt-qty {
            font-family: var(--font-mono);
            font-size: 12px;
            color: var(--ink-soft);
        }

        .gt-amount {
            font-family: var(--font-mono);
            font-weight: 600;
            font-size: 14px;
            color: var(--accent);
            border-left: 1px solid var(--rule-dark);
            padding-left: 16px;
            min-width: 90px;
            text-align: right;
        }

        .tax-summary {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            padding: 8px 16px;
            border-bottom: 1px solid var(--rule);
            background: #faf4ea;
            font-size: 12px;
        }

        .ts-head {
            font-family: var(--font-mono);
            font-size: 10px;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--accent);
            margin-bottom: 3px;
        }

        .ts-col span {
            display: block;
            font-family: var(--font-mono);
            font-size: 11.5px;
            color: var(--ink-soft);
        }

        .amount-words {
            padding: 9px 16px;
            border-bottom: 1px solid var(--rule);
            font-size: 13px;
            font-style: italic;
            background: #fef6e8;
        }

        .amount-words strong {
            font-style: normal;
            color: var(--accent);
        }

        .declaration {
            padding: 12px 16px;
            text-align: center;
            border-bottom: 1px solid var(--rule);
            background: var(--cream);
        }

        .decl-title {
            font-family: var(--font-mono);
            font-size: 10px;
            letter-spacing: .16em;
            text-transform: uppercase;
            color: var(--accent);
            margin-bottom: 6px;
        }

        .decl-ornament {
            color: var(--gold);
            letter-spacing: .3em;
            margin-bottom: 6px;
            font-size: 12px;
        }

        .bank-details {
            font-size: 12.5px;
            line-height: 1.7;
            color: var(--ink-soft);
        }

        .bank-details strong {
            color: var(--ink);
        }

        .bank-acc {
            font-family: var(--font-mono);
            font-size: 11.5px;
            background: #f0e6d3;
            display: inline-block;
            padding: 3px 10px;
            border-radius: 2px;
            margin-top: 4px;
            letter-spacing: .03em;
        }

        .footer-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
        }

        @media (max-width: 500px) {
            .footer-row {
                grid-template-columns: 1fr;
            }

            .terms-block {
                border-right: none !important;
                border-bottom: 1px solid var(--rule);
            }
        }

        .terms-block {
            padding: 12px 16px;
            border-right: 1px solid var(--rule);
        }

        .terms-title {
            font-family: var(--font-mono);
            font-size: 10px;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: var(--accent);
            margin-bottom: 8px;
            padding-bottom: 4px;
            border-bottom: 1px solid var(--rule);
        }

        .terms-block ol {
            padding-left: 16px;
        }

        .terms-block li {
            font-size: 12px;
            color: var(--ink-soft);
            line-height: 1.7;
        }

        .sig-block {
            padding: 12px 16px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 110px;
        }

        .receiver-row {
            font-size: 12px;
            color: var(--ink-soft);
        }

        .for-company {
            font-size: 15px;
            font-weight: 700;
            text-align: right;
            margin-top: auto;
            letter-spacing: .06em;
        }

        .auth-sig {
            font-family: var(--font-mono);
            font-size: 10.5px;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--ink-soft);
            text-align: right;
            margin-top: 26px;
        }

        .band-bottom {
            height: 5px;
            background: repeating-linear-gradient(90deg, var(--accent) 0, var(--accent) 60px, var(--gold) 60px, var(--gold) 70px, var(--accent) 70px, var(--accent) 130px, transparent 130px, transparent 134px);
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }

            .action-bar {
                display: none !important;
            }

            .invoice {
                box-shadow: none;
                border: 1px solid #aaa;
                max-width: 100%;
            }

            @page {
                size: A4 portrait;
                margin: 8mm;
            }
        }
    </style>
</head>

<body>

    <div class="action-bar">
        <button class="btn-download" id="downloadBtn">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                <polyline points="7 10 12 15 17 10" />
                <line x1="12" y1="15" x2="12" y2="3" />
            </svg>
            Download PDF
        </button>
    </div>

    <div class="invoice" id="invoice">
        <div class="band-top"></div>

        <div class="gstin-row">
            <span class="gstin">GSTIN&nbsp;:&nbsp;07AAAFL2632B1ZZ</span>
            <span class="label-pi">Proforma Invoice</span>
        </div>

        <div class="company-header">
            <div class="doc-type">Proforma Invoice</div>
            <div class="company-name">MOROVSKI</div>
            <div class="company-address">1568-A, Bhagirath Place, Chandni Chowk, Delhi-6</div>
            <div class="company-contact">+91 9810887872 &nbsp;&middot;&nbsp; +91 9899583058</div>
            <div class="company-email">Tel: 011-45656342 &nbsp;&middot;&nbsp; rajlitezone@gmail.com</div>
        </div>

        <div class="party-meta">
            <div class="party-block">
                <div class="section-label">Bill To</div>
                <div class="party-name">{{ $invoice->client->full_name }} &nbsp;&middot;&nbsp;
                    {{ $invoice->client->phone }}
                </div>
                @if ($invoice->client->shippingAddresses)
                    @php
                        $address = $invoice->client->shippingAddresses;
                    @endphp

                    <div class="party-address">
                        {{ $address[0]->address_line_1 ?? '' }},
                        {{ $address[0]->address_line_2 ?? '' }}<br>

                        {{ $address[0]->city ?? '' }},
                        {{ $address[0]->state ?? '' }} -
                        {{ $address[0]->postal_code ?? '' }}<br>

                        {{ $address[0]->country ?? '' }}<br>

                        Phone: {{ $address[0]->phone ?? '' }}
                    </div>
                @endif
                @if ($invoice->client->gstin)
                    <div class="gstin-info">GSTIN/UIN&nbsp;:&nbsp;{{ $invoice->client->gstin }}</div>
                @endif
            </div>
            <div class="meta-block">
                <div class="section-label">Invoice Details</div>
                <table class="meta-table">
                    <tbody>
                        <tr>
                            <td class="mk">Invoice No.</td>
                            <td class="mc">:</td>
                            <td class="mv">{{ $invoice->invoice_number }}</td>
                        </tr>
                        <tr>
                            <td class="mk">Dated</td>
                            <td class="mc">:</td>
                            <td class="mv">{{ $invoice->created_at->format('d-m-Y') }} &nbsp;
                                {{ $invoice->created_at->format('h:i A') }}</td>
                        </tr>
                        <tr>
                            <td class="mk">Order No.</td>
                            <td class="mc">:</td>
                            <td class="mv">{{ $invoice->order_id }}</td>
                        </tr>
                        <tr>
                            <td class="mk">Transport</td>
                            <td class="mc">:</td>
                            <td class="mv">{{ $invoice->orders->orderDetail->transport_mode ?? 'Road' }}</td>
                        </tr>
                        <tr>
                            <td class="mk">Valid Until</td>
                            <td class="mc">:</td>
                            <td class="mv">{{ $invoice->valid_until }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="items-wrap">
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width:32px">S.N.</th>
                        <th class="left" style="min-width:160px">Description of Goods</th>
                        <th style="width:52px">Qty.</th>
                        <th style="width:42px">Unit</th>
                        <th style="width:70px">Price (&#8377;)</th>
                        <th style="width:80px">Amount (&#8377;)</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalQuantity = 0; @endphp
                    @forelse($invoice->items as $index => $item)
                        @php
                            $totalQuantity += $item->quantity;
                            $lineTotal = $item->quantity * $item->unit_price;
                        @endphp
                        <tr>
                            <td class="sn">{{ $index + 1 }}.</td>
                            <td class="desc">
                                {{ $item->item->name }}
                                @if ($item->description)
                                    <span class="desc-em">{{ $item->description }}</span>
                                @endif
                            </td>
                            <td class="right">{{ number_format($item->quantity, 3) }}</td>
                            <td class="right">{{ $item->unit ?? 'PCS' }}</td>
                            <td class="right">{{ number_format($item->unit_price, 2) }}</td>
                            <td class="right">{{ number_format($lineTotal, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td class="sn">-</td>
                            <td colspan="6" class="center">No items found</td>
                        </tr>
                    @endforelse
                    @for ($i = 0; $i < max(0, 10 - count($invoice->items)); $i++)
                        <tr class="empty-row">
                            <td>&nbsp;</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <!--<td></td>-->
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>

        <div class="grand-total-row">
            <span class="gt-label">Grand Total</span>
            <span class="gt-amount">&#8377;{{ number_format($invoice->total_amount, 2) }}</span>
        </div>

        <div class="tax-summary">
            <div class="ts-col">
                <div class="ts-head">&nbsp;</div>
            </div>
        </div>

        @php
            $formatter = new NumberFormatter('en_IN', NumberFormatter::SPELLOUT);

            $amountInWords = ucwords($formatter->format($invoice->total_amount));
        @endphp

        <div class="amount-words">
            <strong>Rupees</strong> {{ $amountInWords }} Only
        </div>

        <div class="declaration">
            <div class="decl-title">Declaration &amp; Bank Details</div>
            <div class="decl-ornament">&#8212; &#9670; &#8212;</div>
            <div class="bank-details">
                <strong>Bank:</strong> HDFC Bank &nbsp;&middot;&nbsp;
                <strong>Branch:</strong> Chandni Chowk, Delhi-06<br>
                <div class="bank-acc">A/C No: 50200051568330 &nbsp;|&nbsp; IFSC: HDFC0000553</div>
            </div>
            @if ($invoice->payment_terms)
                <div style="margin-top:6px;font-size:12px;color:var(--ink-soft);">
                    <strong>Payment Terms:</strong> {{ $invoice->payment_terms }}
                </div>
            @endif
        </div>

        <div class="footer-row">
            <div class="terms-block">
                <a href="https://www.markupdesigns.net/morovski-light-web/privacy-policy/" class="terms-title">Terms
                    &amp; Conditions</a>
                <ol>
                    <li>Goods once sold are non-returnable and non-refundable only exchangable.</li>
                    <li>100% Payment Advance with PO.</li>
                    <li>Disputes subject to Delhi jurisdiction only.</li>
                    <li>This is a system generated proforma invoice.</li>
                </ol>
            </div>
            <div class="sig-block">
                <div class="receiver-row">Receiver's Signature &nbsp;:&nbsp; ___________________</div>
                <div>
                    <div class="for-company">For Morovski</div>
                    <div class="auth-sig">Authorised Signatory</div>
                </div>
            </div>
        </div>

        <div class="band-bottom"></div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        document.getElementById('downloadBtn').addEventListener('click', function() {
            var btn = this;
            btn.disabled = true;
            btn.textContent = 'Generating\u2026';
            var el = document.getElementById('invoice');
            var opt = {
                margin: [6, 6, 6, 6],
                filename: 'MOROVSKI_Proforma_Invoice.pdf',
                image: {
                    type: 'jpeg',
                    quality: 0.97
                },
                html2canvas: {
                    scale: 2,
                    useCORS: true,
                    letterRendering: true
                },
                jsPDF: {
                    unit: 'mm',
                    format: 'a4',
                    orientation: 'portrait'
                },
                pagebreak: {
                    mode: ['avoid-all', 'css', 'legacy']
                }
            };
            html2pdf().set(opt).from(el).save().then(function() {
                btn.disabled = false;
                btn.innerHTML =
                    '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg> Download PDF';
            });
        });
    </script>

</body>

</html>
