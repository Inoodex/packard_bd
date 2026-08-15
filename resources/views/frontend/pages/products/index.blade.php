@extends('frontend.layouts.app')

@section('content')
    <div class="container-fluid mt-4">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="fw-bold">Product List</h3>
            <div class="d-flex gap-2">
                <a href="{{ route('products.export', request()->query()) }}" class="btn btn-outline-success rounded-pill px-3">
                    <i class="fas fa-file-excel me-1"></i> Export Excel
                </a>
                <a href="{{ route('products.create') }}" class="btn btn-primary rounded-pill px-4">
                    + Add Product
                </a>
            </div>
        </div>

        {{-- @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('warning'))
            <div class="alert alert-warning">{{ session('warning') }}</div>
        @endif --}}
        @if (session('import_errors'))
            <div class="alert alert-warning">
                <div class="fw-semibold mb-1">Import row issues:</div>
                <ul class="mb-0">
                    @foreach (session('import_errors') as $importError)
                        <li>{{ $importError }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Filter Form --}}
        <div class="card mb-3 shadow-sm border-0 rounded-3">
            <div class="card-body">
                <form method="GET" action="{{ route('products.index') }}" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Product Name</label>
                        <input type="text" name="name" value="{{ request('name') }}" class="form-control"
                            placeholder="Search by Name">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Product Code</label>
                        <input type="text" name="product_code" value="{{ request('product_code') }}" class="form-control"
                            placeholder="Search by Product Code">
                    </div>
                    <div class="col-md-4 d-flex justify-content-md-end gap-2">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="{{ route('products.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mb-3 shadow-sm border-0 rounded-3">
            <div class="card-body">
                <form id="product-import-form" method="POST" action="{{ route('products.import') }}"
                    enctype="multipart/form-data" class="row g-3 align-items-end">
                    @csrf
                    <div class="col-md-8">
                        <label class="form-label">Import Products (CSV/XLSX)</label>
                        <div class="d-flex gap-2 align-items-center">
                            <input type="file" name="import_file" class="form-control" accept=".csv,.xlsx" required>
                            <button id="product-import-btn" type="submit" class="btn btn-primary">Import</button>
                        </div>
                        <div class="form-text">Required headers: name, product_code, details, unit, price</div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-body">
                @php
                    $currentSortBy = request('sort_by', 'created_at');
                    $currentSortDir = strtolower((string) request('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';
                    $nextDir = function ($column) use ($currentSortBy, $currentSortDir) {
                        return $currentSortBy === $column && $currentSortDir === 'asc' ? 'desc' : 'asc';
                    };
                    $sortIcon = function ($column) use ($currentSortBy, $currentSortDir) {
                        if ($currentSortBy !== $column) {
                            return 'fa-sort text-muted';
                        }
                        return $currentSortDir === 'asc' ? 'fa-sort-up' : 'fa-sort-down';
                    };
                @endphp

                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>SL.</th>
                            <th>
                                <a class="text-decoration-none text-dark"
                                    href="{{ route('products.index', array_merge(request()->except('page'), ['sort_by' => 'name', 'sort_dir' => $nextDir('name')])) }}">
                                    Name <i class="fas {{ $sortIcon('name') }} ms-1"></i>
                                </a>
                            </th>
                            <th>
                                <a class="text-decoration-none text-dark"
                                    href="{{ route('products.index', array_merge(request()->except('page'), ['sort_by' => 'product_code', 'sort_dir' => $nextDir('product_code')])) }}">
                                    Product Code <i class="fas {{ $sortIcon('product_code') }} ms-1"></i>
                                </a>
                            </th>
                            <th>
                                <a class="text-decoration-none text-dark"
                                    href="{{ route('products.index', array_merge(request()->except('page'), ['sort_by' => 'unit', 'sort_dir' => $nextDir('unit')])) }}">
                                    Unit <i class="fas {{ $sortIcon('unit') }} ms-1"></i>
                                </a>
                            </th>
                            <th>
                                <a class="text-decoration-none text-dark"
                                    href="{{ route('products.index', array_merge(request()->except('page'), ['sort_by' => 'price', 'sort_dir' => $nextDir('price')])) }}">
                                    Price <i class="fas {{ $sortIcon('price') }} ms-1"></i>
                                </a>
                            </th>
                            <th class="text-end" width="90">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $key => $product)
                            <tr>
                                <td>{{ $products->firstItem() + $key }}</td>
                                <td>{{ Str::limit($product->name, 50) }}</td>
                                <td>{{ $product->product_code }}</td>
                                <td>{{ ucfirst($product->unit) }}</td>
                                <td>{{ number_format($product->price, 2) }}</td>
                                <td class="text-end">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light border" type="button" data-bs-toggle="dropdown"
                                            aria-expanded="false">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item"
                                                    href="{{ route('products.edit', $product->id) }}">
                                                    <i class="far fa-edit me-2"></i>Edit</a>
                                            </li>
                                            <li>
                                                <form action="{{ route('products.destroy', $product->id) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button onclick="return confirm('Delete this product?')" type="submit"
                                                        class="dropdown-item text-danger">
                                                        <i class="far fa-trash-alt me-2"></i>Delete</button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No products found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-3">
                    {{ $products->links('pagination::bootstrap-5') }}
                </div>

            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            const form = document.getElementById('product-import-form');
            const btn = document.getElementById('product-import-btn');
            if (!form || !btn) return;

            form.addEventListener('submit', function() {
                if (btn.disabled) return;
                btn.disabled = true;
                btn.textContent = 'Importing...';
            });
        })();
    </script>
@endpush
