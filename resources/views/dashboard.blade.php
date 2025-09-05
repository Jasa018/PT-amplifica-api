@extends('layouts.app')

@section('content')
<main>
    <h1>Welcome, {{ Auth::user()->name }}!</h1>
    <p class="lead">This is your main dashboard. From here you can view products and orders from your connected stores.</p>
    <hr>
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Manage Products</h5>
                    <p class="card-text">View and manage products from all your stores.</p>
                    <a href="{{ route('products.index') }}" class="btn btn-primary">Go to Products</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Manage Orders</h5>
                    <p class="card-text">View and manage recent orders.</p>
                    <a href="{{ route('orders.index') }}" class="btn btn-primary">Go to Orders</a>
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Manage Stores</h5>
                    <p class="card-text">Add, edit, and manage your connected stores.</p>
                    <a href="{{ route('stores.index') }}" class="btn btn-primary">Go to Stores</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">View Analytics</h5>
                    <p class="card-text">See aggregated metrics and sales performance.</p>
                    <a href="{{ route('analytics.index') }}" class="btn btn-primary">Go to Analytics</a>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection