@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('admin.notifications.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
                <i class="bi bi-arrow-left me-1"></i>Back to Notifications
            </a>
            <h2 class="text-3xl font-bold" style="color: #2a1a05; font-family: Georgia, serif;">
                {{ $notification->title }}
            </h2>
        </div>
        <div class="d-flex gap-2">
            @if (!$notification->is_read)
                <button class="btn btn-sm btn-success" onclick="markAsRead()">
                    <i class="bi bi-check-circle me-1"></i>Mark as Read
                </button>
            @else
                <button class="btn btn-sm btn-warning" onclick="markAsUnread()">
                    <i class="bi bi-circle me-1"></i>Mark as Unread
                </button>
            @endif
            <a href="{{ route('admin.notifications.pdf', $notification->id) }}" class="btn btn-sm btn-info">
                <i class="bi bi-file-pdf me-1"></i>Download PDF
            </a>
            <button class="btn btn-sm btn-danger" onclick="deleteNotification()">
                <i class="bi bi-trash me-1"></i>Delete
            </button>
        </div>
    </div>

    <!-- Notification Details Card -->
    <div style="background: linear-gradient(160deg, #fdf8f0 0%, #faf3e8 100%); 
                border: 1px solid rgba(162, 128, 81, 0.2); border-radius: 16px; 
                padding: 32px; margin-bottom: 24px;">
        
        <!-- Status Badges -->
        <div class="mb-4">
            <div class="d-flex flex-wrap gap-2">
                <span class="badge" style="background-color: rgba(162, 128, 81, 0.15); color: #2a1a05; padding: 8px 12px; font-size: 12px;">
                    <i class="bi bi-tag me-1"></i>{{ ucfirst(str_replace('_', ' ', $notification->type)) }}
                </span>
                <span class="badge" style="background-color: 
                    @if($notification->priority === 'high')
                        rgba(239, 68, 68, 0.15); color: #dc2626;
                    @elseif($notification->priority === 'medium')
                        rgba(245, 158, 11, 0.15); color: #d97706;
                    @else
                        rgba(34, 197, 94, 0.15); color: #16a34a;
                    @endif
                ; padding: 8px 12px; font-size: 12px;">
                    <i class="bi bi-exclamation-circle me-1"></i>{{ ucfirst($notification->priority) }} Priority
                </span>
                <span class="badge" style="background-color: 
                    @if($notification->is_read)
                        rgba(34, 197, 94, 0.15); color: #16a34a;
                    @else
                        rgba(59, 130, 246, 0.15); color: #1e40af;
                    @endif
                ; padding: 8px 12px; font-size: 12px;">
                    <i class="bi {{ $notification->is_read ? 'bi-check-circle' : 'bi-circle' }} me-1"></i>
                    {{ $notification->is_read ? 'Read' : 'Unread' }}
                </span>
            </div>
        </div>

        <!-- Info Grid -->
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div>
                    <label style="display: block; font-size: 12px; color: #999; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; font-weight: 600;">Created Date</label>
                    <p style="margin: 0; font-size: 16px; color: #2a1a05; font-weight: 500;">
                        {{ $notification->created_at->format('d M Y') }}
                    </p>
                    <p style="margin: 0; font-size: 13px; color: #999;">
                        {{ $notification->created_at->format('H:i A') }}
                    </p>
                </div>
            </div>
            <div class="col-md-6">
                <div>
                    <label style="display: block; font-size: 12px; color: #999; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; font-weight: 600;">Read Date</label>
                    <p style="margin: 0; font-size: 16px; color: #2a1a05; font-weight: 500;">
                        @if($notification->read_at)
                            {{ $notification->read_at->format('d M Y') }}
                        @else
                            <span style="color: #999;">Not read yet</span>
                        @endif
                    </p>
                    @if($notification->read_at)
                        <p style="margin: 0; font-size: 13px; color: #999;">
                            {{ $notification->read_at->format('H:i A') }}
                        </p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Reference Information -->
        @if($notification->reference_type && $notification->reference_id)
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <div>
                        <label style="display: block; font-size: 12px; color: #999; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; font-weight: 600;">Reference Type</label>
                        <p style="margin: 0; font-size: 16px; color: #2a1a05; font-weight: 500;">
                            {{ ucfirst($notification->reference_type) }}
                        </p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div>
                        <label style="display: block; font-size: 12px; color: #999; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; font-weight: 600;">Reference ID</label>
                        <p style="margin: 0; font-size: 16px; color: #2a1a05; font-weight: 500;">
                            #{{ $notification->reference_id }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Message Content -->
        <div class="mb-4">
            <label style="display: block; font-size: 12px; color: #999; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 12px; font-weight: 600;">Full Message</label>
            <div style="background: white; border: 1px solid rgba(162, 128, 81, 0.15); 
                        border-radius: 8px; padding: 16px; color: #2a1a05; line-height: 1.6;">
                {{ $notification->message }}
            </div>
        </div>

        <!-- Extra Data -->
        @if($notification->extra_data && count($notification->extra_data) > 0)
            <div>
                <label style="display: block; font-size: 12px; color: #999; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 12px; font-weight: 600;">Additional Information</label>
                <div style="background: white; border: 1px solid rgba(162, 128, 81, 0.15); 
                            border-radius: 8px; padding: 16px; overflow-x: auto;">
                    <pre style="margin: 0; font-size: 12px; color: #2a1a05;">{{ json_encode($notification->extra_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                </div>
            </div>
        @endif
    </div>

    <!-- Related Actions -->
    <div class="row">
        @if($notification->reference_type && $notification->reference_id)
            <div class="col-md-12 mb-4">
                <div style="background: white; border: 1px solid rgba(162, 128, 81, 0.2); 
                            border-radius: 12px; padding: 24px; text-align: center;">
                    <h6 style="color: #2a1a05; margin-bottom: 16px;">
                        <i class="bi bi-arrow-right me-2"></i>Go to Related {{ ucfirst($notification->reference_type) }}
                    </h6>
                    <button class="btn btn-lg" 
                            style="background: linear-gradient(135deg, #2a1a05 0%, #1a0f00 100%); color: white;"
                            onclick="goToReference()">
                        <i class="bi bi-box-arrow-up-right me-2"></i>View {{ ucfirst($notification->reference_type) }} #{{ $notification->reference_id }}
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
    const notificationId = '{{ $notification->id }}';

    function markAsRead() {
        fetch(`{{ route('admin.notifications.read', ':id') }}`.replace(':id', notificationId), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                'Content-Type': 'application/json'
            }
        })
        .then(() => {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Notification marked as read',
                timer: 2000,
                showConfirmButton: false
            }).then(() => location.reload());
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to update notification' });
        });
    }

    function markAsUnread() {
        fetch(`{{ route('admin.notifications.unread', ':id') }}`.replace(':id', notificationId), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                'Content-Type': 'application/json'
            }
        })
        .then(() => {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Notification marked as unread',
                timer: 2000,
                showConfirmButton: false
            }).then(() => location.reload());
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to update notification' });
        });
    }

    function deleteNotification() {
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
                fetch(`{{ route('admin.notifications.delete', ':id') }}`.replace(':id', notificationId), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                        'Content-Type': 'application/json'
                    }
                })
                .then(() => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted',
                        text: 'Notification deleted successfully',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => window.location.href = '{{ route('admin.notifications.index') }}');
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to delete notification' });
                });
            }
        });
    }

    function goToReference() {
        const referenceType = '{{ $notification->reference_type }}';
        const referenceId = '{{ $notification->reference_id }}';

        const routes = {
            'order': `{{ route('admin.orders.show', ':id') }}`.replace(':id', referenceId),
            'user': `{{ route('admin.users.show', ':id') }}`.replace(':id', referenceId),
            'ticket': `{{ route('admin.support-tickets.show', ':id') }}`.replace(':id', referenceId),
            'contact': `{{ route('admin.contact_requests.show', ':id') }}`.replace(':id', referenceId),
        };

        if (routes[referenceType]) {
            window.location.href = routes[referenceType];
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Cannot navigate to this reference type' });
        }
    }
</script>
@endsection
