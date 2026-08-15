@extends('frontend.layouts.app')

@section('title', 'Clients')

@section('content')
    <style>
        .clients-index-card .table th,
        .clients-index-card .table td {
            padding: 0.9rem 1rem;
            vertical-align: middle;
        }

        .clients-index-card .card-body {
            padding: 0.75rem;
        }

        .clients-index-card .table-responsive {
            padding: 0.5rem;
        }
    </style>

    <div class="container-fluid mt-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h3 class="fw-bold mb-0">Clients</h3>
            <div class="d-flex gap-2">
                <a href="{{ route('clients.export', request()->query()) }}" class="btn btn-outline-success rounded-pill px-3">
                    <i class="fas fa-file-excel me-1"></i> Export Excel
                </a>
                <a href="{{ route('clients.create') }}" class="btn btn-primary rounded-pill px-4">Add Client</a>
            </div>
        </div>

        <div class="card shadow-sm border-0 rounded-3 mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('clients.index') }}" class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Name / Phone / Email"
                            value="{{ request('search') }}">
                    </div>
                    {{-- <div class="col-md-3">
                        <label class="form-label">Date From</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date To</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div> --}}
                    <div class="d-flex col-md-2 d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="{{ route('clients.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0 rounded-3 clients-index-card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 70px;">#</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Address</th>
                                <th class="text-end" style="width: 90px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($clients as $client)
                                <tr>
                                    <td>{{ $clients->firstItem() + $loop->index }}</td>
                                    <td class="fw-semibold">{{ Str::limit($client->name, 30) }}</td>
                                    <td>{{ $client->phone }}</td>
                                    <td>{{ $client->email }}</td>
                                    <td>{{ Str::limit($client->address, 30) }}</td>
                                    <td class="text-end">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light border" type="button"
                                                data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item"
                                                        href="{{ route('clients.show', $client->id) }}">
                                                        <i class="fas fa-eye me-2"></i>View</a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item"
                                                        href="{{ route('clients.edit', $client->id) }}"><i
                                                            class="fas fa-edit me-2"></i>Edit</a>
                                                </li>
                                                <li>
                                                    <form action="{{ route('clients.destroy', $client->id) }}"
                                                        method="POST" onsubmit="return confirm('Delete this client?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger">
                                                            <i class="fas fa-trash me-2"></i>Delete</button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No clients found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white">
                {{ $clients->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
@endsection
