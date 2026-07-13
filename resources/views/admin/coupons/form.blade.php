<div class="row g-4">

    <div class="col-md-6">
        <div class="form-group">
            <label class="form-label fw-semibold" style="color: #495057; margin-bottom: 0.5rem;">
                Coupon Code <span class="text-danger">*</span>
            </label>
            <input type="text"
                   name="code"
                   class="form-control @error('code') is-invalid @enderror"
                   style="border-radius: 10px; padding: 0.6rem 1rem; border: 1px solid #e9ecef;"
                   value="{{ old('code', $coupon->code ?? '') }}"
                   placeholder="e.g., SUMMER2024"
                   required>
            <small class="text-muted">Unique code customers will enter at checkout</small>
            @error('code')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label class="form-label fw-semibold" style="color: #495057; margin-bottom: 0.5rem;">
                Discount Type <span class="text-danger">*</span>
            </label>

            <select name="type"
                    id="discountType"
                    class="form-select @error('type') is-invalid @enderror"
                    style="border-radius: 10px; padding: 0.6rem 1rem; border: 1px solid #e9ecef;"
                    required>
                <option value="fixed" {{ old('type', $coupon->type ?? '') == 'fixed' ? 'selected' : '' }}>
                     Fixed Amount (₹)
                </option>
                <option value="percentage" {{ old('type', $coupon->type ?? '') == 'percentage' ? 'selected' : '' }}>
                    📊 Percentage (%)
                </option>
            </select>

            @error('type')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label class="form-label fw-semibold" style="color: #495057; margin-bottom: 0.5rem;">
                Discount Value <span class="text-danger">*</span>
            </label>

            <div class="input-group">
                <span class="input-group-text"
                      id="discountSymbol"
                      style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 10px 0 0 10px;">
                    {{ old('type', $coupon->type ?? '') == 'percentage' ? '%' : '₹' }}
                </span>

                <input type="number"
                       step="0.01"
                       min="0"
                       oninput="validatePositive(this)"
                       name="value"
                       id="discountValue"
                       class="form-control @error('value') is-invalid @enderror"
                       style="border-radius: 0 10px 10px 0; padding: 0.6rem 1rem; border: 1px solid #e9ecef;"
                       value="{{ old('value', $coupon->value ?? '') }}"
                       placeholder="0.00"
                       required>
            </div>

            <small class="text-muted">For percentage: enter number between 1-100</small>

            @error('value')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label class="form-label fw-semibold" style="color: #495057; margin-bottom: 0.5rem;">
                Eligible User Type
            </label>

            <select name="user_type"
                    class="form-select @error('user_type') is-invalid @enderror"
                    style="border-radius: 10px; padding: 0.6rem 1rem; border: 1px solid #e9ecef;">

                <option value="b2c" {{ old('user_type', $coupon->user_type ?? '') == 'b2c' ? 'selected' : '' }}>
                    👤 B2C (Customer)
                </option>

            </select>

            @error('user_type')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label class="form-label fw-semibold" style="color: #495057; margin-bottom: 0.5rem;">
                Minimum Cart Amount
            </label>

            <div class="input-group">
                <span class="input-group-text"
                      style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 10px 0 0 10px;">₹</span>

                <input type="number"
                       step="0.01"
                       min="0"
                       oninput="validatePositive(this)"
                       name="min_cart_amount"
                       class="form-control @error('min_cart_amount') is-invalid @enderror"
                       style="border-radius: 0 10px 10px 0; padding: 0.6rem 1rem; border: 1px solid #e9ecef;"
                       value="{{ old('min_cart_amount', $coupon->min_cart_amount ?? '') }}"
                       placeholder="0.00">
            </div>

            <small class="text-muted">Minimum order value required to apply coupon</small>

            @error('min_cart_amount')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label class="form-label fw-semibold" style="color: #495057; margin-bottom: 0.5rem;">
                Maximum Discount Amount
            </label>

            <div class="input-group">
                <span class="input-group-text"
                      style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 10px 0 0 10px;">₹</span>

                <input type="number"
                       step="0.01"
                       min="0"
                       oninput="validatePositive(this)"
                       name="max_discount_amount"
                       class="form-control @error('max_discount_amount') is-invalid @enderror"
                       style="border-radius: 0 10px 10px 0; padding: 0.6rem 1rem; border: 1px solid #e9ecef;"
                       value="{{ old('max_discount_amount', $coupon->max_discount_amount ?? '') }}"
                       placeholder="0.00">
            </div>

            <small class="text-muted">Maximum discount amount (useful for percentage coupons)</small>

            @error('max_discount_amount')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label class="form-label fw-semibold" style="color: #495057; margin-bottom: 0.5rem;">
                Total Usage Limit
            </label>

            <input type="number"
                   min="0"
                   oninput="validatePositive(this)"
                   name="usage_limit"
                   class="form-control @error('usage_limit') is-invalid @enderror"
                   style="border-radius: 10px; padding: 0.6rem 1rem; border: 1px solid #e9ecef;"
                   value="{{ old('usage_limit', $coupon->usage_limit ?? '') }}"
                   placeholder="Leave empty for unlimited">

            <small class="text-muted">Maximum number of times this coupon can be used overall</small>

            @error('usage_limit')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label class="form-label fw-semibold" style="color: #495057; margin-bottom: 0.5rem;">
                Per User Limit
            </label>

            <input type="number"
                   min="0"
                   oninput="validatePositive(this)"
                   name="per_user_limit"
                   class="form-control @error('per_user_limit') is-invalid @enderror"
                   style="border-radius: 10px; padding: 0.6rem 1rem; border: 1px solid #e9ecef;"
                   value="{{ old('per_user_limit', $coupon->per_user_limit ?? '') }}"
                   placeholder="Leave empty for unlimited">

            <small class="text-muted">How many times a single user can use this coupon</small>

            @error('per_user_limit')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label class="form-label fw-semibold" style="color: #495057; margin-bottom: 0.5rem;">
                Start Date & Time
            </label>

            <input type="datetime-local"
                   name="starts_at"
                   class="form-control @error('starts_at') is-invalid @enderror"
                   style="border-radius: 10px; padding: 0.6rem 1rem; border: 1px solid #e9ecef;"
                   value="{{ isset($coupon->starts_at) ? \Carbon\Carbon::parse($coupon->starts_at)->format('Y-m-d\TH:i') : '' }}">

            <small class="text-muted">Leave empty to activate immediately</small>

            @error('starts_at')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label class="form-label fw-semibold" style="color: #495057; margin-bottom: 0.5rem;">
                Expiry Date & Time
            </label>

            <input type="datetime-local"
                   name="expires_at"
                   class="form-control @error('expires_at') is-invalid @enderror"
                   style="border-radius: 10px; padding: 0.6rem 1rem; border: 1px solid #e9ecef;"
                   value="{{ isset($coupon->expires_at) ? \Carbon\Carbon::parse($coupon->expires_at)->format('Y-m-d\TH:i') : '' }}">

            <small class="text-muted">Coupon will expire after this date & time</small>

            @error('expires_at')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label class="form-label fw-semibold" style="color: #495057; margin-bottom: 0.5rem;">
                Status
            </label>

            <select name="status"
                    class="form-select @error('status') is-invalid @enderror"
                    style="border-radius: 10px; padding: 0.6rem 1rem; border: 1px solid #e9ecef;">

                <option value="1" {{ old('status', $coupon->status ?? 1) == 1 ? 'selected' : '' }}>
                    ✅ Active
                </option>

                <option value="0" {{ old('status', $coupon->status ?? 1) == 0 ? 'selected' : '' }}>
                    ❌ Inactive
                </option>

            </select>

            @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

</div>

@push('styles')
<style>
    .form-group .form-control:focus,
    .form-group .form-select:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
        outline: none;
    }

    .form-group label {
        font-size: 0.9rem;
    }

    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    input[type=number] {
        -moz-appearance: textfield;
    }
</style>
@endpush

@push('scripts')
<script>

    /*
    |--------------------------------------------------------------------------
    | PREVENT NEGATIVE VALUES
    |--------------------------------------------------------------------------
    */

    function validatePositive(input) {

        if (input.value < 0) {
            input.value = 0;
        }

    }

    /*
    |--------------------------------------------------------------------------
    | CHANGE SYMBOL BASED ON TYPE
    |--------------------------------------------------------------------------
    */

    const discountType = document.getElementById('discountType');
    const discountSymbol = document.getElementById('discountSymbol');
    const discountValue = document.getElementById('discountValue');

    function updateDiscountType() {

        if (discountType.value === 'percentage') {

            discountSymbol.innerHTML = '%';
            discountValue.setAttribute('max', '100');

        } else {

            discountSymbol.innerHTML = '₹';
            discountValue.removeAttribute('max');

        }

    }

    updateDiscountType();

    discountType.addEventListener('change', updateDiscountType);

</script>
@endpush