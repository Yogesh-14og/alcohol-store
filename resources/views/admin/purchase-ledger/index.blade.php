@extends('layouts.admin')

@section('title', 'Kharid Khata')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <p class="text-uppercase text-primary fw-semibold small mb-1">Purchase ledger</p>
        <h1 class="h2 fw-bold mb-0">Kharid Khata</h1>
        <p class="text-muted mb-0">Purchases increase supplier balance; recorded payments reduce it.</p>
    </div>
    <a class="btn btn-primary" href="{{ route('admin.purchase-ledger.purchases.create') }}">Record purchase</a>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th class="ps-4">Supplier</th><th>Phone</th><th>Purchases</th><th>Outstanding</th><th></th></tr></thead>
            <tbody>
                @forelse ($suppliers as $supplier)
                    @php($balanceCents = $supplier->purchases->sum(fn ($purchase) => $purchase->balance_due_cents))
                    <tr>
                        <td class="ps-4 fw-semibold">{{ $supplier->name }}</td>
                        <td>{{ $supplier->phone ?: '—' }}</td>
                        <td>{{ $supplier->purchases->count() }}</td>
                        <td>NPR {{ number_format($balanceCents / 100, 2) }}</td>
                        <td class="text-end pe-4"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.purchase-ledger.suppliers.show', $supplier) }}">View khata</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-5">No suppliers yet. Add a supplier to begin recording purchases.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
