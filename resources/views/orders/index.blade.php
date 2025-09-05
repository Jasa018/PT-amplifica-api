@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Orders</h1>
        <div>
            <a href="{{ route('orders.sync') }}" class="btn btn-primary me-2">Sync Orders</a>
            <a href="{{ route('orders.export') }}" class="btn btn-success">Export to CSV</a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">Filter Orders</div>
        <div class="card-body">
            <form method="GET" action="{{ route('orders.index') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="{{ request('start_date') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="{{ request('end_date') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="customer_name" class="form-label">Customer Name</label>
                        <input type="text" class="form-control" id="customer_name" name="customer_name" value="{{ request('customer_name') }}" placeholder="Customer Name">
                    </div>
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Statuses</option>
                            @foreach($statuses as $s)
                                <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="store_name" class="form-label">Store Name</label>
                        <input type="text" class="form-control" id="store_name" name="store_name" value="{{ request('store_name') }}" placeholder="Store Name">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                        <a href="{{ route('orders.index') }}" class="btn btn-secondary">Clear Filters</a>
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
                                    <span class="badge bg-secondary">{{ $order->store->name }}</span>
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