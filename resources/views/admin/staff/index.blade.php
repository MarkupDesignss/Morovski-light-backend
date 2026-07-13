{{-- resources/views/admin/staff/index.blade.php --}}
@extends('layouts.admin')

@section('content')
    <style>
        /* Base Styles with Georgia Serif */
        body,
        .container,
        table,
        input,
        select,
        button,
        a,
        p,
        h2,
        h3,
        label,
        th,
        td,
        .staff-wrapper,
        .alert-custom,
        .pagination .page-link {
            font-family: 'Georgia', 'Times New Roman', serif;
        }

        .staff-wrapper {
            padding: 2rem 1.5rem;
            background: #fefcf8;
            min-height: calc(100vh - 120px);
        }

        /* Page Header */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e9dfd1;
        }

        .staff-wrapper h2 {
            font-family: Georgia, serif;
            font-size: 1.85rem;
            font-weight: 700;
            color: #2c1810;
            margin: 0;
            letter-spacing: 0.01em;
            position: relative;
            display: inline-block;
        }

        .staff-wrapper h2:after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 50px;
            height: 3px;
            background: linear-gradient(90deg, #c4a27a, #8b6946);
            border-radius: 2px;
        }

        /* Stats Row */
        .stats-row {
            display: flex;
            gap: 1.5rem;
            justify-content: space-between;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 1rem 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            border: 1px solid #efe4d8;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .stat-icon {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, #f5ede4, #ede3d8);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-info h4 {
            margin: 0;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #8b735a;
        }

        .stat-info .stat-number {
            font-size: 1.75rem;
            font-weight: 700;
            color: #2c1810;
            line-height: 1.2;
        }

        /* Buttons */
        .btn-primary-custom {
            font-family: Georgia, serif;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.65rem 1.5rem;
            background: linear-gradient(135deg, #2c1810, #1f110a);
            color: #fef5e8 !important;
            border: none;
            border-radius: 40px;
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            letter-spacing: 0.03em;
            transition: all 0.25s ease;
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        }

        .btn-primary-custom:hover {
            background: linear-gradient(135deg, #3f2a1f, #2c1810);
            transform: translateY(-2px);
            box-shadow: 0 6px 14px rgba(44, 24, 16, 0.15);
            color: #fef5e8 !important;
            text-decoration: none;
        }

        .btn-primary-custom:active {
            transform: translateY(0px);
        }

        /* Action Buttons Group */
        .action-buttons {
            display: flex;
            gap: 0.6rem;
            flex-wrap: wrap;
        }

        .btn-view,
        .btn-edit,
        .btn-delete {
            font-family: Georgia, serif;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.35rem 1rem;
            border-radius: 30px;
            font-size: 0.75rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
            background: transparent;
        }

        .btn-view {
            background: #f0ece7;
            color: #5c4b3a !important;
        }

        .btn-view:hover {
            background: #e5ddd4;
            transform: translateY(-1px);
            color: #2c1810 !important;
            text-decoration: none;
        }

        .btn-edit {
            background: #e8e0d8;
            color: #5c4b3a !important;
        }

        .btn-edit:hover {
            background: #ddd2c6;
            transform: translateY(-1px);
            color: #2c1810 !important;
            text-decoration: none;
        }

        .btn-delete {
            background: #f0e2de;
            color: #b85c4a !important;
        }

        .btn-delete:hover {
            background: #e8d0ca;
            transform: translateY(-1px);
            color: #8b3a2a !important;
        }

        /* Improved Table */
        .staff-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(44, 24, 16, 0.06);
            border: 1px solid #efe4d8;
        }

        .staff-table thead tr {
            background: linear-gradient(135deg, #2c1810, #1f110a);
        }

        .staff-table thead th {
            font-family: Georgia, serif;
            color: #f0e2d0;
            font-weight: 600;
            font-size: 0.8rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            padding: 1rem 1.25rem;
            border: none;
        }

        .staff-table tbody tr {
            border-bottom: 1px solid #f0e8e0;
            transition: all 0.2s ease;
        }

        .staff-table tbody tr:hover {
            background: #fefaf5;
            transform: scale(1.00);
        }

        .staff-table tbody td {
            padding: 1rem 1.25rem;
            font-size: 0.9rem;
            color: #3a2a1e;
            vertical-align: middle;
            font-family: Georgia, serif;
            border-bottom: 1px solid #f3ede6;
        }

        .staff-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Role Badge */
        .role-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            background: #f0ece6;
            border-radius: 30px;
            font-size: 0.75rem;
            font-weight: 500;
            color: #5c4b3a;
        }

        /* Alert */
        .alert-success-custom {
            background: #f0f7ef;
            border-left: 4px solid #6b8c5c;
            color: #2c4a1e;
            padding: 0.9rem 1.25rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-family: Georgia, serif;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-success-custom:before {
            content: "✓";
            font-weight: bold;
            font-size: 1.1rem;
        }

        /* Search & Filter Bar */
        .search-bar {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.75rem;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
        }

        .search-input-group {
            display: flex;
            gap: 0.5rem;
            background: white;
            border: 1px solid #e2d5c8;
            border-radius: 60px;
            padding: 0.3rem 0.3rem 0.3rem 1rem;
            min-width: 260px;
        }

        .search-input-group input {
            border: none;
            background: transparent;
            padding: 0.5rem 0;
            font-size: 0.85rem;
            flex: 1;
            outline: none;
        }

        .search-input-group button {
            background: #2c1810;
            border: none;
            border-radius: 40px;
            padding: 0.4rem 1.2rem;
            color: white;
            font-family: Georgia, serif;
            cursor: pointer;
            transition: background 0.2s;
        }

        /* Pagination */
        .pagination {
            margin-top: 2rem;
            gap: 0.3rem;
        }

        .pagination .page-link {
            font-family: Georgia, serif;
            color: #5c4b3a;
            border: 1px solid #e2d5c8;
            background: white;
            padding: 0.5rem 0.9rem;
            border-radius: 30px !important;
            margin: 0 2px;
            transition: all 0.2s;
        }

        .pagination .page-item.active .page-link {
            background: linear-gradient(135deg, #2c1810, #1f110a);
            border-color: #2c1810;
            color: #f0e2d0;
        }

        .pagination .page-link:hover {
            background: #f0ece6;
            color: #2c1810;
            border-color: #cbbcaa;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .staff-table thead {
                display: none;
            }

            .staff-table tbody tr {
                display: block;
                margin-bottom: 1rem;
                border: 1px solid #efe4d8;
                border-radius: 16px;
                padding: 0.75rem;
            }

            .staff-table tbody td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                text-align: right;
                border: none;
                padding: 0.5rem 0.75rem;
            }

            .staff-table tbody td:before {
                content: attr(data-label);
                font-weight: 700;
                text-transform: uppercase;
                font-size: 0.7rem;
                letter-spacing: 0.5px;
                color: #8b735a;
            }

            .action-buttons {
                justify-content: flex-end;
            }
        }

        .alert-error-custom {
            background: #fdf1ef;
            border-left: 4px solid #d9534f;
            color: #8b2e2a;
            padding: 0.9rem 1.25rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-family: Georgia, serif;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-error-custom:before {
            content: "✕";
            font-weight: bold;
            font-size: 1rem;
        }
    </style>

    <div class="staff-wrapper">
        <div class="page-header">
            <h2>Staff Directory</h2>
            <a href="{{ route('admin.staff.create') }}" class="btn-primary-custom">
                + Add New Staff
            </a>
        </div>
        @if (session('error'))
            <div class="alert-error-custom">
                {{ session('error') }}
            </div>
        @endif

        @if (session('success'))
            <div class="alert-success-custom">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul style="margin:0; padding-left:18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Optional: Stats row (you can pass $totalStaff from controller) --}}
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-info">
                    <h4>Total Staff</h4>
                    <div class="stat-number">{{ $users->total() }}</div>
                </div>
            </div>
            <div class="search-bar" style="display: flex; flex-direction: column; gap: 12px; background: #f8f5f0; padding: 20px; border-radius: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                    <form method="GET" action="{{ route('admin.staff.index') }}" class="search-input-group" style="display: flex; flex-wrap: wrap; gap: 12px; align-items: center; background: white; padding: 8px 16px; border-radius: 48px; border: 1px solid #e2dcd3;">
                        <input type="text" name="search" placeholder="Search by name or email..." value="{{ request('search') }}" style="flex: 2; min-width: 180px; padding: 12px 16px; border: none; outline: none; font-size: 0.95rem; background: transparent; font-family: inherit;">
                
                        <select name="account_type" aria-label="Filter by account type" style="flex: 1; min-width: 150px; border: none; background: transparent; padding: 12px 8px; font-family: Georgia, serif; color: #3a2a1e; font-size: 0.9rem; outline: none; cursor: pointer;">
                            <option value="" {{ request('account_type') ? '' : 'selected' }}>All Roles</option>
                            <option value="sales_executive" {{ request('account_type') == 'sales_executive' ? 'selected' : '' }}>Sales Executive</option>
                            <option value="warehouse_manager" {{ request('account_type') == 'warehouse_manager' ? 'selected' : '' }}>Warehouse Manager</option>
                        </select>
                
                        <button type="submit" style="background: #2c5f2d; border: none; padding: 10px 20px; border-radius: 40px; color: white; font-weight: 500; cursor: pointer; font-family: inherit; transition: 0.2s;">Search</button>
                        <a href="{{ route('admin.staff.index') }}" class="btn-back" style="align-self: flex-start; text-decoration: none; background: transparent; border: 1px solid #cbc3b5; padding: 8px 20px; border-radius: 40px; color: #4a3b2c; font-size: 0.85rem; font-weight: 500; transition: 0.2s; text-align: center;">Reset</a>
                    </form>
                
                </div>
        </div>

        {{-- Search Bar (optional, requires route parameter) --}}


        <table class="staff-table">
            <thead>
                <tr>
                    <th>Sr. No.</th>
                    <th>Full Name</th>
                    <th>Email Address</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td data-label="Number"><strong>{{ $loop->iteration }}</strong></td>
                        <td data-label="Name"><strong>{{ $user->full_name }}</strong></td>
                        <td data-label="Email">{{ $user->email }}</td>
                        <td data-label="Phone">{{ $user->phone ?? '—' }}</td>
                        <td data-label="Role">
                            <span class="role-badge">{{ $user->roles->pluck('name')->implode(', ') ?: 'No role' }}</span>
                        </td>
                        <td data-label="Actions">
                            <div class="action-buttons">

                                {{-- BLOCK / UNBLOCK --}}
                                <form action="{{ route('admin.staff.toggleStatus', $user->id) }}" method="POST"
                                    style="display:inline;">
                                    @csrf
                                    @method('PATCH')

                                    @if ($user->is_active)
                                        <button type="submit" class="btn-icon btn-warning" title="Block User"
                                            onclick="return confirm('Block this user?')">
                                            🔒
                                        </button>
                                    @else
                                        <button type="submit" class="btn-icon btn-success" title="Unblock User"
                                            onclick="return confirm('Unblock this user?')">
                                            🔓
                                        </button>
                                    @endif
                                </form>

                                {{-- VIEW --}}
                                <a href="{{ route('admin.staff.show', $user->id) }}" class="btn-icon btn-info"
                                    title="View User">
                                    👁
                                </a>

                                {{-- EDIT --}}
                                <a href="{{ route('admin.staff.edit', $user->id) }}" class="btn-icon btn-primary"
                                    title="Edit User">
                                    ✏️
                                </a>

                                {{-- DELETE --}}
                                <form action="{{ route('admin.staff.destroy', $user->id) }}" method="POST"
                                    style="display:inline;">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="btn-icon btn-danger" title="Delete User"
                                        onclick="return confirm('Delete this user? This action cannot be undone.')">
                                        🗑
                                    </button>
                                </form>

                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 3rem;">No staff members found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="d-flex justify-content-center mt-4">
            {{ $users->links() }}
        </div>
    </div>
    <script>
        setTimeout(() => {

            const successAlert = document.querySelector('.alert-success-custom');

            if (successAlert) {
                successAlert.style.transition = 'opacity 0.5s ease';
                successAlert.style.opacity = '0';

                setTimeout(() => {
                    successAlert.remove();
                }, 500);
            }

            const errorAlerts = document.querySelectorAll('.alert-danger, .alert-error, .alert-error-custom');

            errorAlerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';

                setTimeout(() => {
                    alert.remove();
                }, 500);
            });

        }, 3000);
    </script>
@endsection
