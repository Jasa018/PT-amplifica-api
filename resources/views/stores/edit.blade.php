@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Edit Store: {{ $store->name }}</h1>
        <a href="{{ route('stores.index') }}" class="btn btn-secondary">Back to Stores</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('stores.update', $store) }}" method="POST">
                @csrf
                @method('PUT')
                @include('stores._form', ['store' => $store])
                <button type="submit" class="btn btn-primary">Update Store</button>
            </form>
        </div>
    </div>
</div>
@endsection
