{{-- resources/views/admin/staff/show.blade.php --}}
@extends('layouts.admin')

@section('content')
    <style>
        body,
        .staff-detail-wrapper,
        .detail-card,
        .detail-row,
        .btn-back {
            font-family: 'Georgia', 'Times New Roman', serif;
        }

        .staff-detail-wrapper {
            /* max-width: 720px; */
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .detail-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        .staff-detail-wrapper h2 {
            font-family: Georgia, serif;
            font-size: 1.75rem;
            font-weight: 700;
            color: #2c1810;
            margin: 0;
            letter-spacing: -0.2px;
        }

        .detail-card {
            background: white;
            border-radius: 28px;
            border: 1px solid #efe4d8;
            overflow: hidden;
            box-shadow: 0 8px 24px rgba(44, 24, 16, 0.06);
        }

        .detail-card-header {
            background: linear-gradient(135deg, #2c1810, #1f110a);
            padding: 1rem 1.8rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .detail-card-header .avatar-placeholder {
            width: 48px;
            height: 48px;
            background: rgba(255, 245, 230, 0.15);
            border-radius: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 600;
            color: #f0dfc0;
        }

        .detail-card-header span {
            font-family: Georgia, serif;
            color: #f9efdf;
            font-size: 0.85rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .detail-card-body {
            padding: 1.5rem 1.8rem;
            background: #fefcf9;
        }

        .detail-row {
            display: flex;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #f0e6dc;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-family: Georgia, serif;
            font-size: 0.72rem;
            font-weight: 700;
            color: #a58f76;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            width: 130px;
            flex-shrink: 0;
        }

        .detail-value {
            font-family: Georgia, serif;
            font-size: 1rem;
            color: #2f2219;
            flex: 1;
            font-weight: 500;
        }

        .role-tag {
            background: #f0ece6;
            padding: 0.2rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            display: inline-block;
            font-weight: normal;
        }

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

        .btn-back:hover {
            background: #f8f3ed;
            border-color: #bea587;
            color: #2c1810;
            text-decoration: none;
        }

        .action-buttons-detail {
            margin-top: 1.5rem;
            display: flex;
            gap: 1rem;
        }

        .btn-edit-small {
            background: #2c1810;
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 30px;
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 600;
        }

        @media (max-width: 560px) {
            .detail-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.4rem;
            }

            .detail-label {
                width: auto;
            }
        }
    </style>

    <div class="staff-detail-wrapper">
        <div class="detail-header">
            <h2>Staff Profile</h2>
            <a href="{{ route('admin.staff.edit', $user->id) }}" class="btn-edit-small">✎ Edit Profile</a>
        </div>

        <div class="detail-card">
            <div class="detail-card-header">
                <div class="avatar-placeholder">
                    {{ strtoupper(substr($user->full_name, 0, 1)) }}
                </div>
                <div>
                    <span>Staff Information</span>
                    <div style="color: #e8dccf; font-size: 0.7rem; margin-top: 2px;">ID: {{ $user->id }}</div>
                </div>
            </div>
            <div class="detail-card-body">
                <div class="detail-row">
                    <span class="detail-label">Full Name</span>
                    <span class="detail-value">{{ $user->full_name }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Email Address</span>
                    <span class="detail-value"><a href="mailto:{{ $user->email }}"
                            style="color: #7b623f; text-decoration: none;">{{ $user->email }}</a></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Phone Number</span>
                    <span class="detail-value">{{ $user->phone ?? '—' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Assigned Role</span>
                    <span class="detail-value"><span
                            class="role-tag">{{ $user->roles->pluck('name')->implode(', ') ?: 'No role set' }}</span></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Member Since</span>
                    <span class="detail-value">{{ $user->created_at->format('F j, Y') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Last Updated</span>
                    <span class="detail-value">{{ $user->updated_at->diffForHumans() }}</span>
                </div>
            </div>
        </div>

        <div class="action-buttons-detail">
            <a href="{{ route('admin.staff.index') }}" class="btn-back">← Back to Staff List</a>
        </div>
    </div>
@endsection
