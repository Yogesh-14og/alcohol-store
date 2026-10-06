@extends('layouts.admin')

@section('title', 'Record purchase')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.purchase-ledger.index') }}" class="text-decoration-none">← Kharid Khata</a>
    <h1 class="h2 fw-bold mt-2">Record purchase</h1>
    <p class="text-muted mb-0">Each entry records one product line. An invoice number is generated automatically when you save.</p>
</div>
@if ($suppliers->isEmpty())
    <div class="alert alert-warning">Add a supplier from the Suppliers page before recording a purchase.</div>
    <a class="btn btn-primary" href="{{ route('admin.suppliers.index') }}">Open Suppliers</a>
@elseif ($products->isEmpty())
    <div class="alert alert-warning">Add a product before recording a purchase.</div>
@else
    <form action="{{ route('admin.purchase-ledger.purchases.store') }}" method="POST" class="card border-0 shadow-sm rounded-4">
        @csrf
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="supplier_id" class="form-label fw-semibold">Supplier</label>
                    <select id="supplier_id" name="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror" required>
                        <option value="">Choose supplier</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @selected((string) old('supplier_id', $selectedSupplierId) === (string) $supplier->id)>{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                    @error('supplier_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="purchased_at" class="form-label fw-semibold">Purchase date</label>
                    <input id="purchased_at" name="purchased_at" type="date" class="form-control @error('purchased_at') is-invalid @enderror" value="{{ old('purchased_at', today()->toDateString()) }}" required>
                    @error('purchased_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="product_id" class="form-label fw-semibold">Product</label>
                    <select id="product_id" name="items[0][product_id]" class="form-select @error('items.0.product_id') is-invalid @enderror" required>
                        <option value="">Choose product</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" data-cost="{{ $product->cost_price }}" @selected((string) old('items.0.product_id') === (string) $product->id)>{{ $product->name }}</option>
                        @endforeach
                    </select>
                    @error('items.0.product_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="quantity" class="form-label fw-semibold">Quantity received</label>
                    <input id="quantity" name="items[0][quantity]" type="number" min="1" step="1" class="form-control @error('items.0.quantity') is-invalid @enderror" value="{{ old('items.0.quantity', 1) }}" required>
                    @error('items.0.quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="unit_cost" class="form-label fw-semibold">Unit purchase cost (NPR)</label>
                    <input id="unit_cost" name="items[0][unit_cost]" type="number" min="0" step="0.01" class="form-control @error('items.0.unit_cost') is-invalid @enderror" value="{{ old('items.0.unit_cost') }}" required>
                    @error('items.0.unit_cost')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label for="notes" class="form-label fw-semibold">Notes</label>
                    <textarea id="notes" name="notes" class="form-control @error('notes') is-invalid @enderror" rows="2">{{ old('notes') }}</textarea>
                    @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <button class="btn btn-primary mt-4" type="submit">Save purchase and add stock</button>
        </div>
    </form>
    <script>
        document.getElementById('product_id').addEventListener('change', (event) => {
            const selectedOption = event.currentTarget.selectedOptions[0];
            const costInput = document.getElementById('unit_cost');

            if (selectedOption.dataset.cost && !costInput.value) {
                costInput.value = selectedOption.dataset.cost;
            }
        });
    </script>
@endif
@endsection
