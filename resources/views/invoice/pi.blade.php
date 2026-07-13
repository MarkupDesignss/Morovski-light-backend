<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Proforma Invoice | Morovski</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #e9ecef;
            font-family: 'Inter', 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            padding: 40px 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        /* Premium invoice card */
        .invoice-container {
            max-width: 1000px;
            width: 100%;
            background: #ffffff;
            border-radius: 28px;
            box-shadow: 0 20px 35px -12px rgba(0, 0, 0, 0.15), 0 1px 3px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            transition: all 0.2s ease;
        }

        /* inner padding */
        .invoice-inner {
            padding: 2rem 2rem 2.2rem 2rem;
        }

        /* ----- LOGO SECTION: top middle ----- */
        .logo-area {
            text-align: center;
            margin-bottom: 1.8rem;
            border-bottom: 2px solid #f0f2f5;
            padding-bottom: 1.5rem;
        }

        .logo-image {
            max-width: 180px;
            height: auto;
            display: inline-block;
            filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.03));
            transition: transform 0.2s;
        }

        .logo-image:hover {
            transform: scale(1.01);
        }

        /* invoice header (title + invoice number + client) */
        .invoice-header {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 2rem;
            gap: 1rem;
        }

        .title-section h2 {
            font-size: 1.9rem;
            font-weight: 700;
            letter-spacing: -0.3px;
            background: linear-gradient(135deg, #1e2a3a, #0f172a);
            background-clip: text;
            -webkit-background-clip: text;
            color: transparent;
            margin-bottom: 0.4rem;
        }

        .title-section p {
            color: #4b5563;
            font-size: 0.85rem;
            font-weight: 500;
            letter-spacing: 0.3px;
        }

        .details-panel {
            background: #f8fafc;
            padding: 0.9rem 1.5rem;
            border-radius: 20px;
            text-align: right;
            border: 1px solid #eef2f6;
        }

        .details-panel p {
            margin: 0.25rem 0;
            font-size: 0.9rem;
        }

        .details-panel strong {
            color: #0f2b3d;
            font-weight: 600;
        }

        .client-row {
            background: #fefce8;
            border-left: 5px solid #ca8a04;
            padding: 0.9rem 1.3rem;
            border-radius: 18px;
            margin-bottom: 2rem;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }

        .client-label {
            font-weight: 700;
            color: #854d0e;
            background: #fffbeb;
            padding: 0.2rem 0.8rem;
            border-radius: 40px;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .client-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1e293b;
            word-break: break-word;
        }

        /* modern table design */
        .invoice-table-wrapper {
            overflow-x: auto;
            margin: 1.8rem 0 1.8rem 0;
            border-radius: 20px;
            border: 1px solid #edf2f7;
            background: #fff;
        }

        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
            min-width: 400px;
        }

        .invoice-table th {
            background: #f1f5f9;
            color: #1e293b;
            font-weight: 600;
            padding: 1rem 1rem;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
            font-size: 0.85rem;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }

        .invoice-table td {
            padding: 1rem 1rem;
            border-bottom: 1px solid #f0f2f5;
            color: #2d3a48;
            font-weight: 500;
        }

        .invoice-table tr:last-child td {
            border-bottom: none;
        }

        /* total section */
        .total-section {
            display: flex;
            justify-content: flex-end;
            margin-top: 1rem;
            margin-bottom: 1.2rem;
        }

        .total-card {
            background: #f9fbfd;
            border-radius: 24px;
            padding: 1rem 2rem;
            text-align: right;
            border: 1px solid #eef2f8;
            min-width: 240px;
        }

        .total-amount {
            font-size: 1.9rem;
            font-weight: 800;
            color: #0b3b3f;
            letter-spacing: -0.5px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .total-amount span {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2c6e6b;
        }

        .hr-light {
            margin: 12px 0 8px;
            border: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, #cbd5e1, transparent);
        }

        /* footer / thank you */
        .footer-note {
            margin-top: 2rem;
            text-align: center;
            font-size: 0.75rem;
            color: #6c757d;
            border-top: 1px dashed #e2e8f0;
            padding-top: 1.5rem;
        }

        /* responsive touches */
        @media (max-width: 640px) {
            .invoice-inner {
                padding: 1.2rem;
            }

            .total-amount {
                font-size: 1.5rem;
            }

            .client-name {
                font-size: 0.95rem;
            }

            .title-section h2 {
                font-size: 1.5rem;
            }
        }

        /* subtle badge for item name fallback */
        .item-fallback {
            font-weight: 500;
        }
    </style>
</head>

<body>
    <div class="invoice-container">
        <div class="invoice-inner">
            <!-- Logo at the top MIDDLE -->
            <div class="logo-area">
                <img class="logo-image" src="https://www.markupdesigns.net/morovski-light/logo/MORVOSKI-logo.png"
                    alt="Morovski brand logo"
                    onerror="this.onerror=null; this.style.opacity='0.6'; this.alt='Logo unavailable';">
            </div>

            <!-- HEADER SECTION: Invoice title + invoice number (right) -->
            <div class="invoice-header">
                <div class="title-section">
                    <h2>Proforma Invoice</h2>
                    <p>Tax document · commercial reference</p>
                </div>
                <div class="details-panel">
                    <p><strong>Invoice No.</strong> <span>{{ $invoice->invoice_number ?? '—' }}</span></p>
                    <p><strong>Issue date:</strong>
                        <span>{{ $invoice->created_at ? \Carbon\Carbon::parse($invoice->created_at)->format('d M Y') : now()->format('d M Y') }}</span>
                    </p>
                    <p><strong>Valid until:</strong>
                        <span>{{ $invoice->valid_until ? \Carbon\Carbon::parse($invoice->valid_until)->format('d M Y') : '—' }}</span>
                    </p>
                </div>
            </div>

            <!-- CLIENT INFO (dynamic from dummy context) -->
            <div class="client-row">
                <span class="client-label">BILL TO</span>
                <span class="client-name"
                    id="clientFullName">{{ optional($invoice->order->user)->full_name ?? ($invoice->client_name ?? 'Client Name') }}</span>
            </div>

            <!-- ITEMS TABLE (dynamic data using javascript to reflect live data) -->
            <div class="invoice-table-wrapper">
                <table class="invoice-table" id="itemsTable">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Qty</th>
                            <th>Unit Price (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoice->items as $item)
                            @php
                                $productName =
                                    $item->orderItem->product_name ?? ($item->product_name ?? ($item->name ?? 'Item'));
                                $quantity = $item->quantity ?? 0;
                                $lineTotal = $item->unit_price ?? 0;
                            @endphp
                            <tr>
                                <td style="font-weight:500;">{{ $productName }}</td>
                                <td>{{ $quantity }}</td>
                                <td style="font-weight:600; color:#2c5f5a;">₹ {{ number_format($lineTotal, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" style="text-align:center; padding:30px;">No items available</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- TOTAL AMOUNT section -->
            <div class="total-section">
                <div class="total-card">
                    <div style="font-size:0.85rem; font-weight:500; color:#4b5563;">GRAND TOTAL </div>
                    <small>Includes GST and shipping charges</small>
                    <div class="total-amount">
                        <span>₹</span>
                        <span>
                            {{ number_format($invoice->total_amount ?? ($invoice->items->sum('total_price') ?? 0), 2) }}
                        </span>
                    </div>
                    <div class="hr-light"></div>
                    <div style="font-size:0.85rem; color:#4b5563; margin-bottom:6px;">Paid: ₹
                        {{ number_format($invoice->amount_paid ?? 0, 2) }}</div>
                    <div style="font-size:0.85rem; color:#6c7a8e;">Due: ₹
                        {{ number_format(($invoice->total_amount ?? ($invoice->items->sum('total_price') ?? 0)) - ($invoice->amount_paid ?? 0), 2) }}
                    </div>
                </div>
            </div>

          
        </div>
    </div>

    <script>
        // --------------------------------------------------------------
        // MOCK DATA STRUCTURE reflecting the blade context:
        // It simulates $invoice object with invoice_number, order.user.full_name,
        // items array (each item contains orderItem.product_name, quantity, total_price)
        // and total_amount.
        // --------------------------------------------------------------
        const mockInvoice = {
            invoice_number: "PI-2412-0987",
            order: {
                user: {
                    full_name: "Ms. Aarna Mehra"
                }
            },
            items: [{
                    orderItem: {
                        product_name: "Morovski Artisan Desk Lamp"
                    },
                    quantity: 2,
                    total_price: 4590.00
                },
                {
                    orderItem: {
                        product_name: "Minimalist Leather Journal"
                    },
                    quantity: 5,
                    total_price: 2450.00
                },
                {
                    orderItem: {
                        product_name: "Wireless Ergonomic Mouse"
                    },
                    quantity: 1,
                    total_price: 1899.00
                },
                {
                    orderItem: {
                        product_name: "Morovski Signature Pen Set"
                    },
                    quantity: 3,
                    total_price: 2700.00
                }
            ],
            total_amount: 11639.00 // sum of above: 4590+2450+1899+2700 = 11639
        };

        // Alternative scenario: if you prefer to play with live editing via console,
        // the UI completely relies on this data. You can also override with another demo.
        // Additionally, note: the template originally had $invoice->items etc.
        // For a fully functional preview, we map exactly matching fields.

        // Helper: format price with two decimals and Indian number style (simple)
        function formatPrice(value) {
            if (value === undefined || value === null) return "0.00";
            let num = parseFloat(value);
            if (isNaN(num)) return "0.00";
            return num.toFixed(2);
        }

        // Render the invoice completely from 'invoiceData' object
        function renderInvoice(invoiceData) {
            // 1) Invoice number & date (date fallback: current date)
            const invoiceNumberElem = document.getElementById("invoiceNumberDisplay");
            if (invoiceNumberElem) {
                invoiceNumberElem.textContent = invoiceData.invoice_number || "PI-0000";
            }
            const issueDateSpan = document.getElementById("issueDateFallback");
            if (issueDateSpan) {
                const today = new Date();
                const formattedDate = today.toLocaleDateString('en-IN', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                });
                issueDateSpan.textContent = formattedDate;
            }

            // 2) Client full name (nested order.user.full_name)
            let clientName = "Client not specified";
            if (invoiceData.order && invoiceData.order.user && invoiceData.order.user.full_name) {
                clientName = invoiceData.order.user.full_name;
            } else if (invoiceData.client_name) {
                // fallback if we eventually extend
                clientName = invoiceData.client_name;
            }
            const clientNameSpan = document.getElementById("clientFullName");
            if (clientNameSpan) clientNameSpan.textContent = clientName;

            // 3) Populate items table
            const itemsBody = document.getElementById("invoiceItemsBody");
            if (!itemsBody) return;

            // clear previous rows (except loading)
            itemsBody.innerHTML = "";

            const itemsArray = invoiceData.items || [];
            if (itemsArray.length === 0) {
                // show empty row message
                const emptyRow = document.createElement("tr");
                emptyRow.innerHTML = `<td colspan="3" style="text-align:center; padding:30px;">No items available</td>`;
                itemsBody.appendChild(emptyRow);
            } else {
                itemsArray.forEach(item => {
                    // item.orderItem.product_name ?? 'Item'
                    let productName = "Item";
                    if (item.orderItem && item.orderItem.product_name) {
                        productName = item.orderItem.product_name;
                    } else if (item.product_name) {
                        productName = item.product_name; // flexible
                    } else if (item.name) {
                        productName = item.name;
                    }

                    const quantity = (item.quantity !== undefined && item.quantity !== null) ? item.quantity : 0;
                    let totalPrice = 0;
                    if (item.total_price !== undefined && item.total_price !== null) {
                        totalPrice = parseFloat(item.total_price);
                        if (isNaN(totalPrice)) totalPrice = 0;
                    }

                    const row = document.createElement("tr");
                    // product name cell
                    const tdName = document.createElement("td");
                    tdName.textContent = productName;
                    tdName.style.fontWeight = "500";
                    // qty cell
                    const tdQty = document.createElement("td");
                    tdQty.textContent = quantity;
                    // price cell
                    const tdPrice = document.createElement("td");
                    tdPrice.textContent = `₹ ${formatPrice(totalPrice)}`;
                    tdPrice.style.fontWeight = "600";
                    tdPrice.style.color = "#2c5f5a";

                    row.appendChild(tdName);
                    row.appendChild(tdQty);
                    row.appendChild(tdPrice);
                    itemsBody.appendChild(row);
                });
            }

            // 4) Total amount display
            let totalAmount = 0;
            if (invoiceData.total_amount !== undefined && invoiceData.total_amount !== null) {
                totalAmount = parseFloat(invoiceData.total_amount);
                if (isNaN(totalAmount)) totalAmount = 0;
            } else {
                // fallback: calculate from items if total_amount missing (optional)
                let calculatedTotal = 0;
                itemsArray.forEach(it => {
                    let val = parseFloat(it.total_price);
                    if (!isNaN(val)) calculatedTotal += val;
                });
                totalAmount = calculatedTotal;
            }

            const totalSpan = document.getElementById("totalAmountValue");
            if (totalSpan) {
                totalSpan.textContent = formatPrice(totalAmount);
            }
        }

        // Initial render using mock data (faithful to the original Laravel style)
        renderInvoice(mockInvoice);

        // For advanced demonstration: allow live update from a simulated "order context"
        // but keeping exactly the structure requested. In case you want to showcase dynamic,
        // you can uncomment/override. Also, we respect that logo is fixed top-middle.
        // Optional: additional demo that reveals how it works from console.
        console.log("Proforma Invoice Loaded — Logo displayed in top middle, elegant style applied.");

        // you can replace data on window for testing:
        window.updateInvoicePreview = function(newInvoiceData) {
            if (newInvoiceData && typeof newInvoiceData === 'object') {
                renderInvoice(newInvoiceData);
            } else {
                console.warn(
                    "Provide an invoice object with invoice_number, order.user.full_name, items, total_amount");
            }
        };

        // Extra: if you’d like to simulate a sample with different client/items just to see responsiveness.
        // Not required but nice for devs.
        // Example on console: updateInvoicePreview({ invoice_number: "PRO-999", order: { user: { full_name: "Rohan Das" } }, items: [{ orderItem: { product_name: "Premium Headphones" }, quantity: 1, total_price: 3999 }], total_amount: 3999 })

        // Ensure that even if the provided image link fails due to network, style remains consistent.
        // Already handled with onerror fallback in img tag (opacity style).
    </script>

    <!-- optional note: The blade original syntax used $invoice->items and $invoice->order->user->full_name.
     This HTML + JS fully emulates the same object schema using mockInvoice.
     The logo is placed exactly top middle with spacing and refined style.
     Table design upgraded with modern border-radius, soft shadows, and total section.
     Also mobile responsive -->
</body>

</html>
