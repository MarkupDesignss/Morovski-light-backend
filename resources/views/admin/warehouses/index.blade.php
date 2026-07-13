{{-- resources/views/admin/warehouses/index.blade.php --}}
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
        .warehouse-wrapper,
        .alert-custom,
        .pagination .page-link {
            font-family: 'Georgia', 'Times New Roman', serif;
        }

        .warehouse-wrapper {
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

        .warehouse-wrapper h2 {
            font-family: Georgia, serif;
            font-size: 1.85rem;
            font-weight: 700;
            color: #2c1810;
            margin: 0;
            letter-spacing: 0.01em;
            position: relative;
            display: inline-block;
        }

        .warehouse-wrapper h2:after {
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

        /* Action Buttons Group */
        .action-buttons {
            display: flex;
            gap: 0.6rem;
            flex-wrap: wrap;
        }

        .btn-edit, .btn-delete, .btn-toggle {
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

        .btn-toggle {
            background: #f0ece7;
            color: #5c4b3a !important;
        }

        .btn-toggle:hover {
            background: #e5ddd4;
            transform: translateY(-1px);
            color: #2c1810 !important;
        }

        /* Table */
        .warehouse-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(44, 24, 16, 0.06);
            border: 1px solid #efe4d8;
        }

        .warehouse-table thead tr {
            background: linear-gradient(135deg, #2c1810, #1f110a);
        }

        .warehouse-table thead th {
            font-family: Georgia, serif;
            color: #f0e2d0;
            font-weight: 600;
            font-size: 0.8rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            padding: 1rem 1.25rem;
            border: none;
        }

        .warehouse-table tbody tr {
            border-bottom: 1px solid #f0e8e0;
            transition: all 0.2s ease;
        }

        .warehouse-table tbody tr:hover {
            background: #fefaf5;
        }

        .warehouse-table tbody td {
            padding: 1rem 1.25rem;
            font-size: 0.9rem;
            color: #3a2a1e;
            vertical-align: middle;
            font-family: Georgia, serif;
            border-bottom: 1px solid #f3ede6;
        }

        .warehouse-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 30px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .status-active {
            background: #e0f0e0;
            color: #2c6e2c;
        }

        .status-inactive {
            background: #f0e0e0;
            color: #b85c4a;
        }

        /* Alerts */
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

        /* Search Bar */
        .search-bar {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.75rem;
            flex-wrap: wrap;
            align-items: center;
            justify-content: flex-end;
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

        .btn-back {
            font-family: Georgia, serif;
            background: #e8e0d8;
            padding: 0.5rem 1.2rem;
            border-radius: 40px;
            color: #5c4b3a;
            text-decoration: none;
            font-size: 0.85rem;
            transition: background 0.2s;
        }

        .btn-back:hover {
            background: #ddd2c6;
            color: #2c1810;
        }

        /* Pagination */
        .pagination {
            margin-top: 2rem;
            gap: 0.3rem;
            justify-content: center;
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
            .warehouse-table thead {
                display: none;
            }

            .warehouse-table tbody tr {
                display: block;
                margin-bottom: 1rem;
                border: 1px solid #efe4d8;
                border-radius: 16px;
                padding: 0.75rem;
            }

            .warehouse-table tbody td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                text-align: right;
                border: none;
                padding: 0.5rem 0.75rem;
            }

            .warehouse-table tbody td:before {
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
    </style>

    <div class="warehouse-wrapper">
        <div class="page-header">
            <h2>Warehouse Management</h2>
            <a href="{{ route('admin.warehouses.create') }}" class="btn-primary-custom">
                + Add New Warehouse
            </a>
        </div>

        @if(session('success'))
            <div class="alert-success-custom">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert-error-custom">{{ session('error') }}</div>
        @endif

        @if($errors->any())
            <div class="alert-error-custom">
                <ul style="margin:0; padding-left:18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-icon">🏢</div>
                <div class="stat-info">
                    <h4>Total Warehouses</h4>
                    <div class="stat-number">{{ $warehouses->total() }}</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">✅</div>
                <div class="stat-info">
                    <h4>Active</h4>
                    <div class="stat-number">{{ $warehouses->where('is_active', true)->count() }}</div>
                </div>
            </div>
            <div class="search-bar">
                <form method="GET" action="{{ route('admin.warehouses.index') }}" class="search-input-group">
                    <input type="text" name="search" placeholder="Search by name or code..." value="{{ request('search') }}">
                    <button type="submit">Search</button>
                </form>
                <a href="{{ route('admin.warehouses.index') }}" class="btn-back">Reset</a>
            </div>
        </div>

        <table class="warehouse-table">
            <thead>
                <tr>
                    <th>Sr. No.</th>
                    <th>Name</th>
                    <th>Code</th>
                    <th>Contact Person</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($warehouses as $warehouse)
                    <tr>
                        <td data-label="#">{{ $loop->iteration }}</td>
                        <td data-label="Name"><strong>{{ $warehouse->name }}</strong></td>
                        <td data-label="Code">{{ $warehouse->code }}</td>
                        <td data-label="Contact Person">{{ $warehouse->contact_person ?? '—' }}</td>
                        <td data-label="Phone">{{ $warehouse->contact_phone ?? '—' }}</td>
                        <td data-label="Status">
                            <span class="status-badge {{ $warehouse->is_active ? 'status-active' : 'status-inactive' }}">
                                {{ $warehouse->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td data-label="Actions">
                            <div class="action-buttons">
                                <form action="{{ route('admin.warehouses.toggle-status', $warehouse->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('POST')
                                    <button type="submit" class="btn-toggle" onclick="return confirm('Toggle warehouse status?')">
                                        {{ $warehouse->is_active ? '🔴 Deactivate' : '🟢 Activate' }}
                                    </button>
                                </form>
                                <a href="{{ route('admin.warehouses.edit', $warehouse->id) }}" class="btn-edit">✎ Edit</a>
                                <form action="{{ route('admin.warehouses.destroy', $warehouse->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-delete" onclick="return confirm('Delete this warehouse? This will remove all associated stock and assignments if any.')">
                                        🗑 Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 3rem;">No warehouses found. Click "Add New Warehouse" to create one.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="d-flex justify-content-center mt-4">
            {{ $warehouses->appends(request()->query())->links() }}
        </div>
    </div>

    <script>
        setTimeout(() => {
            const successAlert = document.querySelector('.alert-success-custom');
            if (successAlert) {
                successAlert.style.transition = 'opacity 0.5s ease';
                successAlert.style.opacity = '0';
                setTimeout(() => successAlert.remove(), 500);
            }
            const errorAlerts = document.querySelectorAll('.alert-error-custom');
            errorAlerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 3000);
    </script>
@endsection