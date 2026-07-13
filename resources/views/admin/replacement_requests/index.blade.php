@extends('layouts.admin')

@section('content')
    <div class="container py-4">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 style="color: #3a2819; font-family: Georgia, serif; margin: 0; font-size: 28px; font-weight: bold;">
                    Replacement Requests
                </h2>
                <p style="color: #160c00; margin-top: 8px;">Manage and track all replacement requests</p>
            </div>
        </div>

        <!-- Filter Form -->
        <div class="card mb-4" style="">
            <div class="card-body" style="padding: 20px;">
                <form method="GET">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control"
                                placeholder="Search by request number, user, order..." value="{{ request('search') }}"
                                style="  padding: 10px;">
                        </div>
                        <div class="col-md-3">
                            <select name="status" class="form-control" style="  padding: 10px;">
                                <option value="">All Status</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending
                                </option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved
                                </option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected
                                </option>
                                <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>Received
                                </option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button class="btn"
                                style="background: #3a2819; color: white; border: none; padding: 10px 25px; ">
                                Filter
                            </button>
                            <a href="{{ request()->url() }}" class="btn"
                                style="background: #160c00; color: white; border: none; padding: 10px 20px; ">
                                Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Table Card -->
        <div class="card" style=" overflow: hidden;">
            <div class="card-body table-responsive p-0">
                <table class="table mb-0" style="">
                    <thead style="background: linear-gradient(135deg, #3a2819 0%, #160c00 100%); color: white;">
                        <tr>
                            <th style="padding: 12px;">Sr. No.</th>
                            <th style="padding: 12px;">Request No</th>
                            <th style="padding: 12px;">User</th>
                            <th style="padding: 12px;">Order</th>
                            <th style="padding: 12px;">Reason</th>
                            <th style="padding: 12px;">Status</th>
                            <th style="padding: 12px;">Date</th>
                            <th style="padding: 12px;" width="150">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $request)
                            <tr style="transition: background-color 0.3s ease;"
                                onmouseover="this.style.backgroundColor='#f5f0eb'"
                                onmouseout="this.style.backgroundColor='transparent'">
                                <td style=" padding: 10px; color: #160c00;">{{ $loop->iteration }}
                                </td>
                                <td style=" padding: 10px; color: #160c00; font-weight: 500;">
                                    {{ $request->request_number }}</td>
                                <td style=" padding: 10px; color: #160c00;">
                                    {{ $request->user->full_name ?? '-' }}</td>
                                <td style=" padding: 10px; color: #160c00;">
                                    {{ $request->order->order_number ?? '-' }}</td>
                                <td style=" padding: 10px; color: #160c00;">
                                   {{ ucwords(str_replace('_', ' ', $request->reason)) }}</td>
                                <td style=" padding: 10px;">
                                    @php
                                        $statusColors = [
                                            'pending' => ['bg' => '#ffc107', 'color' => '#160c00'],
                                            'approved' => ['bg' => '#28a745', 'color' => 'white'],
                                            'rejected' => ['bg' => '#dc3545', 'color' => 'white'],
                                            'received' => ['bg' => '#17a2b8', 'color' => 'white'],
                                        ];
                                        $color = $statusColors[$request->status] ?? [
                                            'bg' => '#6c757d',
                                            'color' => 'white',
                                        ];
                                    @endphp
                                    <span class="badge"
                                        style="background: {{ $color['bg'] }}; color: {{ $color['color'] }}; padding: 5px 10px; ">
                                        {{ ucfirst($request->status) }}
                                    </span>
                                </td>
                                <td style=" padding: 10px; color: #160c00;">
                                    {{ $request->created_at->format('d M Y') }}</td>
                                <td style=" padding: 10px;">
                                    <a href="{{ route('admin.replacement_requests.show', $request->id) }}"
                                        class="btn btn-sm"
                                        style="background: #3a2819; color: white; border: none; padding: 5px 15px; ">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center"
                                    style="padding: 60px; color: #160c00; ">
                                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#3a2819"
                                        stroke-width="1.5" style="margin: 0 auto 15px;">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2">
                                        </rect>
                                        <line x1="16" y1="2" x2="16" y2="6"></line>
                                        <line x1="8" y1="2" x2="8" y2="6"></line>
                                        <line x1="3" y1="10" x2="21" y2="10"></line>
                                    </svg>
                                    <p style="font-size: 18px;">No Requests Found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-4" style="display: flex; justify-content: center;">
            <div class="custom-pagination">
                {{ $requests->links() }}
            </div>
        </div>
    </div>

    <style>
        .custom-pagination nav {
            display: inline-block;
        }

        .custom-pagination .pagination {
            margin: 0;
            display: flex;
            gap: 5px;
            list-style: none;
            padding: 0;
        }

        .custom-pagination .page-item {
            display: inline-block;
        }

        .custom-pagination .page-link {
            padding: 8px 12px;

            color: #3a2819;
            background: white;
            text-decoration: none;

            transition: all 0.3s ease;
        }

        .custom-pagination .page-link:hover {
            background: #3a2819;
            color: white;

        }

        .custom-pagination .active .page-link {
            background: #3a2819;
            color: white;

        }

        .custom-pagination .disabled .page-link {
            color: #ccc;
            border-color: #ddd;
            cursor: not-allowed;
        }

        input:focus,
        select:focus {
            outline: none;
            border-color: #3a2819 !important;
            box-shadow: 0 0 0 3px rgba(58, 40, 25, 0.1) !important;
        }

        .table thead th:first-child {
            border-top-left-radius: 8px;
        }

        .table thead th:last-child {
            border-top-right-radius: 8px;
        }

        .table tbody tr:last-child td:first-child {
            border-bottom-left-radius: 8px;
        }

        .table tbody tr:last-child td:last-child {
            border-bottom-right-radius: 8px;
        }
    </style>
@endsection
