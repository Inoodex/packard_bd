@extends('frontend.layouts.app')

@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <div class="content-page-header">
                <h5>Edit User</h5>
            </div>
        </div>
        @if ($errors->any())
            <div class="alert alert-danger" id="validation-error-alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>

            <script>
                // Set a timeout to hide the alert after 2000 milliseconds (2 seconds)
                setTimeout(function() {
                    document.getElementById('validation-error-alert').style.display = 'none';
                }, 3000);
            </script>
        @endif
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header align-items-center d-flex">
                        <h4 class="card-title mb-0 flex-grow-1"></h4>
                        <div class="flex-shrink-0">
                            <div class="form-check form-switch form-switch-right form-switch-md">
                                <a href="{{ route('users.index') }}" class="btn btn-info">User List</a>
                            </div>
                        </div>
                    </div>
                    <!-- end card header -->
                    <div class="card-body">
                        <div class="live-preview">
                            <div class="row gy-4">
                                <form action="{{ route('users.update', $user->id) }}" method="post"
                                    enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')
                                    <div class="row">
                                        <!-- <div class="col-xxl-3 col-md-6 mb-3">
                                                    <label for="name" class="form-label">Type</label>
                                                    <select class="form-select mb-3" name="role_id">
                                                        @foreach (userTypes() as $key => $value)
    <option value="{{ $key }}">{{ $value }}</option>
    @endforeach
                                                    </select>
                                                </div> -->
                                        <div class="col-xxl-3 col-md-6 mb-3">
                                            <label for="name" class="form-label">Name</label>
                                            <input type="text" class="form-control"
                                                value="{{ old('name', $user->name) }}" id="name" name="name"
                                                placeholder="Enter User name">
                                        </div>
                                        <div class="col-xxl-3 col-md-6 mb-3">
                                            <label for="designation" class="form-label">Designation</label>
                                            <input type="text" class="form-control"
                                                value="{{ old('designation', $user->designation) }}" id="designation" name="designation"
                                                placeholder="e.g. Sales Manager">
                                        </div>
                                        <div class="col-xxl-3 col-md-6 mb-3">
                                            <label for="name" class="form-label">Email</label>
                                            <input type="text" class="form-control"
                                                value="{{ old('email', $user->email) }}" id="email" name="email"
                                                placeholder="Enter User email">
                                        </div>
                                        <div class="col-xxl-3 col-md-6 mb-3">
                                            <label for="name" class="form-label">Phone</label>
                                            <input type="text" class="form-control"
                                                value="{{ old('phone', $user->phone) }}" id="phone" name="phone"
                                                placeholder="Enter User phone">
                                        </div>
                                        <div class="col-xxl-3 col-md-6 mb-3">
                                            <label for="image" class="form-label">Signature Image</label>
                                            <input type="file" multiple class="form-control" id="image"
                                                name="images">
                                        </div>
                                        <div class="col-xxl-3 col-md-6 mb-3">
                                            <label for="status" class="form-label">Status</label>
                                            <select class="form-select mb-3" name="status">
                                                <option {{ $user->status == '1' ? 'selected' : '' }} value="1">Actve
                                                </option>
                                                <option {{ $user->status == '0' ? 'selected' : '' }} value="0">InActve
                                                </option>
                                            </select>
                                        </div>
                                        <div class="col-xxl-3 col-md-6 mb-3">
                                            <label for="name" class="form-label">Role</label>
                                            <select class="form-select mb-3" name="user_role" required>
                                                @foreach ($roles as $role)
                                                    <option {{ $user->role_id == $role->id ? 'selected' : '' }}
                                                        value="{{ $role->id }}">{{ $role->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary float-end">Submit</button>
                                </form>

                            </div>
                            <!--end row-->
                        </div>
                    </div>
                </div>
            </div>
            <!--end col-->
        </div>

    </div>
    <!-- container-fluid -->

@section('script')
    <script>
        ClassicEditor
            .create(document.querySelector('#editor'))
            .catch(error => {
                console.error(error);
            });
    </script>
@endsection
@endsection
