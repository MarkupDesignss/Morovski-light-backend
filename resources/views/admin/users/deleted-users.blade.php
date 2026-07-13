@extends('layouts.admin')

@section('content')
    <div class="container py-4">
        <!-- Header Section - matching deleted users page style -->
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-4">
            <div>
                <h2 class="text-3xl font-bold" style="color: #3a2819; font-family: Georgia, serif; margin: 0;">
                    Account Deletion Requests
                </h2>
                <p class="mt-2 text-sm" style="color: #160c00;">Manage and review user requests to delete their accounts.</p>
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if (session('success'))
            <div class="alert alert-success mb-4"
                style="background: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 8px; padding: 12px 15px;">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger mb-4"
                style="background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 8px; padding: 12px 15px;">
                {{ session('error') }}
            </div>
        @endif

        <!-- Search and Filter Form - redesigned to match theme -->
        <div class="mb-4">
            <form method="GET" action="{{ route('admin.deleted-users.index') }}" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search name, email, reason..."
                        value="{{ request('search') }}"
                        style="border: 1px solid #3a2819; padding: 10px 15px; border-radius: 8px;">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-control"
                        style="border: 1px solid #3a2819; padding: 10px 15px; border-radius: 8px;">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn w-100"
                        style="background: #3a2819; color: white; border: none; padding: 10px 15px; border-radius: 8px; transition: all 0.3s ease;">Filter</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('admin.deleted-users.index') }}" class="btn w-100"
                        style="background: #6c757d; color: white; border: none; padding: 10px 15px; border-radius: 8px; text-decoration: none; display: inline-block; text-align: center; transition: all 0.3s ease;">Reset</a>
                </div>
            </form>
        </div>

        <!-- Table - matching the exact styling from deleted users -->
        <div class="table-responsive">
            <table class="table table-bordered" style="border-collapse: separate; border-spacing: 0; width: 100%;">
                <thead style="background: linear-gradient(135deg, #3a2819 0%, #160c00 100%); color: white;">
                    <tr>
                        <th style="padding: 12px; border-top-left-radius: 8px;">ID</th>
                        <th style="padding: 12px;">Name</th>
                        <th style="padding: 12px;">Email</th>
                        <th style="padding: 12px;">Reason</th>
                        <th style="padding: 12px;">Status</th>
                        <th style="padding: 12px;">OTP</th>
                        <th style="padding: 12px;">Requested At</th>
                        <th style="padding: 12px; border-top-right-radius: 8px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($deletedUsers as $user)
                        <tr style="transition: background-color 0.3s ease;"
                            onmouseover="this.style.backgroundColor='#f5f0eb'"
                            onmouseout="this.style.backgroundColor='transparent'">
                            <td style="padding: 10px; color: #160c00;">{{ $user->id }}</td>
                            <td style="padding: 10px; color: #160c00; font-weight: 500;">{{ $user->name ?? '-' }}</td>
                            <td style="padding: 10px; color: #160c00;">{{ $user->email ?? '-' }}</td>
                            <td style="padding: 10px; color: #160c00;">
                                @if ($user->reason)
                                    <span class="badge"
                                        style="background: #3a2819; color: white; padding: 5px 10px; border-radius: 6px; font-size: 12px; display: inline-block; max-width: 250px; word-break: break-word;">
                                        {{ $user->reason }}
                                    </span>
                                @else
                                    <span style="color: #999;">-</span>
                                @endif
                            </td>
                            <td style="padding: 10px;">
                                @if ($user->status == 'pending')
                                    <span class="badge"
                                        style="background: #ffc107; color: #160c00; padding: 5px 10px; border-radius: 6px; font-size: 12px;">Pending</span>
                                @elseif($user->status == 'approved')
                                    <span class="badge"
                                        style="background: #28a745; color: white; padding: 5px 10px; border-radius: 6px; font-size: 12px;">Approved</span>
                                @else
                                    <span class="badge"
                                        style="background: #dc3545; color: white; padding: 5px 10px; border-radius: 6px; font-size: 12px;">Rejected</span>
                                @endif
                            </td>
                            <td style="padding: 10px;">
                                @if ($user->otp_verified)
                                    <span class="badge"
                                        style="background: #28a745; color: white; padding: 5px 10px; border-radius: 6px; font-size: 12px;">Verified</span>
                                @else
                                    <span class="badge"
                                        style="background: #dc3545; color: white; padding: 5px 10px; border-radius: 6px; font-size: 12px;">Pending</span>
                                @endif
                            </td>
                            <td style="padding: 10px; color: #160c00;">{{ $user->created_at->format('M d, Y H:i') }}</td>
                            <td style="padding: 10px;">
                                @if ($user->status == 'pending')
                                    <form action="{{ route('admin.delete-request.approve', $user->id) }}" method="POST"
                                        style="display:inline-block">
                                        @csrf
                                        <button type="submit" class="btn btn-sm"
                                            style="background: #28a745; color: white; border: none; padding: 5px 12px; border-radius: 6px; margin-right: 5px; transition: all 0.3s ease;"
                                            onclick="return confirm('Approve this deletion request?')"
                                            onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                                            Approve
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.delete-request.reject', $user->id) }}" method="POST"
                                        style="display:inline-block"
                                        onsubmit="return confirm('Reject this deletion request?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm"
                                            style="background: #dc3545; color: white; border: none; padding: 5px 12px; border-radius: 6px; transition: all 0.3s ease;"
                                            onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                                            Reject
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted" style="color: #999;">No Action</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center" style="padding: 40px; color: #160c00;">
                                <div class="text-center">
                                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none"
                                        stroke="#3a2819" stroke-width="1.5" style="margin: 0 auto 15px;">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>
                                    <p style="font-size: 18px;">No deletion requests found.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Styling - matching deleted users page -->
        <div class="mt-4" style="display: flex; justify-content: center;">
            <div class="custom-pagination">
                {{ $deletedUsers->links() }}
            </div>
        </div>
    </div>

    <!-- Custom Pagination Styles (copied from deleted users page) -->
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
            border: 1px solid #dee2e6;
            border-radius: 6px;
        }

        .custom-pagination .page-link:hover {
            background: #3a2819;
            color: white;
        }

        .custom-pagination .active .page-link {
            background: #3a2819;
            color: white;
            border-color: #3a2819;
        }

        .custom-pagination .disabled .page-link {
            color: #ccc;
            cursor: not-allowed;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .table {
                font-size: 14px;
            }

            .table th,
            .table td {
                padding: 8px;
            }

            h2 {
                font-size: 24px;
            }
        }

        /* Search input focus style */
        input[type="text"]:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #3a2819;
            box-shadow: 0 0 0 3px rgba(58, 40, 25, 0.1);
        }

        /* Table border radius consistency */
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