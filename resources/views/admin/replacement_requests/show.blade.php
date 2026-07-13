@extends('layouts.admin')
<style>
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
    <div class="container py-4">
        <!-- Header Section -->
        <div class="mb-4" style="display: flex;
    justify-content: space-between;">
            <div>
                <h2 style="color: #3a2819; font-family: Georgia, serif; margin: 0; font-size: 28px; font-weight: bold;">
                    Replacement Request Details
                </h2>
                <p style="color: #160c00; margin-top: 8px;">View and manage replacement request information</p>
            </div>
            <div>
                <!--<a href="{{ route('admin.replacement_requests.index') }}" class="btn mb-3 btn-back"-->
                <!--    >-->
                <!--    ← Back to Requests-->
                <!--</a>-->
                <a href="{{ route('admin.replacement_requests.index') }}" class="btn-back">← Back to Items</a>
            </div>
        </div>

        <div class="row">
            <!-- Main Details Card -->
            <div class="col-md-7">
                <div class="card" style=" border-radius: 8px; overflow: hidden; margin-bottom: 20px;">
                    <div class="card-header"
                        style="background: linear-gradient(135deg, #3a2819 0%, #160c00 100%); color: white; border-bottom: none; padding: 15px 20px;">
                        <h4 style="margin: 0; font-size: 18px;">Request Information</h4>
                    </div>
                    <div class="card-body" style="padding: 20px;">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong style="color: #3a2819;">Request Number:</strong>
                            </div>
                            <div class="col-md-8">
                                <span style="color: #160c00; font-weight: 500;">{{ $replacement->request_number }}</span>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong style="color: #3a2819;">User:</strong>
                            </div>
                            <div class="col-md-8">
                                <span style="color: #160c00;">{{ $replacement->user->full_name ?? '-' }}</span>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong style="color: #3a2819;">Order Number:</strong>
                            </div>
                            <div class="col-md-8">
                                <span style="color: #160c00;">{{ $replacement->order->order_number ?? '-' }}</span>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong style="color: #3a2819;">Status:</strong>
                            </div>
                            <div class="col-md-8">
                                @php
                                    $statusColors = [
                                        'pending' => ['bg' => '#ffc107', 'color' => '#160c00'],
                                        'approved' => ['bg' => '#28a745', 'color' => 'white'],
                                        'rejected' => ['bg' => '#dc3545', 'color' => 'white'],
                                        'received' => ['bg' => '#17a2b8', 'color' => 'white'],
                                    ];
                                    $color = $statusColors[$replacement->status] ?? [
                                        'bg' => '#6c757d',
                                        'color' => 'white',
                                    ];
                                @endphp
                                <span class="badge"
                                    style="background: {{ $color['bg'] }}; color: {{ $color['color'] }}; padding: 5px 12px; border-radius: 6px;">
                                    {{ ucfirst($replacement->status) }}
                                </span>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong style="color: #3a2819;">Reason:</strong>
                            </div>
                            <div class="col-md-8">
                                <span style="color: #160c00;">{{ ucfirst($replacement->reason) }}</span>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong style="color: #3a2819;">Date:</strong>
                            </div>
                            <div class="col-md-8">
                                <span style="color: #160c00;">{{ $replacement->created_at->format('d M Y, h:i A') }}</span>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong style="color: #3a2819;">Received At:</strong>
                            </div>
                            <div class="col-md-8">
                                <span style="color: #160c00;">
                                    {{ $replacement->received_at ? $replacement->received_at->format('d M Y, h:i A') : '-' }}
                                </span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <strong style="color: #3a2819;">Message:</strong>
                            </div>
                            <div class="col-md-8">
                                <div
                                    style="color: #160c00; background: #f9f6f2; padding: 12px; border-radius: 6px; border-left: 3px solid #3a2819;">
                                    {{ $replacement->message ?? 'No message provided' }}
                                </div>
                            </div>
                        </div>
                        @if ($replacement->admin_notes)
                            <div class="row mt-3">
                                <div class="col-md-4">
                                    <strong style="color: #3a2819;">Admin Notes:</strong>
                                </div>
                                <div class="col-md-8">
                                    <div
                                        style="color: #160c00; background: #f9f6f2; padding: 12px; border-radius: 6px; border-left: 3px solid #dc3545;">
                                        {{ $replacement->admin_notes }}
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Actions Card - Only for Pending Status (Approve/Reject) -->
            @if ($replacement->status == 'pending')
                <div class="col-md-5">
                    <div class="card" style=" border-radius: 8px; overflow: hidden; margin-bottom: 20px;">
                        <div class="card-header"
                            style="background: linear-gradient(135deg, #3a2819 0%, #160c00 100%); color: white; border-bottom: none; padding: 15px 20px;">
                            <h4 style="margin: 0; font-size: 18px;">Pending Actions</h4>
                        </div>
                        <div class="card-body" style="padding: 20px;">
                            <!-- Approve Form -->
                            <form action="{{ route('admin.replacement_requests.approve', $replacement->id) }}"
                                method="POST" class="mb-4">
                                @csrf
                                <label style="color: #3a2819; font-weight: bold; margin-bottom: 8px; display: block;">Admin
                                    Notes (Optional)</label>
                                <textarea name="admin_notes" class="form-control mb-3" placeholder="Add approval notes..."
                                    style=" border-radius: 6px; padding: 10px; min-height: 100px; width: 100%;"></textarea>
                                <button class="btn w-100"
                                    style="background: #28a745; color: white; border: none; padding: 10px; border-radius: 6px; cursor: pointer; font-weight: bold;">
                                    ✓ Approve Request
                                </button>
                            </form>

                            <hr style="border-color: #3a2819; margin: 20px 0;">

                            <!-- Reject Form -->
                            <form action="{{ route('admin.replacement_requests.reject', $replacement->id) }}"
                                method="POST">
                                @csrf
                                <label
                                    style="color: #3a2819; font-weight: bold; margin-bottom: 8px; display: block;">Rejection
                                    Reason <span style="color: #dc3545;">*</span></label>
                                <textarea name="admin_notes" class="form-control mb-3" placeholder="Provide rejection reason..."
                                    style=" border-radius: 6px; padding: 10px; min-height: 100px; width: 100%;" required></textarea>
                                <button class="btn w-100"
                                    style="background: #dc3545; color: white; border: none; padding: 10px; border-radius: 6px; cursor: pointer; font-weight: bold;">
                                    ✗ Reject Request
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Warehouse Action Card - Only for Approved Status (Mark as Received) -->
            <!--@if ($replacement->status == 'approved')-->
            <!--    <div class="col-md-5">-->
            <!--        <div class="card" style=" border-radius: 8px; overflow: hidden;">-->
            <!--            <div class="card-header"-->
            <!--                style="background: linear-gradient(135deg, #3a2819 0%, #160c00 100%); color: white; border-bottom: none; padding: 15px 20px;">-->
            <!--                <h4 style="margin: 0; font-size: 18px;">Warehouse Action</h4>-->
            <!--            </div>-->
            <!--            <div class="card-body" style="padding: 20px;">-->
            <!--                <div-->
            <!--                    style="background: #e8f5e9; padding: 12px; border-radius: 6px; margin-bottom: 20px; border-left: 3px solid #28a745;">-->
            <!--                    <p style="margin: 0; color: #155724; font-size: 14px;">-->
            <!--                        ✅ This request has been approved. Please process the replacement and mark as received.-->
            <!--                    </p>-->
            <!--                </div>-->

            <!--                <form action="{{ route('admin.replacement_requests.received', $replacement->id) }}"-->
            <!--                    method="POST">-->
            <!--                    @csrf-->
            <!--                    <label-->
            <!--                        style="color: #3a2819; font-weight: bold; margin-bottom: 8px; display: block;">Warehouse-->
            <!--                        Notes (Optional)</label>-->
            <!--                    <textarea name="admin_notes" class="form-control mb-3"-->
            <!--                        placeholder="Add warehouse notes (e.g., item received, quality check passed, etc.)..."-->
            <!--                        style=" border-radius: 6px; padding: 10px; min-height: 100px; width: 100%;"></textarea>-->
            <!--                    <button class="btn w-100"-->
            <!--                        style="background: #17a2b8; color: white; border: none; padding: 10px; border-radius: 6px; cursor: pointer; font-weight: bold;">-->
            <!--                        📦 Mark as Received-->
            <!--                    </button>-->
            <!--                </form>-->
            <!--            </div>-->
            <!--        </div>-->
            <!--    </div>-->
            <!--@endif-->

            <!-- Information Card - For Rejected or Received Status (No Actions) -->
            @if ($replacement->status == 'rejected')
                <div class="col-md-5">
                    <div class="card" style="border: 1px solid #dc3545; border-radius: 8px; overflow: hidden;">
                        <div class="card-header"
                            style="background: linear-gradient(135deg, #dc3545 0%, #a71d2a 100%); color: white; border-bottom: none; padding: 15px 20px;">
                            <h4 style="margin: 0; font-size: 18px;">Request Rejected</h4>
                        </div>
                        <div class="card-body" style="padding: 20px;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#dc3545"
                                    stroke-width="2">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                </svg>
                                <div>
                                    <p style="color: #160c00; margin: 0 0 8px 0;">
                                        This request has been <strong style="color: #dc3545;">REJECTED</strong>
                                    </p>
                                    <p style="color: #666; margin: 0; font-size: 13px;">
                                        No further actions are required for this request.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if ($replacement->status == 'approved')
                <div class="col-md-5">
                    <div class="card" style="border: 1px solid #28a745; border-radius: 8px; overflow: hidden;">
                        <div class="card-header"
                            style="background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); color: white; border-bottom: none; padding: 15px 20px;">
                            <h4 style="margin: 0; font-size: 18px;">Request Completed</h4>
                        </div>
                        <div class="card-body" style="padding: 20px;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#28a745"
                                    stroke-width="2">
                                    <path d="M20 6L9 17l-5-5"></path>
                                    <circle cx="12" cy="12" r="10"></circle>
                                </svg>
                                <div>
                                    <p style="color: #160c00; margin: 0 0 8px 0;">
                                        This request has been <strong style="color: #28a745;">COMPLETED</strong>
                                    </p>
                                    <p style="color: #666; margin: 0; font-size: 13px;">
                                        Replacement item has been marked as received.
                                    </p>
                                    @if ($replacement->received_at)
                                        <p style="color: #666; margin: 5px 0 0 0; font-size: 12px;">
                                            Received on: {{ $replacement->received_at->format('d M Y, h:i A') }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Images Section -->
            <div class="col-md-12">
                <div class="card" style=" border-radius: 8px; overflow: hidden; margin-top: 20px;">
                    <div class="card-header"
                        style="background: linear-gradient(135deg, #3a2819 0%, #160c00 100%); color: white; border-bottom: none; padding: 15px 20px;">
                        <h4 style="margin: 0; font-size: 18px;">Uploaded Images</h4>
                    </div>
                    <div class="card-body" style="padding: 20px;">
                        @if ($replacement->images && count($replacement->images) > 0)
                            <div class="row">
                                @foreach ($replacement->images as $image)
                                    <div class="col-md-3 mb-4">
                                        <div class="image-container"
                                            style=" border-radius: 8px; overflow: hidden; transition: transform 0.3s ease;">
                                            <img src="{{ $image }}" class="img-fluid"
                                                style="width: 100%; height: 200px; object-fit: cover; cursor: pointer;"
                                                onclick="window.open(this.src)">
                                            <div style="padding: 8px; text-align: center; background: #f9f6f2;">
                                                <small style="color: #3a2819;">Click to enlarge</small>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center" style="padding: 40px; color: #160c00;">
                                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#3a2819"
                                    stroke-width="1.5">
                                    <rect x="2" y="2" width="20" height="20" rx="2.18"></rect>
                                    <circle cx="8.5" cy="8.5" r="2.5"></circle>
                                    <polyline points="21 15 16 10 5 21"></polyline>
                                </svg>
                                <p style="margin-top: 15px;">No images uploaded</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .image-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(58, 40, 25, 0.2);
        }

        input:focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: #3a2819 !important;
            box-shadow: 0 0 0 3px rgba(58, 40, 25, 0.1) !important;
        }

        .card {
            transition: box-shadow 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 5px 20px rgba(58, 40, 25, 0.1);
        }

        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }

            h2 {
                font-size: 24px;
            }

            .col-md-3 {
                width: 100%;
            }
        }

        button {
            transition: all 0.3s ease;
        }

        button:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }
    </style>
@endsection
