<div class="mb-3">
    <label for="name" class="form-label">Store Name</label>
    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $store->name ?? '') }}" required>
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="platform" class="form-label">Platform</label>
    <select class="form-select @error('platform') is-invalid @enderror" id="platform" name="platform" required>
        <option value="">Select Platform</option>
        <option value="shopify" {{ old('platform', $store->platform ?? '') == 'shopify' ? 'selected' : '' }}>Shopify</option>
        <option value="woocommerce" {{ old('platform', $store->platform ?? '') == 'woocommerce' ? 'selected' : '' }}>WooCommerce</option>
    </select>
    @error('platform')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="store_url" class="form-label">Store URL</label>
    <input type="url" class="form-control @error('store_url') is-invalid @enderror" id="store_url" name="store_url" value="{{ old('store_url', $store->store_url ?? '') }}" placeholder="https://your-store.com" required>
    @error('store_url')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div id="api_fields" style="display: none;">
    <div class="mb-3">
        <label for="api_key" class="form-label">API Key</label>
        <input type="text" class="form-control @error('api_key') is-invalid @enderror" id="api_key" name="api_key" value="{{ old('api_key', $store->api_key ?? '') }}">
        @error('api_key')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="mb-3">
        <label for="api_secret" class="form-label">API Secret</label>
        <input type="text" class="form-control @error('api_secret') is-invalid @enderror" id="api_secret" name="api_secret" value="{{ old('api_secret', $store->api_secret ?? '') }}">
        @error('api_secret')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div id="shopify_token_field" style="display: none;">
    <div class="mb-3">
        <label for="access_token" class="form-label">Shopify Admin Access Token</label>
        <input type="text" class="form-control @error('access_token') is-invalid @enderror" id="access_token" name="access_token" value="{{ old('access_token', $store->access_token ?? '') }}">
        @error('access_token')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>


@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const platformSelect = document.getElementById('platform');
        const apiFields = document.getElementById('api_fields');
        const shopifyTokenField = document.getElementById('shopify_token_field');

        function togglePlatformFields() {
            const selectedPlatform = platformSelect.value;
            
            if (selectedPlatform === 'shopify' || selectedPlatform === 'woocommerce') {
                apiFields.style.display = 'block';
                apiFields.querySelectorAll('input').forEach(input => input.required = true);
            } else {
                apiFields.style.display = 'none';
                apiFields.querySelectorAll('input').forEach(input => input.required = false);
            }

            if (selectedPlatform === 'shopify') {
                shopifyTokenField.style.display = 'block';
                shopifyTokenField.querySelector('input').required = true;
            } else {
                shopifyTokenField.style.display = 'none';
                shopifyTokenField.querySelector('input').required = false;
            }
        }

        platformSelect.addEventListener('change', togglePlatformFields);

        // Initial call to set visibility based on current value
        togglePlatformFields();
    });
</script>
@endpush
