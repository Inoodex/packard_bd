@extends('frontend.layouts.app')

@section('content')
    <div class="container-fluid mt-4">

        <h3 class="mb-4 fw-bold">Add New Product</h3>
        {{-- @if (session('warning'))
            <div class="alert alert-warning">{{ session('warning') }}</div>
        @endif --}}

        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-body p-4">

                <form method="POST" action="{{ route('products.store') }}">
                    @csrf

                    <div class="row mb-3">

                        <div class="col-6 mb-3">
                            <label class="form-label">Name *</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                        </div>

                        <div class="col-6 mb-3">
                            <label class="form-label">Product Code *</label>
                            <input type="text" name="product_code" class="form-control" value="{{ old('product_code') }}"
                                required>
                        </div>

                        <div class="col-6 mb-3">
                            <label class="form-label">Unit *</label>
                            <select name="unit" class="form-select" required>
                                <option value="">Select Unit</option>
                                <option value="piece">Piece</option>
                                <option value="packet">Packet</option>
                                <option value="kg">KG</option>
                                <option value="liter">Liter</option>
                            </select>
                        </div>

                        <div class="col-6 mb-3">
                            <label class="form-label">Price *</label>
                            <input type="number" step="0.01" min="0" name="price" class="form-control"
                                value="{{ old('price') }}" required>
                        </div>

                        <div class="col-12 mb-3">
                            <label class="form-label">Details</label>
                            <textarea name="details" class="form-control" rows="3">{{ old('details') }}</textarea>
                        </div>

                    </div>

                    <div class="d-flex mt-4">
                        <button type="submit" class="btn btn-primary px-5 py-2 rounded-pill fw-semibold">
                            Save
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
@endsection
