@extends('layouts.admin')

@section('title', 'Manage Users')

@section('content')
    <style>
        * {
            box-sizing: border-box;
        }

        .page-wrap {
            min-height: 100vh;
            padding: 0.25rem 0 2rem;
        }

        /* ── Success Banner ── */
        .success-banner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: linear-gradient(135deg, #2a1a05 0%, #1a0f00 100%);
            border: 1px solid rgba(162, 128, 81, 0.4);
            border-radius: 14px;
            padding: 14px 18px;
            margin-bottom: 1.5rem;
            position: relative;
            overflow: hidden;
        }

        .success-banner::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, #A28051, #C5A672, #A28051, transparent);
        }

        .success-banner-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .success-icon-wrap {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(162, 128, 81, 0.15);
            border: 1px solid rgba(162, 128, 81, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .success-icon-wrap svg {
            width: 18px;
            height: 18px;
            color: #A28051;
        }

        .success-title {
            font-size: 14px;
            font-weight: 600;
            color: #e8c98a;
            font-family: Georgia, serif;
        }

        .success-sub {
            font-size: 11px;
            color: rgba(212, 180, 131, 0.6);
            letter-spacing: 0.5px;
            margin-top: 2px;
        }

        .success-close {
            background: none;
            border: none;
            color: rgba(162, 128, 81, 0.5);
            cursor: pointer;
            padding: 4px;
            transition: color 0.2s;
        }

        .success-close:hover {
            color: #A28051;
        }

        .success-progress {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: rgba(162, 128, 81, 0.15);
        }

        .success-progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #A28051, #C5A672);
            animation: shrink 3s linear forwards;
        }

        @keyframes shrink {
            from {
                width: 100%;
            }

            to {
                width: 0%;
            }
        }

        /* ── Page Header ── */
        .page-header {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1.5rem 1.75rem;
            background: linear-gradient(160deg, #fdf8f0 0%, #faf3e8 100%);
            border: 1px solid rgba(162, 128, 81, 0.22);
            border-radius: 18px;
            margin-bottom: 1.75rem;
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, #A28051, #C5A672, #A28051, transparent);
        }

        .page-header-title {
            font-size: 22px;
            font-weight: 700;
            color: #2a1a05;
            letter-spacing: 1px;
            font-family: Georgia, serif;
            margin: 0;
        }

        .page-header-sub {
            font-size: 11px;
            color: #8a6a3a;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-top: 4px;
        }

        /* ── Tab Buttons ── */
        .tab-group {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
        }

        .tab-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 9px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-decoration: none;
            transition: all 0.2s;
            border: 1px solid rgba(162, 128, 81, 0.3);
            background: rgba(255, 255, 255, 0.6);
            color: #8a6a3a;
        }

        .tab-btn:hover {
            background: rgba(162, 128, 81, 0.1);
            border-color: #A28051;
            color: #2a1a05;
        }

        .tab-btn.active-tab {
            background: linear-gradient(135deg, #2a1a05, #1a0f00);
            border-color: rgba(162, 128, 81, 0.5);
            color: #d4b483;
        }

        .tab-count-pill {
            padding: 7px 14px;
            border-radius: 9px;
            font-size: 12px;
            font-weight: 600;
            background: linear-gradient(135deg, #2a1a05, #1a0f00);
            border: 1px solid rgba(162, 128, 81, 0.4);
            color: #d4b483;
            letter-spacing: 0.5px;
        }

        /* ── Table Card ── */
        .table-card {
            background: linear-gradient(160deg, #fdf8f0 0%, #faf3e8 100%);
            border: 1px solid rgba(162, 128, 81, 0.2);
            border-radius: 18px;
            overflow: hidden;
        }

        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead tr {
            background: linear-gradient(90deg, rgba(162, 128, 81, 0.1), rgba(162, 128, 81, 0.04));
            border-bottom: 1px solid rgba(162, 128, 81, 0.2);
        }

        th {
            padding: 13px 16px;
            text-align: left;
            font-size: 10px;
            font-weight: 700;
            color: #8a6a3a;
            letter-spacing: 2px;
            text-transform: uppercase;
            white-space: nowrap;
        }

        tbody tr {
            border-bottom: 1px solid rgba(162, 128, 81, 0.1);
            transition: background 0.2s;
        }

        tbody tr:last-child {
            border-bottom: none;
        }

        tbody tr:hover {
            background: rgba(162, 128, 81, 0.05);
        }

        td {
            padding: 13px 16px;
            font-size: 13px;
            color: #3a2510;
            vertical-align: middle;
        }

        /* ── Avatar ── */
        .user-avatar-cell {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: linear-gradient(135deg, #2a1a05, #1a0f00);
            border: 1.5px solid rgba(162, 128, 81, 0.45);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
            color: #d4b483;
            flex-shrink: 0;
            font-family: Georgia, serif;
        }

        .user-name-cell {
            font-size: 13px;
            font-weight: 600;
            color: #2a1a05;
        }

        /* ── Badges ── */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 11px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .badge svg {
            width: 11px;
            height: 11px;
        }

        .badge-b2b {
            background: rgba(162, 128, 81, 0.12);
            color: #7a5e30;
            border: 1px solid rgba(162, 128, 81, 0.3);
        }

        .badge-b2c {
            background: rgba(196, 165, 114, 0.1);
            color: #8a6a3a;
            border: 1px solid rgba(162, 128, 81, 0.2);
        }

        /* ── Status Select ── */
        .status-select {
            font-size: 12px;
            font-weight: 600;
            padding: 6px 10px;
            border-radius: 8px;
            border: 1px solid rgba(162, 128, 81, 0.3);
            background: rgba(255, 255, 255, 0.6);
            color: #2a1a05;
            outline: none;
            cursor: pointer;
            transition: border-color 0.2s, box-shadow 0.2s;
            font-family: inherit;
        }

        .status-select:focus {
            border-color: #A28051;
            box-shadow: 0 0 0 3px rgba(162, 128, 81, 0.12);
        }

        /* ── Action Buttons ── */
        .btn-view {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 7px 14px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
            letter-spacing: 0.3px;
            background: linear-gradient(135deg, #2a1a05, #1a0f00);
            border: 1px solid rgba(162, 128, 81, 0.4);
            color: #d4b483;
        }

        .btn-view:hover {
            border-color: #A28051;
            color: #e8c98a;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .btn-delete {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 7px 14px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.2s;
            letter-spacing: 0.3px;
            cursor: pointer;
            background: rgba(200, 60, 60, 0.08);
            border: 1px solid rgba(200, 60, 60, 0.25);
            color: #a03030;
        }

        .btn-delete:hover {
            background: rgba(200, 60, 60, 0.15);
            border-color: rgba(200, 60, 60, 0.45);
        }

        .actions-cell {
            display: flex;
            align-items: center;
            gap: 7px;
            flex-wrap: wrap;
        }

        /* ── Pagination ── */
        .pagination-wrap {
            padding: 1rem 1.5rem;
            border-top: 1px solid rgba(162, 128, 81, 0.15);
            background: rgba(162, 128, 81, 0.03);
        }

        .ornament-line {
            text-align: center;
            color: rgba(162, 128, 81, 0.3);
            font-size: 11px;
            letter-spacing: 6px;
            padding: 1.25rem 0 0;
        }

        /* ── Modal overrides ── */
        .modal-content {
            border-radius: 16px;
            border: 1px solid rgba(162, 128, 81, 0.3);
            background: #fdf8f0;
        }

        .modal-header {
            border-bottom: 1px solid rgba(162, 128, 81, 0.2);
            padding: 1.1rem 1.5rem;
        }

        .modal-title {
            font-family: Georgia, serif;
            font-size: 15px;
            color: #2a1a05;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .modal-footer {
            border-top: 1px solid rgba(162, 128, 81, 0.2);
        }

        .form-label {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #8a6a3a;
        }

        .form-control {
            border: 1px solid rgba(162, 128, 81, 0.3);
            border-radius: 9px;
            background: rgba(255, 255, 255, 0.7);
            color: #2a1a05;
            font-size: 13px;
            padding: 8px 12px;
            font-family: inherit;
        }

        .form-control:focus {
            border-color: #A28051;
            box-shadow: 0 0 0 3px rgba(162, 128, 81, 0.1);
            outline: none;
        }

        .btn-secondary-modal {
            padding: 8px 18px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            background: rgba(162, 128, 81, 0.1);
            border: 1px solid rgba(162, 128, 81, 0.3);
            color: #8a6a3a;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-secondary-modal:hover {
            background: rgba(162, 128, 81, 0.18);
        }

        .btn-primary-modal {
            padding: 8px 18px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            background: linear-gradient(135deg, #2a1a05, #1a0f00);
            border: 1px solid rgba(162, 128, 81, 0.4);
            color: #d4b483;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-primary-modal:hover {
            border-color: #A28051;
            color: #e8c98a;
        }

        .btn-danger-modal {
            padding: 8px 18px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            background: rgba(180, 50, 50, 0.1);
            border: 1px solid rgba(180, 50, 50, 0.3);
            color: #a03030;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-danger-modal:hover {
            background: rgba(180, 50, 50, 0.18);
        }
    </style>

    <div class="page-wrap">

        {{-- SUCCESS --}}
        @if (session('success'))
            <div id="success-message" class="success-banner">
                <div class="success-banner-left">
                    <div class="success-icon-wrap">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <div class="success-title">{{ session('success') }}</div>
                        <div class="success-sub">Success</div>
                    </div>
                </div>
                <button class="success-close" onclick="this.closest('.success-banner').remove()">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                <div class="success-progress">
                    <div class="success-progress-bar"></div>
                </div>
            </div>
        @endif

        {{-- HEADER --}}
        <div class="page-header">
            <div>
                <h2 class="page-header-title">{{ $title ?? __('admin.manage_users') }}</h2>
                <p class="page-header-sub">{{ __('admin.view_manage_users') }}</p>
            </div>
            <div class="tab-group">
                <a href="{{ route('admin.users.index') }}" class="tab-btn">B2C Users</a>
                <a href="{{ route('admin.business-users.index') }}" class="tab-btn">B2B Users</a>
                {{-- <a href="{{ route('admin.rejected.users') }}" class="tab-btn active-tab">Rejected B2B</a> --}}
                <div class="tab-count-pill">
                    <div style="font-size:12px; line-height:1;">
                        <!--<div>Total: {{ $totalUsers ?? $users->total() }}</div>-->
                        <div style="font-size:11px; margin-top:4px;">B2B: {{ $totalB2B ?? 0 }} &nbsp;·&nbsp; B2C:
                            {{ $totalB2C ?? 0 }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- TABLE --}}
        <div class="table-card">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Sr. No.</th>
                            <th>{{ __('admin.name') }}</th>
                            <th>{{ __('admin.email') }}</th>
                            <th>{{ __('admin.account_type') }}</th>
                            @if (request()->routeIs('admin.business-users.*'))
                                <th>Discount %</th>
                            @endif
                            <th>{{ __('admin.business_status') }}</th>
                            <th>{{ __('admin.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td style="color:#A28051; font-weight:700; font-family:Georgia,serif;">
                                    {{ $loop->iteration }}</td>
                                <td>
                                    <div style="display:flex; align-items:center; gap:10px;">
                                        <div class="user-avatar-cell">{{ strtoupper(substr($user->full_name, 0, 2)) }}
                                        </div>
                                        <span class="user-name-cell">{{ $user->full_name }}</span>
                                    </div>
                                </td>
                                <td style="color:#6a4e24;">{{ $user->email }}</td>
                                <td>
                                    @if ($user->account_type == 'b2b')
                                        <span class="badge badge-b2b">
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                            </svg>
                                            B2B
                                        </span>
                                    @else
                                        <span class="badge badge-b2c">
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                            B2C
                                        </span>
                                    @endif
                                </td>
                                @if (request()->routeIs('admin.business-users.*'))
                                    <td>
                                        @if ($user->account_type === 'b2b')
                                            <form action="{{ route('admin.users.update-discount', $user->id) }}"
                                                method="POST" class="inline-block">
                                                @csrf
                                                @method('PATCH')
                                                <div style="display:flex; gap:6px; align-items:center;">
                                                    <input type="number" name="discount_percentage" min="0"
                                                        max="100" step="0.01"
                                                        value="{{ old('discount_percentage', optional($user->businessProfile)->discount_percentage ?? 0) }}"
                                                        class="form-control"
                                                        style="width:100px; padding:6px 10px; font-size:13px;" />
                                                    <button type="submit" class="btn "
                                                        style="padding:7px 10px;color:white;font-size:12px;background: linear-gradient(135deg, #2a1a05, #1a0f00);">Save</button>
                                                </div>
                                            </form>
                                        @else
                                            <span style="color:rgba(120,88,42,0.65);">-</span>
                                        @endif
                                    </td>
                                @endif
                                <td>
                                    @if ($user->account_type == 'b2b')
                                        <form action="{{ route('admin.users.status', $user->id) }}" method="POST"
                                            class="status-form">
                                            @csrf
                                            @method('PATCH')
                                            <select name="business_status" class="status-select"
                                                onchange="handleStatusChange(this)">
                                                <option value="pending"
                                                    {{ $user->business_status == 'pending' ? 'selected' : '' }}>🟡
                                                    {{ __('admin.pending') }}</option>
                                                <option value="approved"
                                                    {{ $user->business_status == 'approved' ? 'selected' : '' }}>🟢
                                                    {{ __('admin.approved') }}</option>
                                <option value="rejected"
                                    {{ $user->business_status == 'rejected' ? 'selected' : '' }}>🔴
                                    {{ __('admin.rejected') }}</option>
                                            </select>
                                        </form>
                                @else
                                        <span style="font-size:12px; color:rgba(120,88,42,0.5); letter-spacing:1px;">N /
                                            A</span>
                                
    @endif
                                </td>
                                <td>
                                    <div class="actions-cell">
                                        <a href="{{ route('admin.users.show', $user->id) }}" class="btn-view">
                                            <svg width="13" height="13" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            {{ __('admin.view_details') }}
                                        </a>
                                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST"
                                            class="inline-block"
                                            onsubmit="return confirm('{{ __('admin.delete_user_confirm_msg') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-delete">
                                                <svg width="13" height="13" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                {{ __('admin.delete') }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pagination-wrap">{{ $users->links() }}</div>
        </div>

        <div class="ornament-line">✦ &nbsp; &nbsp; ✦ &nbsp; &nbsp; ✦</div>
    </div>

    {{-- Role Modal --}}
    <div class="modal fade" id="roleModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.users.assign-role') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('admin.manage_user_role') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" style="padding:1.25rem 1.5rem;">
                        <input type="hidden" name="user_id" id="role_user_id">
                        <div class="mb-3">
                            <label class="form-label">{{ __('admin.user') }}</label>
                            <input type="text" id="role_user_name" class="form-control" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('admin.select_role') }}</label>
                            <select name="role_id" class="form-control" required>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer" style="gap:8px;">
                        <button class="btn-secondary-modal" data-bs-dismiss="modal">{{ __('admin.cancel') }}</button>
                        <button type="submit" class="btn-primary-modal">{{ __('admin.assign_role') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Reject Reason Modal --}}
    <div class="modal fade" id="rejectReasonModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reason for Rejection</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="padding:1.25rem 1.5rem;">
                    <div class="mb-3">
                        <label class="form-label">Please enter reason</label>
                        <textarea id="rejectReasonInput" class="form-control" rows="4" maxlength="500"></textarea>
                        <div style="font-size:11px; color:#8a6a3a; margin-top:4px;">
                            {{ __('admin.maximum_500_characters') }}</div>
                    </div>
                </div>
                <div class="modal-footer" style="gap:8px;">
                    <button type="button" class="btn-secondary-modal" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="confirmRejectBtn"
                        class="btn-danger-modal">{{ __('admin.reject_and_submit') }}</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const msg = document.getElementById('success-message');
            if (msg) {
                setTimeout(() => {
                    msg.style.transition = 'opacity 0.5s ease';
                    msg.style.opacity = '0';
                    setTimeout(() => msg.style.display = 'none', 500);
                }, 3000);
            }
            document.querySelectorAll('select[name="business_status"]').forEach(function(sel) {
                sel.addEventListener('focus', function() {
                    this.setAttribute('data-previous', this.value);
                });
                sel.addEventListener('mousedown', function() {
                    this.setAttribute('data-previous', this.value);
                });
            });
        });

        // (Deduplicated) status/reject handlers are defined below once (AJAX-enabled)

        let pendingStatusForm = null,
            pendingSelect = null,
            pendingPrevValue = null,
            submittedViaModal = false;

        function handleStatusChange(selectElem) {
            const value = selectElem.value;
            const form = selectElem.closest('form');
            if (!form) return;
            if (value === 'rejected') {
                pendingStatusForm = form;
                pendingSelect = selectElem;
                pendingPrevValue = selectElem.getAttribute('data-previous') ?? selectElem.querySelector('option[selected]')
                    ?.value ?? 'pending';
                submittedViaModal = false;
                const reasonInput = document.getElementById('rejectReasonInput');
                reasonInput.value = '';
                const modalEl = document.getElementById('rejectReasonModal');
                const bsModal = new bootstrap.Modal(modalEl);
                // keep a global reference so we can hide it before submitting
                window.pendingBsModal = bsModal;
                bsModal.show();
                modalEl.addEventListener('hidden.bs.modal', function() {
                    if (!submittedViaModal && pendingSelect) pendingSelect.value = pendingPrevValue;
                    pendingStatusForm = null;
                    pendingSelect = null;
                    pendingPrevValue = null;
                    submittedViaModal = false;
                }, {
                    once: true
                });
            } else {
                const existing = form.querySelector('input[name="reason"]');
                if (existing) existing.remove();
                form.submit();
            }
        }
        document.addEventListener('hidden.bs.modal', function() {
            document.body.classList.remove('modal-open');
            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
        });
        document.getElementById('confirmRejectBtn').addEventListener('click', function() {
            const reason = document.getElementById('rejectReasonInput').value.trim();
            if (!reason) {
                alert('Please enter a reason for rejection.');
                return;
            }
            if (!pendingStatusForm) return;
            // attach reason input to the form data
            let input = pendingStatusForm.querySelector('input[name="reason"]');
            if (!input) {
                input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'reason';
                pendingStatusForm.appendChild(input);
            }
            input.value = reason;

            submittedViaModal = true;

            // hide modal UI ASAP and clean backdrop
            try {
                if (window.pendingBsModal) window.pendingBsModal.hide();
            } catch (e) {}
            setTimeout(() => {
                document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
            }, 60);

            // Submit via AJAX to avoid navigating while modal animations/backdrop may linger
            (async () => {
                try {
                    // show a temporary loading state
                    const originalBtn = this;
                    originalBtn.disabled = true;
                    const spinner = document.createElement('span');
                    spinner.className = 'spinner-border spinner-border-sm ms-2';
                    originalBtn.appendChild(spinner);

                    const url = pendingStatusForm.action;
                    const tokenInput = pendingStatusForm.querySelector('input[name="_token"]');
                    const csrfToken = tokenInput ? tokenInput.value : null;

                    const fd = new FormData(pendingStatusForm);
                    // Ensure method override for PATCH
                    fd.set('_method', pendingStatusForm.querySelector('input[name="_method"]')?.value ||
                        'PATCH');

                    const res = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken || ''
                        },
                        body: fd,
                        credentials: 'same-origin'
                    });

                    // Remove spinner
                    spinner.remove();
                    originalBtn.disabled = false;

                    if (res.ok) {
                        // on success, reload to reflect changes
                        window.location.reload();
                    } else {
                        // try to parse json error
                        let errText = 'Request failed';
                        try {
                            const j = await res.json();
                            errText = j.message || JSON.stringify(j);
                        } catch (e) {}
                        alert('Reject failed: ' + errText);
                    }
                } catch (e) {
                    alert('Network error. Try again.');
                }
            })();
        });

        function openRoleModal(userId, userName) {
            document.getElementById('role_user_id').value = userId;
            document.getElementById('role_user_name').value = userName;
            new bootstrap.Modal(document.getElementById('roleModal')).show();
        }
    </script>

@endsection
