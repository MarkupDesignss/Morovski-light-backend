@extends('layouts.admin')

@section('content')
    <div class="container py-4">

        <div class="row g-4">

            {{-- LEFT COLUMN: Ticket Details & Images --}}
            <div class="col-lg-8">

                {{-- Ticket Details Card --}}
                <div class="card shadow-sm border-0 mb-4" style="background: #ffffff; border-radius: 20px; overflow: hidden;">
                    <div class="card-header"
                        style="background: #f8f9fa; border-bottom: 1px solid #e9ecef; padding: 1.25rem 1.5rem;">
                        <h4 class="mb-0" style="color: #2c3e50; font-weight: 600;">
                            Ticket #{{ $ticket->id }}
                        </h4>
                    </div>

                    <div class="card-body" style="padding: 1.75rem;">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div style="margin-bottom: 0.5rem;">
                                    <strong style="color: #495057; font-weight: 500; letter-spacing: 0.3px;">Customer
                                        Name</strong>
                                </div>
                                <p
                                    style="color: #212529; background: #f8f9fa; padding: 0.75rem 1rem; border-radius: 12px; margin: 0; border-left: 3px solid #0d6efd;">
                                    {{ $ticket->full_name }}
                                </p>
                            </div>

                            <div class="col-md-6">
                                <div style="margin-bottom: 0.5rem;">
                                    <strong style="color: #495057; font-weight: 500; letter-spacing: 0.3px;">Email</strong>
                                </div>
                                <p
                                    style="color: #212529; background: #f8f9fa; padding: 0.75rem 1rem; border-radius: 12px; margin: 0;">
                                    {{ $ticket->email }}
                                </p>
                            </div>

                            <div class="col-md-6">
                                <div style="margin-bottom: 0.5rem;">
                                    <strong style="color: #495057; font-weight: 500; letter-spacing: 0.3px;">Order
                                        Number</strong>
                                </div>
                                <p
                                    style="color: #212529; background: #f8f9fa; padding: 0.75rem 1rem; border-radius: 12px; margin: 0; font-family: monospace;">
                                    {{ $ticket->order_number }}
                                </p>
                            </div>

                            <div class="col-md-6">
                                <div style="margin-bottom: 0.5rem;">
                                    <strong style="color: #495057; font-weight: 500; letter-spacing: 0.3px;">Query
                                        Type</strong>
                                </div>
                                <p style="margin: 0;">
                                    <span
                                        style="display: inline-block; background: #e9ecef; color: #495057; padding: 0.5rem 1rem; border-radius: 40px; font-size: 0.875rem;">
                                        {{ ucwords(str_replace('_', ' ', $ticket->query_type)) }}
                                    </span>
                                </p>
                            </div>

                            <!-- Status section is commented out in original, kept as is -->
                        </div>

                        <div style="margin-top: 1.75rem;">
                            <div style="margin-bottom: 0.75rem;">
                                <strong style="color: #495057; font-weight: 500; letter-spacing: 0.3px;">Message</strong>
                            </div>
                            <div
                                style="background: #f8f9fa; padding: 1.25rem 1.5rem; border-radius: 16px; color: #212529; line-height: 1.7;">
                                {{ $ticket->message }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Ticket Images Card --}}
                <div class="card shadow-sm border-0" style="background: #ffffff; border-radius: 20px; overflow: hidden;">
                    <div class="card-header"
                        style="background: #f8f9fa; border-bottom: 1px solid #e9ecef; padding: 1rem 1.5rem;">
                        <h5 class="mb-0" style="color: #2c3e50; font-weight: 500;">Uploaded Images</h5>
                    </div>

                    <div class="card-body" style="padding: 1.5rem;">
                        <div class="row g-3">
                            @forelse($ticket->images as $image)
                                <div class="col-md-4">
                                    <a href="{{ asset('storage/' . $image->image_path) }}" target="_blank"
                                        style="display: block; overflow: hidden; border-radius: 16px; transition: transform 0.2s ease;">
                                        <img src="{{ asset('storage/' . $image->image_path) }}" class="img-fluid"
                                            style="height: 200px; width: 100%; object-fit: cover; border: 1px solid #dee2e6; border-radius: 16px; transition: opacity 0.2s ease;"
                                            onmouseover="this.style.opacity='0.85'" onmouseout="this.style.opacity='1'">
                                    </a>
                                </div>
                            @empty
                                <div class="col-12">
                                    <p
                                        style="color: #6c757d; text-align: center; margin: 0; padding: 2rem; background: #f8f9fa; border-radius: 16px;">
                                        No images uploaded.
                                    </p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

            </div>

            {{-- RIGHT COLUMN: Order Items --}}
            <div class="col-lg-4">
                <div class="card shadow-sm border-0"
                    style="background: #ffffff; border-radius: 20px; overflow: hidden; position: sticky; top: 20px;">
                    <div class="card-header"
                        style="background: #f8f9fa; border-bottom: 1px solid #e9ecef; padding: 1rem 1.5rem;">
                        <h5 class="mb-0" style="color: #2c3e50; font-weight: 500;">Order Items</h5>
                    </div>

                    <div class="card-body" style="padding: 1.25rem; max-height: 600px; overflow-y: auto;">
                        @if($ticket->order)
                            @foreach ($ticket->order->items as $orderItem)
                                @php
                                    $product = $orderItem->item;
                                    $image = $product->images->first();
                                @endphp

                                <div
                                    style="background: #f8f9fa; border-radius: 16px; padding: 1rem; margin-bottom: 1rem; transition: transform 0.15s ease; border: 1px solid #e9ecef;">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0 me-3">
                                            @if ($image)
                                                <img src="{{ asset('storage/' . $image->image) }}" width="75" height="75"
                                                    style="object-fit: cover; border-radius: 12px; border: 1px solid #dee2e6;">
                                            @else
                                                <div
                                                    style="width: 75px; height: 75px; background: #e9ecef; border-radius: 12px; display: flex; align-items: center; justify-content: center; border: 1px solid #dee2e6;">
                                                    <span style="color: #6c757d; font-size: 10px;">No img</span>
                                                </div>
                                            @endif
                                        </div>

                                        <div style="flex: 1;">
                                            <h6 style="color: #212529; margin-bottom: 0.25rem; font-weight: 500;">
                                                {{ $product->name }}
                                            </h6>

                                            <small style="color: #6c757d; display: block; font-size: 0.7rem;">
                                                SKU: {{ $product->sku }}
                                            </small>

                                            <div style="display: flex; gap: 1rem; margin-top: 0.5rem; flex-wrap: wrap;">
                                                <small style="color: #6c757d;">
                                                    Qty: {{ $orderItem->quantity }}
                                                </small>
                                                <small style="color: #198754; font-weight: 500;">
                                                    ₹{{ number_format($orderItem->total_price, 2) }}
                                                </small>
                                            </div>

                                            <span
                                                style="display: inline-block; background: #198754; color: #fff; padding: 0.25rem 0.75rem; border-radius: 40px; font-size: 0.7rem; font-weight: 500; margin-top: 0.5rem;">
                                                {{ ucfirst($orderItem->status) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="alert alert-warning mb-0">
                                No order associated with this ticket.
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>

    </div>
@endsection