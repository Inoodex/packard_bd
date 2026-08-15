@extends('frontend.layouts.app')

@section('content')
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="mb-0">Quotation Details</h4>
                            <div>
                                {{-- <a href="{{ route('quotations.pdf.preview', $quotation->id) }}" target="_blank"
                                    class="btn btn-info me-2">
                                    <i class="mdi mdi-eye me-1"></i> Preview PDF
                                </a> --}}
                                <a href="{{ route('quotations.pdf', $quotation->id) }}" class="btn btn-success me-2">
                                    <i class="mdi mdi-file-pdf me-1"></i> Generate PDF
                                </a>
                                <a href="{{ route('quotations.index') }}" class="btn btn-secondary">
                                    <i class="mdi mdi-arrow-left me-1"></i> Back to List
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body" id="quotation-content">
                        @php
                            $clientName = $quotation->client?->name ?? 'N/A';
                            $clientAddress = $quotation->client?->address ?? 'N/A';
                            $client_email = $quotation->client?->email ?? 'N/A';
                            // $attention_to = $quotation->attention_to ? 'Attention: ' . $quotation->attention_to : null;
                            $clientDesignation = 'Client';
                        @endphp
                        <!-- Quotation Header -->
                        <div class="row mb-4">
                            <div class="col-6">
                                {{-- <h2 class="text-primary">QUOTATION</h2> --}}
                                <p class="mb-1"><strong>Date:</strong>
                                    {{ \Carbon\Carbon::parse($quotation->created_at)->format('F d, Y') }}</p>
                                <p class="mb-1"><strong>Quotation:</strong>
                                    {{ $quotation->quotation_number }}</p>
                            </div>
                            <div class="col-6 text-end">
                                <h4 class="text-primary">{{ $quotation->company_name }}</h4>
                                <p class="mb-1">{{ $quotation->company_phone }}</p>
                                <p class="mb-1">{{ $quotation->company_email }}</p>
                                <p class="mb-0">{{ $quotation->company_website }}</p>
                            </div>
                        </div>

                        <hr>

                        <!-- Client Information -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <p>To,</p>
                                <p>{{ $attention_to }}</p>
                                {{-- <p>{{ $clientDesignation }}</p> --}}
                                <p>{{ $clientName }}</p>
                                <p>{{ $client_email }}</p>
                                <p>{{ $clientAddress }}</p>

                            </div>
                        </div>

                        <!-- Subject -->
                        {{-- <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-primary">Subject: {{ $quotation->subject }}</h5>
                            </div>
                        </div> --}}

                        <!-- Body Content -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div style="white-space: pre-line;">{{ $quotation->body_content }}</div>
                            </div>
                        </div>

                        <!-- Products Table -->
                        @if ($quotation->items && count($quotation->items) > 0)
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="text-primary mb-3">Quotation Details</h5>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead class="table-light">
                                                <tr>
                                                    <th width="5%" class="text-center">S/L</th>
                                                    <th width="45%">Product Description</th>
                                                    <th width="10%" class="text-center">QTY</th>
                                                    <th width="20%" class="text-end">Unit Price</th>
                                                    <th width="20%" class="text-end">Total Price</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($quotation->items as $index => $item)
                                                    <tr>
                                                        <td class="text-center">{{ $index + 1 }}.</td>
                                                        <td>
                                                            <strong>{{ Str::limit($item->product->name, 50) ?? 'N/A' }}</strong>
                                                            @if ($item->product->brand->name ?? false)
                                                                <br><small class="text-muted">Brand:
                                                                    {{ Str::limit($item->product->brand->name, 50) }}</small>
                                                            @endif
                                                            @if ($item->product->model ?? false)
                                                                <br><small class="text-muted">Model:
                                                                    {{ Str::limit($item->product->model, 50) }}</small>
                                                            @endif
                                                            @if ($item->description)
                                                                <br><small
                                                                    class="text-muted">{{ Str::limit($item->description, 50) }}</small>
                                                            @endif
                                                        </td>
                                                        <td class="text-center">{{ $item->quantity }}</td>
                                                        <td class="text-end">{{ number_format($item->unit_price, 2) }}
                                                        </td>
                                                        <td class="text-end">
                                                            {{ number_format($item->quantity * $item->unit_price, 2) }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot class="table-light">
                                                <tr>
                                                    <td colspan="3" class="text-end"><strong>Sub Total:</strong></td>
                                                    <td colspan="2" class="text-end"><strong>
                                                            {{ number_format($quotation->sub_total, 2) }}</strong></td>
                                                </tr>
                                                @if ($quotation->discount_amount > 0)
                                                    <tr>
                                                        <td colspan="3" class="text-end"><strong>Discount:</strong></td>
                                                        <td colspan="2" class="text-end"><strong>-
                                                                {{ number_format($quotation->discount_amount, 2) }}</strong>
                                                        </td>
                                                    </tr>
                                                @endif
                                                <tr>
                                                    <td colspan="3" class="text-end"><strong>VAT (%):</strong></td>
                                                    <td colspan="2" class="text-end">
                                                        <strong>{{ number_format((float) ($quotation->vat_percent ?? 0), 2) }}%</strong>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="3" class="text-end"><strong>VAT Amount:</strong></td>
                                                    <td colspan="2" class="text-end">
                                                        <strong>
                                                            {{ number_format((float) ($quotation->vat_amount ?? 0), 2) }}</strong>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="3" class="text-end"><strong>Installation
                                                            Charge:</strong></td>
                                                    <td colspan="2" class="text-end">
                                                        <strong>
                                                            {{ number_format((float) ($quotation->installation_charge ?? 0), 2) }}</strong>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="3" class="text-end"><strong>Round Off (-):</strong></td>
                                                    <td colspan="2" class="text-end">
                                                        <strong>-
                                                            {{ number_format((float) ($quotation->round_off ?? 0), 2) }}</strong>
                                                    </td>
                                                </tr>
                                                <tr class="table-primary">
                                                    <td colspan="3" class="text-end"><strong>Total Amount:</strong></td>
                                                    <td colspan="2" class="text-end"><strong>
                                                            {{ number_format($quotation->total_amount, 2) }}</strong></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- <!-- Additional Enclosed -->
                        @if ($quotation->additional_enclosed)
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="text-primary">Enclosed Documents:</h5>
                                    <div style="white-space: pre-line;">{{ $quotation->additional_enclosed }}</div>
                                </div>
                            </div>
                        @endif

                        <!-- Terms and Conditions -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-primary">Terms and Conditions:</h5>
                                <div style="white-space: pre-line;">{{ $quotation->terms_conditions }}</div>
                            </div>
                        </div> --}}

                        {{-- <!-- Signatory Section -->
                        <div class="row mt-5">
                            <div class="col-6">
                                <p><strong>Prepared By:</strong><br>
                                    {{ $quotation->signatory_name ?? '-' }}</p>
                            </div>
                            <div class="col-6 text-end">
                                <div class="border-top pt-3 mt-4" style="width: 200px; margin-left: auto;">
                                    <p class="text-center mb-0"><strong>Authorized Signature</strong></p>
                                    <p class="text-center mb-0">{{ $quotation->company_name }}</p>
                                </div>
                            </div>
                        </div> --}}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        #quotation-content {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        .text-primary {
            color: #2c5aa0 !important;
        }
    </style>
@endsection
