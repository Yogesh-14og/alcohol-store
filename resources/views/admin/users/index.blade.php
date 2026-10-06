@extends('layouts.admin')

@section('title', 'Customers')

@section('content')
<div class="mb-4"><p class="text-uppercase text-primary fw-semibold small mb-1">Accounts</p><h1 class="h2 fw-bold mb-0">Customers</h1></div>
<div class="card border-0 shadow-sm rounded-4"><div class="card-body p-0"><div class="table-responsive">
    <table class="table align-middle mb-0">
        <thead><tr><th class="ps-4">Customer</th><th>Email</th><th>Joined</th><th>Orders</th><th></th></tr></thead>
        <tbody>
            @forelse ($users as $user)
                <tr>
                    <td class="ps-4 fw-semibold">{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->created_at->format('M j, Y') }}</td>
                    <td>{{ $user->orders_count }}</td>
                    <td class="text-end pe-4"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.users.edit', $user) }}">Edit</a></td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-5">No customer accounts found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div></div></div>
<div class="mt-3">{{ $users->links() }}</div>
@endsection
