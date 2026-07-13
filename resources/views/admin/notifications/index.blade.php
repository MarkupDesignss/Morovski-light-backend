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
    <div class="container-fluid px-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 style="color: #2a1a05; font-family: Georgia, serif;">
                    <i class="bi bi-bell-fill me-2"></i>Notifications
                </h2>
                <p class="text-muted small">Manage your admin notifications</p>
            </div>

            <div class="d-flex gap-2">
                <!-- Back Button -->

                <a href="{{ url()->previous() }}" class="btn-back">← Back</a>

                <!-- Delete All Button -->
                <button class="btn btn-sm btn-back"
                    style="background: linear-gradient(135deg, #2a1a05 0%, #1a0f00 100%); color: white;"
                    onclick="deleteAllNotifications()">
                    <i class="bi bi-trash me-1"></i>Delete All
                </button>
            </div>
        </div>

        <!-- Success/Error Messages -->
        <div id="alertContainer"></div>

        <!-- Filter Options -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div
                    style="background: linear-gradient(160deg, #fdf8f0 0%, #faf3e8 100%);
                            border: 1px solid rgba(162, 128, 81, 0.2); border-radius: 12px; padding: 16px;">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <select id="filterStatus" class="form-select" style="border-color: rgba(162, 128, 81, 0.3);">
                                <option value="">All Notifications</option>
                                <option value="unread" {{ request('status') == 'unread' ? 'selected' : '' }}>Unread Only
                                </option>
                                <option value="read" {{ request('status') == 'read' ? 'selected' : '' }}>Read Only
                                </option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select id="filterPriority" class="form-select" style="border-color: rgba(162, 128, 81, 0.3);">
                                <option value="">All Priorities</option>
                                <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                                <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium
                                </option>
                                <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select id="filterType" class="form-select" style="border-color: rgba(162, 128, 81, 0.3);">
                                <option value="">All Types</option>
                                <option value="new_order" {{ request('type') == 'new_order' ? 'selected' : '' }}>New Order
                                </option>
                                <option value="new_user" {{ request('type') == 'new_user' ? 'selected' : '' }}>New User
                                </option>
                                {{-- <option value="report" {{ request('type') == 'report' ? 'selected' : '' }}>Report</option>
                                <option value="ticket" {{ request('type') == 'ticket' ? 'selected' : '' }}>Ticket</option> --}}
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select id="filterDate" class="form-select" style="border-color: rgba(162, 128, 81, 0.3);">
                                <option value="">All Dates</option>
                                <option value="today" {{ request('date_filter') == 'today' ? 'selected' : '' }}>Today
                                </option>
                                <option value="yesterday" {{ request('date_filter') == 'yesterday' ? 'selected' : '' }}>
                                    Yesterday</option>
                                <option value="earlier" {{ request('date_filter') == 'earlier' ? 'selected' : '' }}>Earlier
                                </option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-outline-secondary w-100" onclick="applyFilters()">
                                <i class="bi bi-funnel me-1"></i>Apply Filters
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notifications Table -->
        <div id="notificationsContainer"
            style="background: white; border: 1px solid rgba(162, 128, 81, 0.2);
                border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">

            @if ($notifications->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead
                            style="background: linear-gradient(90deg, rgba(162, 128, 81, 0.1), rgba(162, 128, 81, 0.04));
                                  border-bottom: 1px solid rgba(162, 128, 81, 0.2);">
                            <tr>
                                <th style="color: #2a1a05; padding: 16px; font-weight: 600;">Type</th>
                                <th style="color: #2a1a05; padding: 16px; font-weight: 600;">Title</th>
                                <th style="color: #2a1a05; padding: 16px; font-weight: 600;">Message</th>
                                <th style="color: #2a1a05; padding: 16px; font-weight: 600; text-align: center;">Priority
                                </th>
                                <th style="color: #2a1a05; padding: 16px; font-weight: 600;">Status</th>
                                <th style="color: #2a1a05; padding: 16px; font-weight: 600;">Date</th>
                                <th style="color: #2a1a05; padding: 16px; font-weight: 600; text-align: center;">Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody id="notificationsTableBody">
                            @php $currentGroup = null; @endphp
                            @foreach ($notifications as $notification)
                                @php
                                    if ($notification->created_at->isToday()) {
                                        $groupLabel = 'Today';
                                    } elseif ($notification->created_at->isYesterday()) {
                                        $groupLabel = 'Yesterday';
                                    } else {
                                        $groupLabel = 'Earlier';
                                    }
                                @endphp

                                @if ($currentGroup !== $groupLabel)
                                    <tr class="notification-group-row" style="background: rgba(162, 128, 81, 0.08);">
                                        <td colspan="7" style="padding: 12px 16px; font-weight: 700; color: #2a1a05;">
                                            {{ $groupLabel }}
                                        </td>
                                    </tr>
                                    @php $currentGroup = $groupLabel; @endphp
                                @endif

                                <tr id="notification-{{ $notification->id }}"
                                    style="border-bottom: 1px solid rgba(162, 128, 81, 0.1); {{ $notification->is_read ? '' : 'background: rgba(162, 128, 81, 0.04);' }}">
                                    <td style="padding: 16px; color: #666;">
                                        <span class="badge"
                                            style="background-color: rgba(162, 128, 81, 0.15); color: #2a1a05;">
                                            {{ ucfirst(str_replace('_', ' ', $notification->type)) }}
                                        </span>
                                    </td>
                                    <td style="padding: 16px; color: #2a1a05; font-weight: 500;">
                                        {{ $notification->title }}
                                    </td>
                                    <td style="padding: 16px; color: #666; max-width: 300px;">
                                        <div
                                            style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                            {{ $notification->message }}
                                        </div>
                                    </td>
                                    <td style="padding: 16px; text-align: center;">
                                        <span class="badge priority-badge-{{ $notification->id }}"
                                            style="background-color:
                                        @if ($notification->priority === 'high') rgba(239, 68, 68, 0.1); color: #dc2626;
                                        @elseif($notification->priority === 'medium') rgba(245, 158, 11, 0.1); color: #d97706;
                                        @else rgba(34, 197, 94, 0.1); color: #16a34a; @endif">
                                            {{ ucfirst($notification->priority) }}
                                        </span>
                                    </td>
                                    <td style="padding: 16px;">
                                        <span id="status-badge-{{ $notification->id }}"
                                            class="badge {{ $notification->is_read ? 'bg-success' : 'bg-warning' }}">
                                            <i
                                                class="bi {{ $notification->is_read ? 'bi-check-circle' : 'bi-circle' }} me-1"></i>
                                            <span
                                                class="status-text">{{ $notification->is_read ? 'Read' : 'Unread' }}</span>
                                        </span>
                                    </td>
                                    <td style="padding: 16px; color: #999; font-size: 13px;">
                                        {{ $notification->created_at->format('d M Y, H:i') }}
                                    </td>
                                    <td style="padding: 16px; text-align: center;">
                                        <div class="btn-group btn-group-sm" role="group">
                                            @if ($notification->is_read)
                                                <button class="btn btn-outline-warning"
                                                    onclick="markAsUnread('{{ $notification->id }}')"
                                                    title="Mark as unread">
                                                    <i class="bi bi-circle"></i>
                                                </button>
                                            @else
                                                <button class="btn btn-outline-success"
                                                    onclick="markAsRead('{{ $notification->id }}')" title="Mark as read">
                                                    <i class="bi bi-check-circle"></i>
                                                </button>
                                            @endif

                                            <button class="btn btn-outline-danger"
                                                onclick="deleteNotification('{{ $notification->id }}')" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div id="paginationContainer"
                    style="padding: 16px; background: rgba(162, 128, 81, 0.03); border-top: 1px solid rgba(162, 128, 81, 0.2);">
                    {{ $notifications->links() }}
                </div>
            @else
                <div id="emptyState" style="padding: 48px; text-align: center;">
                    <div style="font-size: 48px; color: rgba(162, 128, 81, 0.3); margin-bottom: 16px;">
                        <i class="bi bi-inbox"></i>
                    </div>
                    <h5 style="color: #666; margin-bottom: 8px;">No Notifications</h5>
                    <p style="color: #999;">You don't have any notifications yet</p>
                </div>
            @endif
        </div>
    </div>

    <script>
        function showAlert(type, message) {
            const alertContainer = document.getElementById('alertContainer');
            if (!alertContainer) return;

            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            const icon = type === 'success' ? 'bi-check-circle' : 'bi-exclamation-circle';

            const alert = document.createElement('div');
            alert.className = `alert ${alertClass} alert-dismissible fade show`;
            alert.role = 'alert';
            alert.innerHTML = `
                <i class="bi ${icon} me-2"></i>${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;

            alertContainer.appendChild(alert);

            setTimeout(() => {
                alert.classList.remove('show');
                setTimeout(() => alert.remove(), 150);
            }, 3000);
        }

        function markAsRead(notificationId) {
            const url = `/morovski-light/admin/notifications/${notificationId}/read`;

            fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('success', 'Notification marked as read');

                        // Update UI
                        const statusBadge = document.getElementById(`status-badge-${notificationId}`);
                        const actionButton = document.querySelector(
                            `#notification-${notificationId} .btn-outline-success`);

                        if (statusBadge) {
                            statusBadge.className = 'badge bg-success';
                            statusBadge.innerHTML =
                                '<i class="bi bi-check-circle me-1"></i><span class="status-text">Read</span>';
                        }

                        if (actionButton) {
                            actionButton.className = 'btn btn-outline-warning';
                            actionButton.setAttribute('onclick', `markAsUnread('${notificationId}')`);
                            actionButton.innerHTML = '<i class="bi bi-circle"></i>';
                            actionButton.title = 'Mark as unread';
                        }

                        const notificationRow = document.getElementById(`notification-${notificationId}`);
                        if (notificationRow) {
                            notificationRow.style.background = '';
                        }
                    } else {
                        showAlert('error', data.message || 'Failed to mark notification as read');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('error', 'Failed to mark notification as read');
                });
        }

        function markAsUnread(notificationId) {
            const url = `https://www.markupdesigns.net/morovski-light/admin/notifications/${notificationId}/unread`;
                console.log(url)
            fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('success', 'Notification marked as unread');

                        // Update UI
                        const statusBadge = document.getElementById(`status-badge-${notificationId}`);
                        const actionButton = document.querySelector(
                            `#notification-${notificationId} .btn-outline-warning`);

                        if (statusBadge) {
                            statusBadge.className = 'badge bg-warning';
                            statusBadge.innerHTML =
                                '<i class="bi bi-circle me-1"></i><span class="status-text">Unread</span>';
                        }

                        if (actionButton) {
                            actionButton.className = 'btn btn-outline-success';
                            actionButton.setAttribute('onclick', `markAsRead('${notificationId}')`);
                            actionButton.innerHTML = '<i class="bi bi-check-circle"></i>';
                            actionButton.title = 'Mark as read';
                        }

                        const notificationRow = document.getElementById(`notification-${notificationId}`);
                        if (notificationRow) {
                            notificationRow.style.background = 'rgba(162, 128, 81, 0.04)';
                        }
                    } else {
                        showAlert('error', data.message || 'Failed to mark notification as unread');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('error', 'Failed to mark notification as unread');
                });
        }

        function deleteNotification(notificationId) {
            if (typeof Swal === 'undefined') {
                if (confirm('Delete this notification? This action cannot be undone.')) {
                    performDelete(notificationId);
                }
                return;
            }

            Swal.fire({
                title: 'Delete Notification?',
                text: 'This action cannot be undone',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Delete',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#ef4444'
            }).then(result => {
                if (result.isConfirmed) {
                    performDelete(notificationId);
                }
            });
        }

        function performDelete(notificationId) {
            const url = `/admin/notifications/${notificationId}`;

            fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('success', 'Notification deleted successfully');

                        // Remove row from UI
                        const row = document.getElementById(`notification-${notificationId}`);
                        if (row) {
                            const tbody = row.parentElement;
                            row.remove();

                            // Remove empty group rows
                            const groupRows = document.querySelectorAll('.notification-group-row');
                            groupRows.forEach(groupRow => {
                                let hasNextRows = false;
                                let nextRow = groupRow.nextElementSibling;
                                while (nextRow) {
                                    if (nextRow.id && nextRow.id.startsWith('notification-')) {
                                        hasNextRows = true;
                                        break;
                                    }
                                    nextRow = nextRow.nextElementSibling;
                                }
                                if (!hasNextRows && groupRow.nextElementSibling && groupRow.nextElementSibling
                                    .classList &&
                                    groupRow.nextElementSibling.classList.contains('notification-group-row')) {
                                    groupRow.remove();
                                } else if (!hasNextRows) {
                                    groupRow.remove();
                                }
                            });

                            // Check if table is empty
                            const tbodyElement = document.getElementById('notificationsTableBody');
                            if (tbodyElement && tbodyElement.children.length === 0) {
                                const container = document.getElementById('notificationsContainer');
                                if (container) {
                                    container.innerHTML = `
                                    <div style="padding: 48px; text-align: center;">
                                        <div style="font-size: 48px; color: rgba(162, 128, 81, 0.3); margin-bottom: 16px;">
                                            <i class="bi bi-inbox"></i>
                                        </div>
                                        <h5 style="color: #666; margin-bottom: 8px;">No Notifications</h5>
                                        <p style="color: #999;">You don't have any notifications yet</p>
                                    </div>
                                `;
                                }
                            }
                        }
                    } else {
                        showAlert('error', data.message || 'Failed to delete notification');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('error', 'Failed to delete notification');
                });
        }

        function deleteAllNotifications() {
            if (typeof Swal === 'undefined') {
                if (confirm('Delete all notifications? This action cannot be undone.')) {
                    performDeleteAll();
                }
                return;
            }

            Swal.fire({
                title: 'Delete All Notifications?',
                text: 'This action cannot be undone',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Delete All',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#ef4444'
            }).then(result => {
                if (result.isConfirmed) {
                    performDeleteAll();
                }
            });
        }

        function performDeleteAll() {
            const url = '{{ route('admin.notifications.delete_all') }}';

            fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('success', 'All notifications deleted successfully');

                        // Clear the table and show empty state
                        const container = document.getElementById('notificationsContainer');
                        if (container) {
                            container.innerHTML = `
                            <div style="padding: 48px; text-align: center;">
                                <div style="font-size: 48px; color: rgba(162, 128, 81, 0.3); margin-bottom: 16px;">
                                    <i class="bi bi-inbox"></i>
                                </div>
                                <h5 style="color: #666; margin-bottom: 8px;">No Notifications</h5>
                                <p style="color: #999;">You don't have any notifications yet</p>
                            </div>
                        `;
                        }
                    } else {
                        showAlert('error', data.message || 'Failed to delete notifications');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('error', 'Failed to delete notifications');
                });
        }

        function applyFilters() {
            const status = document.getElementById('filterStatus')?.value || '';
            const priority = document.getElementById('filterPriority')?.value || '';
            const type = document.getElementById('filterType')?.value || '';
            const dateFilter = document.getElementById('filterDate')?.value || '';

            let url = '{{ route('admin.notifications.index') }}?';
            const params = [];
            if (status) params.push(`status=${encodeURIComponent(status)}`);
            if (priority) params.push(`priority=${encodeURIComponent(priority)}`);
            if (type) params.push(`type=${encodeURIComponent(type)}`);
            if (dateFilter) params.push(`date_filter=${encodeURIComponent(dateFilter)}`);

            window.location.href = url + params.join('&');
        }
    </script>
@endsection
