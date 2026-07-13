<div class="card border-0 shadow-sm rounded-4" style="background: #fffdf9;">
    <div class="card-body p-4">
        <div class="row g-4">
            <!-- Warehouse Name -->
            <div class="col-md-6">
                <label class="form-label fw-semibold">Warehouse Name <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-building"></i></span>
                    <input type="text" name="name" class="form-control bg-light border-start-0" 
                           placeholder="e.g., Delhi Warehouse" 
                           value="{{ old('name', $warehouse->name ?? '') }}" required>
                </div>
                @error('name')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <!-- Warehouse Code -->
            <div class="col-md-6">
                <label class="form-label fw-semibold">Warehouse Code <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-hashtag"></i></span>
                    <input type="text" name="code" class="form-control bg-light border-start-0" 
                           placeholder="e.g., DEL01" 
                           value="{{ old('code', $warehouse->code ?? '') }}" required>
                </div>
                @error('code')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <!-- Address -->
            <div class="col-12">
                <label class="form-label fw-semibold">Address</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="fas fa-location-dot"></i></span>
                    <textarea name="address" class="form-control bg-light" rows="2" placeholder="Full address...">{{ old('address', $warehouse->address ?? '') }}</textarea>
                </div>
                @error('address')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <!-- City -->
            <div class="col-md-4">
                <label class="form-label fw-semibold">City</label>
                <input type="text" name="city" class="form-control bg-light" 
                       value="{{ old('city', $warehouse->city ?? '') }}" placeholder="City">
                @error('city')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <!-- State -->
            <div class="col-md-4">
                <label class="form-label fw-semibold">State</label>
                <input type="text" name="state" class="form-control bg-light" 
                       value="{{ old('state', $warehouse->state ?? '') }}" placeholder="State">
                @error('state')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <!-- Pin Code -->
            <div class="col-md-4">
                <label class="form-label fw-semibold">Pin Code</label>
                <input type="text" name="pin_code" class="form-control bg-light" 
                       value="{{ old('pin_code', $warehouse->pin_code ?? '') }}" placeholder="Postal code">
                @error('pin_code')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <!-- Country -->
            <div class="col-md-4">
                <label class="form-label fw-semibold">Country</label>
                <input type="text" name="country" class="form-control bg-light" 
                       value="{{ old('country', $warehouse->country ?? 'India') }}" placeholder="Country">
                @error('country')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <!-- Contact Person -->
            <div class="col-md-4">
                <label class="form-label fw-semibold">Contact Person</label>
                <input type="text" name="contact_person" class="form-control bg-light" 
                       value="{{ old('contact_person', $warehouse->contact_person ?? '') }}" placeholder="Name">
                @error('contact_person')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <!-- Contact Phone -->
            <div class="col-md-4">
                <label class="form-label fw-semibold">Contact Phone</label>
                <input type="text" name="contact_phone" class="form-control bg-light" 
                       value="{{ old('contact_phone', $warehouse->contact_phone ?? '') }}" placeholder="Phone number">
                @error('contact_phone')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <!-- Status (Active/Inactive) -->
            <div class="col-md-12">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" 
                           id="isActive" {{ old('is_active', isset($warehouse) ? $warehouse->is_active : true) ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold" for="isActive">Active Warehouse</label>
                </div>
                <small class="text-muted">Inactive warehouses won't appear in stock allocation dropdowns.</small>
                @error('is_active')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="d-flex justify-content-end gap-3 mt-5 pt-3 border-top">
            <a href="{{ route('admin.warehouses.index') }}" class="btn btn-outline-secondary px-4 rounded-pill">Cancel</a>
            <button type="submit" class="btn btn-primary-custom px-5 rounded-pill" 
                    style="background: linear-gradient(135deg, #2c1810, #1f110a); color: #fef5e8; border: none;">
                {{ isset($warehouse) ? 'Update Warehouse' : 'Create Warehouse' }}
            </button>
        </div>
    </div>
</div>

<style>
    .btn-primary-custom:hover {
        background: linear-gradient(135deg, #3f2a1f, #2c1810) !important;
        transform: translateY(-1px);
        transition: all 0.2s ease;
    }
    .form-control.bg-light, .input-group-text.bg-light {
        background-color: #fefcf8 !important;
        border-color: #e2d5c8;
    }
    .form-control:focus {
        box-shadow: none;
        border-color: #c4a27a;
    }
</style>