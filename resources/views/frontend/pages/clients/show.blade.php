@extends('frontend.layouts.app')

@section('title', 'Client Details')

@section('content')
    <style>
        .client-show-card .card-body {
            padding: 1.5rem;
        }

        .client-info-item {
            padding: 0.75rem 0.9rem;
            border: 1px solid #edf0f3;
            border-radius: 0.6rem;
            background: #fff;
            height: 100%;
        }

        .client-info-label {
            display: block;
            font-size: 0.8rem;
            color: #6c757d;
            margin-bottom: 0.35rem;
        }

        .client-info-value {
            font-weight: 500;
            color: #212529;
            word-break: break-word;
        }
    </style>

    <div class="container-fluid mt-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h3 class="fw-bold mb-0">Client Details</h3>
            <div class="d-flex gap-2">
                <a href="{{ route('clients.index') }}" class="btn btn-secondary rounded-pill px-4">Back</a>
            </div>
        </div>

        <div class="row g-3 justify-content-center">
            <div class="col-lg-12">
                <div class="card shadow-sm border-0 rounded-3 h-100 client-show-card">
                    <div class="card-body">
                        <h5 class="mb-3 fw-semibold">Basic Information</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="client-info-item">
                                    <span class="client-info-label">Name</span>
                                    <div class="client-info-value">{{ $client->name }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="client-info-item">
                                    <span class="client-info-label">Phone</span>
                                    <div class="client-info-value">{{ $client->phone ?: '-' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="client-info-item">
                                    <span class="client-info-label">Email</span>
                                    <div class="client-info-value">{{ $client->email ?: '-' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="client-info-item">
                                    <span class="client-info-label">Created</span>
                                    <div class="client-info-value">{{ $client->created_at?->format('d M Y') ?: '-' }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="client-info-item">
                                    <span class="client-info-label">Address</span>
                                    <div class="client-info-value">{{ $client->address ?: '-' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
