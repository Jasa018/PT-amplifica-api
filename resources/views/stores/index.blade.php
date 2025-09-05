@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>My Stores</h1>
        <a href="{{ route('stores.create') }}" class="btn btn-primary">Add New Store</a>
    </div>

    @if (session('success'))
        <div class="alert alert-success" role="alert">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Platform</th>
                            <th>Store URL</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($stores as $store)
                            <tr>
                                <td>{{ $store->name }}</td>
                                <td><span class="badge bg-info">{{ ucfirst($store->platform) }}</span></td>
                                <td>{{ $store->store_url }}</td>
                                <td>
                                    <a href="{{ route('stores.edit', $store) }}" class="btn btn-sm btn-warning me-2">Edit</a>
                                    <form action="{{ route('stores.destroy', $store) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this store?');">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">No stores added yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
