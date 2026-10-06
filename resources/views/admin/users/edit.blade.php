@extends('layouts.admin')

@section('title', 'Edit customer')

@section('content')
<div class="mb-4"><a href="{{ route('admin.users.index') }}" class="text-decoration-none">← Customers</a><h1 class="h2 fw-bold mt-2">Edit customer</h1></div>
<form action="{{ route('admin.users.update', $user) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="card border-0 shadow-sm rounded-4"><div class="card-body p-4">
        <div class="mb-3"><label class="form-label fw-semibold" for="name">Name</label><input id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="mb-3"><label class="form-label fw-semibold" for="email">Email</label><input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <p class="text-muted small mb-0">Customer has {{ $user->orders()->count() }} order(s). Accounts are not deleted here to preserve order history.</p>
    </div></div>
    <div class="d-flex gap-2 mt-3"><button class="btn btn-primary" type="submit">Save customer</button><a class="btn btn-outline-secondary" href="{{ route('admin.users.index') }}">Cancel</a></div>
</form>
@endsection
