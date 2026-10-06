@extends('layouts.admin')

@section('title', 'Products')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div><p class="text-uppercase text-primary fw-semibold small mb-1">Catalog</p><h1 class="h2 fw-bold mb-0">Products</h1></div>
    <a href="{{ route('admin.products.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add product</a>
</div>
<form action="{{ route('admin.products.index') }}" method="GET" class="card border-0 shadow-sm rounded-4 mb-4" role="search" data-live-search>
    <div class="card-body">
        <label for="productSearch" class="form-label fw-semibold">Search products</label>
        <input id="productSearch" name="search" type="search" class="form-control mb-3" value="{{ $search }}" placeholder="Name, slug, category, or volume" maxlength="100" autocomplete="off">
        <div class="d-flex gap-2">
            <button class="btn btn-primary" type="submit"><i class="bi bi-search me-1" aria-hidden="true"></i>Search</button>
            @if ($search !== '')
                <a class="btn btn-outline-secondary" href="{{ route('admin.products.index') }}">Clear</a>
            @endif
        </div>
    </div>
</form>
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th class="ps-4">Product</th><th>Category</th><th>Price</th><th>Cost price</th><th>Margin</th><th>Stock</th><th>Tags</th><th></th></tr></thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td class="ps-4"><span class="fw-semibold">{{ $product->name }}</span><br><small class="text-muted">{{ $product->slug }}</small></td>
                            <td>{{ $product->category?->name ?? 'Uncategorized' }}</td>
                            <td>NPR {{ number_format((float) $product->price, 2) }}</td>
                            <td>{{ $product->cost_price !== null ? 'NPR '.number_format((float) $product->cost_price, 2) : 'Not set' }}</td>
                            <td>
                                @if ($product->gross_margin_percentage !== null)
                                    <span class="fw-semibold">{{ number_format($product->gross_margin_percentage, 1) }}%</span>
                                    <small class="d-block text-muted">NPR {{ number_format($product->gross_profit_per_unit_cents / 100, 2) }} / unit</small>
                                @else
                                    <span class="text-muted">Not available</span>
                                @endif
                            </td>
                            <td>{{ $product->stock_quantity }} on hand<br><small class="text-muted">{{ $product->available_stock }} available</small></td>
                            <td>
                                @if ($product->is_best_seller)<span class="badge text-bg-warning">Best seller</span>@endif
                                @if ($product->is_new_arrival)<span class="badge text-bg-info">New</span>@endif
                                @if ($product->is_hot_deal)<span class="badge text-bg-danger">Deal</span>@endif
                            </td>
                            <td class="text-end pe-4">
                                <a class="btn btn-sm btn-outline-success" href="{{ route('admin.products.stock-adjustments.index', $product) }}">Adjust stock</a>
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.products.edit', $product) }}">Edit</a>
                                <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this product? It will be removed from carts.')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-5">{{ $search !== '' ? 'No products match your search.' : 'No products yet.' }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="mt-3">{{ $products->links() }}</div>
@endsection
