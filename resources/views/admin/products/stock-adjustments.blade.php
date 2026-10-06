@extends('layouts.admin')

@section('title', 'Adjust stock · '.$product->name)

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.products.index') }}" class="text-decoration-none">← Products</a>
    <p class="text-uppercase text-primary fw-semibold small mt-3 mb-1">Inventory</p>
    <h1 class="h2 fw-bold mb-1">Adjust stock</h1>
    <p class="text-muted mb-0">{{ $product->name }} · {{ $product->stock_quantity }} on hand · {{ $product->available_stock }} available</p>
</div>

<div class="alert alert-info" role="note">
    Use this for restocking or correcting a count. It changes inventory only and does not create a supplier purchase or supplier balance.
</div>

<form action="{{ route('admin.products.stock-adjustments.store', $product) }}" method="POST" class="card border-0 shadow-sm rounded-4 mb-4">
    @csrf
    <div class="card-body p-4">
        <h2 class="h5 fw-bold mb-3">Record stock change</h2>
        <div class="row g-3">
            <div class="col-md-4">
                <label for="direction" class="form-label fw-semibold">Adjustment</label>
                <select id="direction" name="direction" class="form-select @error('direction') is-invalid @enderror" required>
                    <option value="add" @selected(old('direction', 'add') === 'add')>Add stock</option>
                    <option value="remove" @selected(old('direction') === 'remove')>Remove stock</option>
                </select>
                @error('direction')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label for="quantity" class="form-label fw-semibold">Quantity</label>
                <input id="quantity" name="quantity" type="number" min="1" max="1000000" step="1" class="form-control @error('quantity') is-invalid @enderror" value="{{ old('quantity') }}" required>
                @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label for="reason" class="form-label fw-semibold">Reason</label>
                <select id="reason" name="reason" class="form-select @error('reason') is-invalid @enderror" required>
                    @foreach ([
                        'restock' => 'Restock',
                        'stock_count' => 'Stock count correction',
                        'damaged' => 'Damaged',
                        'expired' => 'Expired',
                        'other' => 'Other',
                    ] as $value => $label)
                        <option value="{{ $value }}" @selected(old('reason') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label for="notes" class="form-label fw-semibold">Notes <span class="text-muted fw-normal">(optional)</span></label>
                <textarea id="notes" name="notes" rows="2" maxlength="1000" class="form-control @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>
                @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <button class="btn btn-primary mt-4" type="submit">Save stock adjustment</button>
    </div>
</form>

<section class="card border-0 shadow-sm rounded-4">
    <div class="card-body border-bottom">
        <h2 class="h5 fw-bold mb-0">Stock adjustment history</h2>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th class="ps-4">Date</th>
                    <th>Change</th>
                    <th>Before</th>
                    <th>After</th>
                    <th>Reason</th>
                    <th>Admin</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($adjustments as $adjustment)
                    <tr>
                        <td class="ps-4">{{ $adjustment->created_at->format('M j, Y g:i A') }}</td>
                        <td class="fw-semibold {{ $adjustment->direction === 'add' ? 'text-success' : 'text-danger' }}">
                            {{ $adjustment->direction === 'add' ? '+' : '−' }}{{ $adjustment->quantity }}
                        </td>
                        <td>{{ $adjustment->stock_before }}</td>
                        <td>{{ $adjustment->stock_after }}</td>
                        <td class="text-capitalize">{{ str_replace('_', ' ', $adjustment->reason) }}</td>
                        <td>{{ $adjustment->admin?->name ?? 'Admin account removed' }}</td>
                        <td>{{ $adjustment->notes ?: '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-5">No manual stock adjustments yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
<div class="mt-3">{{ $adjustments->links() }}</div>
@endsection
