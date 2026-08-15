@extends('frontend.layouts.app')

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="content-page-header">
                <h5>Create Role</h5>
            </div>
        </div>
        <!-- /Page Header -->
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
                setTimeout(function () {
                    document.getElementById('validation-error-alert').style.display = 'none';
                }, 3000);
            </script>
        @endif
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header align-items-center d-flex">
                        <h4 class="card-title mb-0 flex-grow-1">Create Role</h4>
                        <div class="flex-shrink-0">
                            <div class="form-check form-switch form-switch-right form-switch-md">
                                <a href="{{ route('role.index') }}" class="btn btn-info">Role List</a>
                            </div>
                        </div>
                    </div>
                    <!-- end card header -->
                    <div class="card-body">
                        <div class="live-preview">
                            <div class="row gy-4">
                                <form action="{{ route('role.store') }}" method="post" enctype="multipart/form-data">
                                    @csrf
                                    <div class="row">
                                        <div class="col-12 col-md-4">
                                            <label for="name" class="form-label">Name</label>
                                            <input type="text" class="form-control" value="{{ old('name') }}" id="name"
                                                name="name" placeholder="Enter User name">
                                        </div>
                                        <div class="col-12 col-md-8">
                                            <h5>Parmissions</h5>
                                            <hr style="margin:0px;">
                                            <div class="row mt-2">
                                                @foreach ($permissions as $item)
                                                    <div class="col-12 col-sm-6 col-md-4 col-lg-3 shadow-lg">
                                                        <div class="form-check form-switch" style="padding: 0px;">
                                                            <label
                                                                for="permission_{{ $item->id }}">{{ $item->name }}</label><br>
                                                            <input class="form-check-input mb-2"
                                                                style="margin-left: 0.5em !important;" type="checkbox"
                                                                role="switch" name="permissions[]"
                                                                id="permission_{{ $item->id }}" value="{{ $item->id }}" />
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
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