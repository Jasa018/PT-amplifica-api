@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Analytics Dashboard</h1>

    <div class="row mb-4">
        {{-- Total Sales --}}
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-header">Total Sales (Last 30 Days)</div>
                <div class="card-body">
                    <h3 class="card-title">${{ number_format($totalSales, 2) }}</h3>
                </div>
            </div>
        </div>

        {{-- Total Orders --}}
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-header">Total Orders (Last 30 Days)</div>
                <div class="card-body">
                    <h3 class="card-title">{{ number_format($totalOrders) }}</h3>
                </div>
            </div>
        </div>

        {{-- Average Order Value --}}
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-header">Average Order Value (Last 30 Days)</div>
                <div class="card-body">
                    <h3 class="card-title">${{ number_format($averageOrderValue, 2) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">Top 5 Best-Selling Products (All Time)</div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        @forelse ($topProducts as $product)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                {{ $product->product_name }}
                                <span class="badge bg-primary rounded-pill">{{ $product->total_quantity }} sold</span>
                            </li>
                        @empty
                            <li class="list-group-item text-center">No sales data available yet.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
