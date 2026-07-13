{{-- resources/views/pdf/products.blade.php --}}
<!-- ==================== PAGE : PRODUCTS ==================== -->
<div class="page">
    <div class="topbar">
        <span class="date">{{ $generatedDate ?? now()->format('d M Y H:i') }}</span>
        <span class="pageno">Catalogue · Page {{ $pageNumber ?? 2 }}</span>
    </div>

    <div
        class="products-main @if (!($showPrice ?? true)) hide-price @endif @if (!($showDescription ?? false)) hide-description @endif">
        <div class="products-head">
            <div>
                <div class="cover-eyebrow">Collection</div>
                <h2 class="products-heading">Selected Works</h2>
            </div>
            <div class="products-count">
                {{ $totalItems ?? 0 }} products
                @if ($showPrice ?? true)
                    <br>prices included
                @endif
            </div>
        </div>

        <div class="product-grid">
            @forelse($items ?? [] as $item)
                <div class="product-card">
                    <div class="product-category">
                        {{ $item->category->name ?? 'Uncategorized' }}
                    </div>
                    <div class="product-image">
                        @if ($item->images->isNotEmpty() && !empty($item->images->first()->base64))
                            <img src="{{ $item->images->first()->base64 }}"
                                  alt="{{ $item->name }}">
                        @else
                            <span class="no-image">No Image</span>
                        @endif
                    </div>
                    <div class="product-info">
                        <div class="product-sku">{{ $item->sku ?? 'N/A' }}</div>
                        <div class="product-name">{{ $item->name }}</div>
                        @if ($showDescription ?? false)
                            <p class="product-description">{{ $item->description ?? '' }}</p>
                        @endif
                        <div class="product-bottom">
                            @if ($showPrice ?? true)
                                <div class="product-price">
                                    <span class="currency">{{ config('app.currency_symbol', '₹') }}</span>
                                    {{ number_format($item->price ?? 0) }}
                                </div>
                            @endif
                            @php
                                $available = ($item->quantity ?? 0) - ($item->damaged_quantity ?? 0);
                                $stockClass = $available <= 0 ? 'out' : ($available < 5 ? 'low' : 'ok');
                                $stockLabel =
                                    $available <= 0 ? 'Out of Stock' : ($available < 5 ? 'Low Stock' : 'In Stock');
                            @endphp
                            <div class="stock-badge {{ $stockClass }}">{{ $stockLabel }}</div>
                        </div>
                    </div>
                </div>
            @empty
                <p style="grid-column:1/-1;text-align:center;padding:40px;color:var(--muted);">No products selected.</p>
            @endforelse
        </div>
    </div>

    <div class="pagefooter">
        <span class="brand">© {{ date('Y') }} Morovski Lighting Pvt. Ltd.</span>
        <span>All Rights Reserved</span>
    </div>
</div>