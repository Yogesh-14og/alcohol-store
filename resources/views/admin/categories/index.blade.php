@extends('layouts.admin')

@section('title', 'Categories')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div><p class="text-uppercase text-primary fw-semibold small mb-1">Catalog</p><h1 class="h2 fw-bold mb-0">Categories</h1></div>
    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add category</a>
</div>
<div class="card border-0 shadow-sm rounded-4"><div class="card-body p-0"><div class="table-responsive">
    <table class="table align-middle mb-0">
        <thead><tr><th class="ps-4">Category</th><th>Products</th><th></th></tr></thead>
        <tbody>
            @forelse ($categories as $category)
                <tr>
                    <td class="ps-4"><span class="fw-semibold">{{ $category->name }}</span><br><small class="text-muted">{{ $category->slug }}</small></td>
                    <td>{{ $category->products_count }}</td>
                    <td class="text-end pe-4">
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.categories.edit', $category) }}">Edit</a>
                        <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this category? Its products will become uncategorized.')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="3" class="text-center text-muted py-5">No categories yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div></div></div>
<div class="mt-3">{{ $categories->links() }}</div>
@endsection
