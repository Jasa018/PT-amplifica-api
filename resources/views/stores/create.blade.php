@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Add New Store</h1>
        <a href="{{ route('stores.index') }}" class="btn btn-secondary">Back to Stores</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('stores.store') }}" method="POST">
                @csrf
                @include('stores._form')
                <button type="submit" class="btn btn-primary">Add Store</button>
            </form>
        </div>
    </div>
</div>
@endsection
