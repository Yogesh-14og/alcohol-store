@extends('layouts.admin')

@section('title', 'Add supplier')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.suppliers.index') }}" class="text-decoration-none">← Suppliers</a>
    <h1 class="h2 fw-bold mt-2">Add supplier</h1>
</div>
<form action="{{ route('admin.suppliers.store') }}" method="POST" class="card border-0 shadow-sm rounded-4">
    @csrf
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-md-6">
                <label for="name" class="form-label fw-semibold">Supplier name</label>
                <input id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label for="phone" class="form-label fw-semibold">Phone</label>
                <input id="phone" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}">
                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label for="email" class="form-label fw-semibold">Email</label>
                <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}">
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label for="address" class="form-label fw-semibold">Address</label>
                <textarea id="address" name="address" class="form-control @error('address') is-invalid @enderror" rows="2">{{ old('address') }}</textarea>
                @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <button class="btn btn-primary mt-4" type="submit">Save supplier</button>
    </div>
</form>
@endsection
