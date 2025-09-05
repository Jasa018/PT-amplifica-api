@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Products</h1>
        <a href="{{ route('products.index') }}" class="btn btn-primary">Sync Products</a>
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
                                    <span class="badge bg-secondary">{{ $product->store->platform }}</span>
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
