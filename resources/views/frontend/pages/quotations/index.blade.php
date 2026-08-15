@extends('frontend.layouts.app')

@section('title', 'Quotations')

@section('content')
    <div class="container-fluid mt-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h3 class="fw-bold mb-0">Quotation Management</h3>
            <div class="d-flex gap-2">
                <a href="{{ route('quotations.export', request()->query()) }}" class="btn btn-outline-success rounded-pill px-3">
                    <i class="fas fa-file-excel me-1"></i> Export Excel
                </a>
                <a href="{{ route('quotations.create') }}" class="btn btn-primary rounded-pill px-4">
                    <i class="fas fa-plus me-2"></i>Create Quotation
                </a>
            </div>
        </div>

        {{-- @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif --}}

        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end mb-4">
                    <div class="col-md-3">
                        <label class="form-label">Quotation No.</label>
                        <input type="text" class="form-control" name="quotation_number"
                            value="{{ request('quotation_number') }}" placeholder="Search quotation no">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Client Name</label>
                        <input type="text" class="form-control" name="client_name" value="{{ request('client_name') }}"
                            placeholder="Search client name">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Date From</label>
                        <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Date To</label>
                        <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="{{ route('quotations.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0" id="quotations-datatable">
                        <thead class="table-light">
                            <tr>
                                <th>Sl.</th>
                                <th>Quotation No.</th>
                                <th>Client</th>
                                <th>Created Date</th>
                                <th>Total Amount</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($quotations as $index => $quotation)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <a href="{{ route('quotations.show', $quotation->id) }}"
                                            class="fw-semibold text-decoration-none">
                                            {{ $quotation->quotation_number }}
                                        </a>
                                    </td>
                                    <td>{{ Str::limit($quotation->client?->name, 30) ?? 'N/A' }}</td>
                                    <td>{{ optional($quotation->quotation_date)->format('M d, Y') }}</td>
                                    <td>{{ number_format($quotation->total_amount, 2) }}</td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light border" type="button"
                                                data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item"
                                                        href="{{ route('quotations.edit', $quotation->id) }}">
                                                        <i class="far fa-edit me-2"></i>Edit
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item"
                                                        href="{{ route('quotations.show', $quotation->id) }}">
                                                        <i class="far fa-eye me-2"></i>View
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item"
                                                        href="{{ route('quotations.pdf.preview', $quotation->id) }}"
                                                        target="_blank">
                                                        <i class="far fa-file-pdf me-2"></i>Preview PDF
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item"
                                                        href="{{ route('quotations.pdf', $quotation->id) }}">
                                                        <i class="fas fa-download me-2"></i>Download PDF
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item text-danger" href="javascript:void(0)"
                                                        onclick="if (confirm('Are you sure to delete?')) { document.getElementById('quotationDelete{{ $quotation->id }}').submit(); }">
                                                        <i class="far fa-trash-alt me-2"></i>Delete
                                                    </a>
                                                    <form id="quotationDelete{{ $quotation->id }}"
                                                        action="{{ route('quotations.destroy', $quotation->id) }}"
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
                                    <td colspan="7" class="text-center py-4">No quotations found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#quotations-datatable').DataTable({
                order: [
                    [0, 'desc']
                ],
                language: {
                    paginate: {
                        previous: "<i class='mdi mdi-chevron-left'>",
                        next: "<i class='mdi mdi-chevron-right'>"
                    }
                },
                columnDefs: [{
                    orderable: false,
                    targets: [5]
                }],
                drawCallback: function() {
                    $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
                }
            });
        });
    </script>
@endpush
