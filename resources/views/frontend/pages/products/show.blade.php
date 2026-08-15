@extends('frontend.layouts.app')

@section('content')
    <div class="container-fluid mt-4">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="fw-bold">Product Details</h3>

            <div>
                <a href="{{ route('products.edit', $product->id) }}" class="btn btn-warning rounded-pill px-4">
                    Edit
                </a>

                <a href="{{ route('products.index') }}" class="btn btn-secondary rounded-pill px-4">
                    Back
                </a>
            </div>
        </div>
        {{-- @if (session('warning'))
            <div class="alert alert-warning">{{ session('warning') }}</div>
        @endif --}}

        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-body p-4">

                <div class="row mb-3">
                    <div class="col-6 mb-3">
                        <label class="form-label fw-semibold">Name</label>
                        <div class="form-control bg-light">
                            {{ $product->name }}
                        </div>
                    </div>

                    <div class="col-6 mb-3">
                        <label class="form-label fw-semibold">Product Code</label>
                        <div class="form-control bg-light">
                            {{ $product->product_code }}
                        </div>
                    </div>

                    <div class="col-6 mb-3">
                        <label class="form-label fw-semibold">Unit</label>
                        <div class="form-control bg-light">
                            {{ ucfirst($product->unit) }}
                        </div>
                    </div>

                    <div class="col-6 mb-3">
                        <label class="form-label fw-semibold">Price</label>
                        <div class="form-control bg-light">
                            {{ number_format($product->price, 2) }}
                        </div>
                    </div>

                    <div class="col-12 mb-3">
                        <label class="form-label fw-semibold">Details</label>
                        <div class="form-control bg-light" style="min-height: 80px;">
                            {{ $product->details ?? 'N/A' }}
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
@endsection
