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
</main>
@endsection