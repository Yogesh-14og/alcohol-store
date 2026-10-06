@extends('layouts.admin')

@section('title', 'Suppliers')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <p class="text-uppercase text-primary fw-semibold small mb-1">Business contacts</p>
        <h1 class="h2 fw-bold mb-0">Suppliers</h1>
        <p class="text-muted mb-0">Manage supplier details and open their purchase ledger.</p>
    </div>
    <a class="btn btn-primary" href="{{ route('admin.suppliers.create') }}">
        <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Add supplier
    </a>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th class="ps-4">Supplier</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Purchases</th>
                    <th>Outstanding</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($suppliers as $supplier)
                    @php($balanceCents = $supplier->purchases->sum(fn ($purchase) => $purchase->balance_due_cents))
                    <tr>
                        <td class="ps-4 fw-semibold">{{ $supplier->name }}</td>
                        <td>{{ $supplier->phone ?: '—' }}</td>
                        <td>{{ $supplier->email ?: '—' }}</td>
                        <td>{{ $supplier->purchases->count() }}</td>
                        <td>NPR {{ number_format($balanceCents / 100, 2) }}</td>
                        <td class="text-end pe-4">
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.purchase-ledger.suppliers.show', $supplier) }}">View khata</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">
                            No suppliers yet. Add a supplier to get started.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
