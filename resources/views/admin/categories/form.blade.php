@extends('layouts.admin')

@section('title', $category->exists ? 'Edit category' : 'Add category')

@section('content')
<div class="mb-4"><a href="{{ route('admin.categories.index') }}" class="text-decoration-none">← Categories</a><h1 class="h2 fw-bold mt-2">{{ $category->exists ? 'Edit category' : 'Add category' }}</h1></div>
<form action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}" method="POST">
    @csrf
    @if ($category->exists) @method('PUT') @endif
    <div class="card border-0 shadow-sm rounded-4"><div class="card-body p-4">
        <div class="mb-3"><label class="form-label fw-semibold" for="name">Name</label><input id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $category->name) }}" required>@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="mb-3"><label class="form-label fw-semibold" for="slug">Slug <span class="text-muted fw-normal">(optional; generated from name if blank)</span></label><input id="slug" name="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug', $category->slug) }}">@error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div><label class="form-label fw-semibold" for="image">Image path or URL</label><input id="image" name="image" class="form-control @error('image') is-invalid @enderror" value="{{ old('image', $category->image) }}">@error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    </div></div>
    <div class="d-flex gap-2 mt-3"><button class="btn btn-primary" type="submit">Save category</button><a class="btn btn-outline-secondary" href="{{ route('admin.categories.index') }}">Cancel</a></div>
</form>
@endsection
