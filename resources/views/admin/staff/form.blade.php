@csrf

<div class="staff-form-fields">
    <!-- Name Field -->
    <div class="form-group">
        <label for="full_name">
            <span class="label-icon">👤</span>
            Full Name
            <span class="required-star">*</span>
        </label>
        <input type="text" name="full_name" id="full_name" class="form-control @error('full_name') is-invalid @enderror"
            value="{{ old('full_name', $user->full_name ?? '') }}" placeholder="Enter full name" autocomplete="name">
        @error('full_name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Email Field -->
    <div class="form-group">
        <label for="email">
            <span class="label-icon">📧</span>
            Email Address
            <span class="required-star">*</span>
        </label>
        <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror"
            value="{{ old('email', $user->email ?? '') }}" placeholder="staff@example.com" autocomplete="email">
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Phone Field -->
    <div class="form-group">
        <label for="phone">
            <span class="label-icon">📞</span>
            Phone Number
        </label>
        <input type="tel" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror"
            value="{{ old('phone', $user->phone ?? '') }}" placeholder="+1 234 567 8900" autocomplete="tel">
        @error('phone')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Password Field -->
    <div class="form-group">
        <label for="password">
            <span class="label-icon">🔒</span>
            {{ isset($user) ? 'New Password' : 'Password' }}
            @if (!isset($user))
                <span class="required-star">*</span>
            @endif
        </label>
        <div class="password-wrapper">
            <input type="password" name="password" id="password"
                class="form-control @error('password') is-invalid @enderror" {{ isset($user) ? '' : 'required' }}
                placeholder="{{ isset($user) ? 'Enter new password (optional)' : 'Create a password' }}">
            <button type="button" class="toggle-password" data-target="password">
                👁️
            </button>
        </div>
        @if (isset($user))
            <div class="hint-text">
                <span class="hint-icon">💡</span>
                Leave blank to keep the current password
            </div>
        @endif
        @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Password Confirmation Field (for create) -->
    @if (!isset($user))
        <div class="form-group">
            <label for="password_confirmation">
                <span class="label-icon">🔒</span>
                Confirm Password
                <span class="required-star">*</span>
            </label>
            <div class="password-wrapper">
                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control"
                    placeholder="Confirm your password">
                <button type="button" class="toggle-password" data-target="password_confirmation">
                    👁️
                </button>
            </div>
        </div>
    @endif

    <!-- Role Field -->
    <div class="form-group">
        <label for="role_id">
            <span class="label-icon">🎭</span>
            Assign Role
            <span class="required-star">*</span>
        </label>
        <select name="role_id" id="role_id" class="form-control @error('role_id') is-invalid @enderror">
            <option value="">— Select a Role —</option>
            @foreach ($roles as $role)
                <option value="{{ $role->id }}"
                    {{ old('role_id', isset($user) ? $user->roles->first()->id ?? '' : '') == $role->id ? 'selected' : '' }}>
                    {{ $role->name }}
                </option>
            @endforeach
        </select>
        @error('role_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Warehouse Field (NEW) -->
    <div class="form-group" id="warehouse-group" style="display: none;">
        <label for="warehouse_id">
            <span class="label-icon">🏢</span>
            Assigned Warehouse
            <span class="required-star" id="warehouse-required-star">*</span>
        </label>
        <select name="warehouse_id" id="warehouse_id" class="form-control @error('warehouse_id') is-invalid @enderror">
            <option value="">— Select Warehouse —</option>
            @isset($warehouses)
                @foreach ($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}"
                        {{ old('warehouse_id', isset($user) ? $user->warehouse_id : '') == $warehouse->id ? 'selected' : '' }}>
                        {{ $warehouse->name }} ({{ $warehouse->code }})
                    </option>
                @endforeach
            @endisset
        </select>
        @error('warehouse_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="hint-text">
            <span class="hint-icon">📌</span>
            Warehouse Manager must be assigned a warehouse. Sales Executive optional.
        </div>
    </div>

    <!-- Form Actions -->
    <div class="form-actions ">
        <button type="submit" class="btn-submit" style="text-align:center,justify-content: end;">
            <span class="btn-icon">{{ isset($user) ? '💾' : '✓' }}</span>
            {{ isset($user) ? 'Update Staff' : 'Create Staff' }}
        </button>
        <a href="{{ route('admin.staff.index') }}" class="btn-back">
            <span class="btn-icon">←</span>
            Cancel
        </a>
    </div>
</div>

<style>
    /* Modern Form Styling with Georgia Serif */
    .staff-form-fields {
        /* max-width: 680px; */
        margin: 0 auto;
    }

    .form-group {
        margin-bottom: 1.75rem;
    }

    .form-group label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-family: 'Georgia', 'Times New Roman', serif;
        font-size: 0.85rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: #5c4b3a;
        margin-bottom: 0.6rem;
    }

    .label-icon {
        font-size: 1rem;
        opacity: 0.7;
    }

    .required-star {
        color: #c97e5a;
        font-size: 1rem;
        margin-left: 0.2rem;
    }

    .form-control {
        width: 100%;
        padding: 0.85rem 1rem;
        font-family: 'Georgia', 'Times New Roman', serif;
        font-size: 0.95rem;
        color: #2c1810;
        background: #fefcf9;
        border: 1.5px solid #e8dfd5;
        border-radius: 14px;
        transition: all 0.25s ease;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
    }

    .form-control:focus {
        outline: none;
        border-color: #c4a27a;
        background: #ffffff;
        box-shadow: 0 0 0 4px rgba(196, 162, 122, 0.1);
    }

    .form-control.is-invalid {
        border-color: #dc9a7a;
        background: #fffaf8;
    }

    .invalid-feedback {
        font-family: 'Georgia', 'Times New Roman', serif;
        font-size: 0.75rem;
        color: #c97e5a;
        margin-top: 0.4rem;
        padding-left: 0.5rem;
        border-left: 2px solid #c97e5a;
    }

    /* Password toggle wrapper */
    .password-wrapper {
        position: relative;
    }

    .password-wrapper .form-control {
        padding-right: 3rem;
    }

    .toggle-password {
        position: absolute;
        right: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        background: transparent;
        border: none;
        cursor: pointer;
        font-size: 1rem;
        padding: 0.25rem;
        opacity: 0.5;
        transition: opacity 0.2s;
    }

    .toggle-password:hover {
        opacity: 1;
    }

    select.form-control {
        cursor: pointer;
        appearance: none;
        background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%238b735a' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 1rem center;
        background-size: 1.2rem;
    }

    select.form-control option {
        padding: 0.5rem;
        font-family: 'Georgia', 'Times New Roman', serif;
    }

    .hint-text {
        font-family: 'Georgia', 'Times New Roman', serif;
        font-size: 0.7rem;
        color: #a28d74;
        margin-top: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.4rem;
        font-style: italic;
    }

    .hint-icon {
        font-size: 0.8rem;
    }

    /* Form Actions */
    .form-actions {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
        justify-content: end;
        padding-top: 1.5rem;
        border-top: 2px solid #f0e6dc;
    }

    .btn-submit {
        display: inline-flex;
        align-items: center;
        gap: 0.6rem;
        padding: 0.8rem 1.8rem;
        background: linear-gradient(135deg, #2c1810, #1f110a);
        color: #f9efdf;
        border: none;
        border-radius: 40px;
        font-family: 'Georgia', 'Times New Roman', serif;
        font-size: 0.9rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.25s ease;
        letter-spacing: 0.02em;
    }

    .btn-submit:hover {
        transform: translateY(-2px);
        background: linear-gradient(135deg, #3f2a1f, #2c1810);
        box-shadow: 0 6px 14px rgba(44, 24, 16, 0.2);
    }

    .btn-submit:active {
        transform: translateY(0);
    }

    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 0.6rem;
        padding: 0.8rem 1.8rem;
        background: transparent;
        border: 1.5px solid #d4c4b4;
        border-radius: 40px;
        color: #5c4b3a;
        font-family: 'Georgia', 'Times New Roman', serif;
        font-size: 0.9rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.25s ease;
        cursor: pointer;
    }

    .btn-back:hover {
        background: #f8f3ed;
        border-color: #bea587;
        color: #2c1810;
        text-decoration: none;
        transform: translateY(-1px);
    }

    .btn-icon {
        font-size: 1rem;
    }

    /* Responsive Design */
    @media (max-width: 520px) {
        .form-actions {
            flex-direction: column;
            gap: 0.8rem;
        }

        .btn-submit,
        .btn-back {
            justify-content: center;
        }

        .form-group label {
            font-size: 0.75rem;
        }

        .form-control {
            padding: 0.7rem 0.9rem;
            font-size: 0.9rem;
        }
    }

    /* Animation for form fields */
    .form-control {
        animation: fadeInUp 0.3s ease-out;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Stagger animation for form groups */
    .form-group:nth-child(1) { animation: fadeInUp 0.2s ease-out; }
    .form-group:nth-child(2) { animation: fadeInUp 0.25s ease-out; }
    .form-group:nth-child(3) { animation: fadeInUp 0.3s ease-out; }
    .form-group:nth-child(4) { animation: fadeInUp 0.35s ease-out; }
    .form-group:nth-child(5) { animation: fadeInUp 0.4s ease-out; }
    .form-group:nth-child(6) { animation: fadeInUp 0.45s ease-out; }
    .form-group:nth-child(7) { animation: fadeInUp 0.5s ease-out; }
    .form-actions { animation: fadeInUp 0.55s ease-out; }
</style>

<script>
    // Password visibility toggle functionality
    document.addEventListener('DOMContentLoaded', function() {
        const toggleButtons = document.querySelectorAll('.toggle-password');
        toggleButtons.forEach(button => {
            button.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const passwordInput = document.getElementById(targetId);
                if (passwordInput) {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    this.textContent = type === 'password' ? '🙈' : '👁️';
                }
            });
        });

        // Show/hide warehouse field based on role selection
        const roleSelect = document.getElementById('role_id');
        const warehouseGroup = document.getElementById('warehouse-group');
        const warehouseRequiredStar = document.getElementById('warehouse-required-star');

        function toggleWarehouseField() {
            if (!roleSelect || !warehouseGroup) return;
            const selectedRoleId = parseInt(roleSelect.value);
            // Role ID 2 = Warehouse Manager (from roles table)
            const isWarehouseManager = selectedRoleId === 2;
            warehouseGroup.style.display = isWarehouseManager ? 'block' : 'none';
            const warehouseSelect = document.getElementById('warehouse_id');
            if (warehouseSelect) {
                if (isWarehouseManager) {
                    warehouseSelect.setAttribute('required', 'required');
                    if (warehouseRequiredStar) warehouseRequiredStar.style.display = 'inline';
                } else {
                    warehouseSelect.removeAttribute('required');
                    if (warehouseRequiredStar) warehouseRequiredStar.style.display = 'none';
                }
            }
        }

        if (roleSelect) {
            roleSelect.addEventListener('change', toggleWarehouseField);
            toggleWarehouseField(); // initial state
        }
    });
</script>