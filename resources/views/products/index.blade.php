@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Products</h1>
        <div>
            <a href="{{ route('products.sync') }}" class="btn btn-primary me-2">Sync Products</a>
            <a href="{{ route('products.export') }}" class="btn btn-success">Export to CSV</a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">Filter Products</div>
        <div class="card-body">
            <form method="GET" action="{{ route('products.index') }}">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="name" class="form-label">Product Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ request('name') }}" placeholder="Product Name">
                    </div>
                    <div class="col-md-4">
                        <label for="sku" class="form-label">SKU</label>
                        <input type="text" class="form-control" id="sku" name="sku" value="{{ request('sku') }}" placeholder="SKU">
                    </div>
                    <div class="col-md-4">
                        <label for="store_name" class="form-label">Store Name</label>
                        <input type="text" class="form-control" id="store_name" name="store_name" value="{{ request('store_name') }}" placeholder="Store Name">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                        <a href="{{ route('products.index') }}" class="btn btn-secondary">Clear Filters</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col" style="width: 10%;">Image</th>
                            <th scope="col">Name</th>
                            <th scope="col">Store</th>
                            <th scope="col">SKU</th>
                            <th scope="col">Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $product)
                            <tr>
                                <td>
                                    @if($product->image_url)
                                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="img-thumbnail" style="max-width: 75px;">
                                    @else
                                        <div class="img-thumbnail text-center bg-light" style="width: 75px; height: 75px; line-height: 75px;">No Image</div>
                                    @endif
                                </td>
                                <td>{{ $product->name }}</td>
                                <td>
                                    <span class="badge bg-secondary">{{ $product->store->name }}</span>
                                </td>
                                <td>{{ $product->sku }}</td>
                                <td>${{ number_format($product->price, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No products found. Try syncing.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {{ $products->links() }}
        </div>
    </div>
</div>
@endsection
