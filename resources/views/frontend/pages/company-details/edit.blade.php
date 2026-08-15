@extends('frontend.layouts.app')

@section('content')
    <div class="container-fluid p-5">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Edit Company Details</h4>
                        <a href="{{ route('company.index') }}" class="btn btn-secondary">
                            Back
                        </a>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('company.update', $companyDetail->id) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Company Name *</label>
                                        <input type="text" name="name" class="form-control"
                                            value="{{ old('name', $companyDetail->name) }}" required>
                                        @error('name')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                {{-- <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Signatory Name *</label>
                                        <input type="text" name="signatory_name" class="form-control"
                                            value="{{ old('signatory_name', $companyDetail->signatory_name) }}" required>
                                        @error('signatory_name')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Signatory Designation *</label>
                                        <input type="text" name="signatory_designation" class="form-control"
                                            value="{{ old('signatory_designation', $companyDetail->signatory_designation) }}"
                                            required>
                                        @error('signatory_designation')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div> --}}
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Phone</label>
                                        <input type="text" name="phone" class="form-control"
                                            value="{{ old('phone', $companyDetail->phone) }}">
                                        @error('phone')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Change Photo</label>
                                        <input type="file" name="photo" class="form-control">
                                        @error('photo')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" name="email" class="form-control"
                                            value="{{ old('email', $companyDetail->email) }}">
                                        @error('email')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Website</label>
                                        <input type="text" name="website" class="form-control"
                                            value="{{ old('website', $companyDetail->website) }}">
                                        @error('website')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
                                        <label>Company Address</label>
                                        <textarea name="address" class="form-control" rows="3">{{ old('address', $companyDetail->address) }}</textarea>
                                        @error('address')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="form-check mt-3">
                                            <input type="checkbox" name="is_default" value="1"
                                                {{ old('is_default', $companyDetail->is_default) ? 'checked' : '' }}
                                                class="form-check-input" id="is_default">
                                            <label class="form-check-label" for="is_default">
                                                Set as Default Company
                                            </label>
                                        </div>
                                        {{-- <small class="form-text text-muted">
                                            This company will be used as default for new bills
                                        </small> --}}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="form-check mt-3">
                                            <input type="checkbox" name="is_active" value="1"
                                                {{ old('is_active', $companyDetail->is_active) ? 'checked' : '' }}
                                                class="form-check-input" id="is_active">
                                            <label class="form-check-label" for="is_active">
                                                Active
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mt-4">
                                <button type="submit" class="btn btn-primary">
                                    Update
                                </button>
                                <a href="{{ route('company.index') }}" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
