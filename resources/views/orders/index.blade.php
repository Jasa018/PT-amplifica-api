@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Orders</h1>
        <div>
            <a href="{{ route('orders.index') }}" class="btn btn-primary me-2">Sync Orders</a>
            <a href="{{ route('orders.export') }}" class="btn btn-success">Export to CSV</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">Order #</th>
                            <th scope="col">Customer</th>
                            <th scope="col">Store</th>
                            <th scope="col">Date</th>
                            <th scope="col">Status</th>
                            <th scope="col">Items</th>
                            <th scope="col">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                            <tr>
                                <td>#{{ $order->platform_order_id }}</td>
                                <td>{{ $order->customer_name }}</td>
                                <td>
                                    <span class="badge bg-secondary">{{ $order->store->platform }}</span>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($order->order_date)->format('M d, Y') }}</td>
                                <td><span class="badge bg-info">{{ $order->status }}</span></td>
                                <td>
                                    <ul class="list-unstyled mb-0">
                                        @foreach($order->items as $item)
                                            <li>{{ $item->quantity }} x {{ $item->product_name }}</li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td>${{ number_format($order->total_amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No orders found. Try syncing.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {{ $orders->links() }}
        </div>
    </div>
</div>
@endsection