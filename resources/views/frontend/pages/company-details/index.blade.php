@extends('frontend.layouts.app')

@section('title', 'Company Details')

@section('content')
    <div class="container-fluid mt-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h3 class="fw-bold mb-0">Company Details</h3>
            <a href="{{ route('company.create') }}" class="btn btn-primary rounded-pill px-4">
                <i class="fas fa-plus me-2"></i>Add Company
            </a>
        </div>

        <div class="card shadow-sm border-0 rounded-3 mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('company.index') }}" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Name / Phone / Email"
                            value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive
                            </option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Default</label>
                        <select name="is_default" class="form-select">
                            <option value="">All</option>
                            <option value="yes" {{ request('is_default') === 'yes' ? 'selected' : '' }}>Default</option>
                            <option value="no" {{ request('is_default') === 'no' ? 'selected' : '' }}>Non-default
                            </option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="{{ route('company.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Sl.</th>
                                <th class="text-center">Logo</th>
                                <th>Company Name</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Default</th>
                                <th class="text-end" width="90">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($companies as $company)
                                <tr>
                                    <td>{{ $companies->firstItem() + $loop->index }}</td>
                                    <td class="text-center align-middle">
                                        @if ($company->photo)
                                            <img src="{{ asset($company->photo) }}" alt="Logo" width="40"
                                                height="40" class="rounded border d-block mx-auto">
                                        @else
                                            <span class="text-muted small">No Image</span>
                                        @endif
                                    </td>
                                    <td class="fw-semibold">{{ Str::limit($company->name, 30) }}</td>
                                    <td>{{ $company->phone ?? 'N/A' }}</td>
                                    <td>{{ $company->email ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $company->is_active ? 'success' : 'danger' }}">
                                            {{ $company->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if ($company->is_default)
                                            <span class="badge bg-success">Default</span>
                                        @else
                                            <form action="{{ route('company.set-default', $company->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-primary">Set
                                                    Default</button>
                                            </form>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light border" type="button"
                                                data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item"
                                                        href="{{ route('company.edit', $company->id) }}">
                                                        <i class="far fa-edit me-2"></i>Edit
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item text-danger" href="javascript:void(0)"
                                                        onclick="if (confirm('Are you sure to delete?')) { document.getElementById('companyDelete{{ $company->id }}').submit(); }">
                                                        <i class="far fa-trash-alt me-2"></i>Delete
                                                    </a>
                                                    <form id="companyDelete{{ $company->id }}"
                                                        action="{{ route('company.destroy', $company->id) }}"
                                                        method="POST" class="d-none">
                                                        @csrf
                                                        @method('DELETE')
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">No company records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white">
                {{ $companies->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
@endsection
