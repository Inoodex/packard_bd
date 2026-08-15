@extends('frontend.layouts.app')

@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <div class="content-page-header">
                <h5> Users</h5>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    @if (session('sweet_alert'))
                        <script>
                            Swal.fire({
                                icon: '{{ session('sweet_alert.type') }}',
                                title: '{{ session('sweet_alert.title') }}',
                                text: '{{ session('sweet_alert.text') }}',
                            });
                        </script>
                    @endif

                    <div class="card-header align-items-center d-flex">
                        <h4 class="card-title mb-0 flex-grow-1"></h4>
                        <div class="flex-shrink-0">
                            <div class="form-check form-switch form-switch-right form-switch-md">
                                <a href="{{ route('users.create') }}" class="btn btn-info">Create Users</a>
                            </div>
                        </div>
                    </div>
                    <!-- end card header -->
                    <div class="card-body">
                        <div class="live-preview">
                            <div class="row gy-4">
                                <table class="table" id="dataTbl">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Signature Image</th>
                                            <th>Name</th>
                                            <th>Designation</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <!-- <th>Type</th> -->
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($users as $item)
                                            <tr>
                                                <td>{{ $loop->index + 1 }}</td>
                                                <td>
                                                    <img width="60px" height="60px"
                                                        src="{{ asset('frontend/users/' . $item->images) }}"
                                                        alt="Users Image">
                                                </td>
                                                <td>{{ $item->name }} <br><span
                                                        class="text-muted">{{ $item->roleName ?? 'N/A' }}</span>
                                                </td>
                                                <td>{{ Str::limit($item->designation, 20, '...') }}</td>
                                                <td>{{ $item->email }}</td>
                                                <td>{{ $item->phone }}</td>
                                                <!-- <td>{{ $item->roleName ?? 'N/A' }}</td> -->
                                                <td class="{{ $item->status == '1' ? 'text-success' : 'text-danger' }}">
                                                    {{ $item->status == '1' ? 'Active' : 'Inactive' }}
                                                </td>
                                                <td>
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-light border" type="button"
                                                            data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="fas fa-ellipsis-v"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end">
                                                            <li>
                                                                <a class="dropdown-item"
                                                                    href="{{ route('users.edit', $item->id) }}">Edit</a>
                                                            </li>
                                                            <li>
                                                                <button type="button" class="dropdown-item text-danger"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#myModal{{ $item->id }}">
                                                                    Delete
                                                                </button>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </td>

                                                <!-- Default Modals -->
                                                <div id="myModal{{ $item->id }}" class="modal fade" tabindex="-1"
                                                    aria-labelledby="myModalLabel" aria-hidden="true"
                                                    style="display: none;">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="myModalLabel">Delete</h5>
                                                                <button type="button" class="btn-close"
                                                                    data-bs-dismiss="modal" aria-label="Close"> </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                Are you sure you want to delete this Users:
                                                                <strong
                                                                    style="color: darkorange">{{ $item->name }}</strong>
                                                                ?
                                                            </div>
                                                            <div class="modal-footer">

                                                                <form action="{{ route('users.destroy', $item->id) }}"
                                                                    method="post">
                                                                    @csrf
                                                                    @method('delete')
                                                                    <button type="submit"
                                                                        class="btn btn-danger">Delete</button>

                                                                </form>
                                                                <button type="button" class="btn btn-secondary"
                                                                    data-bs-dismiss="modal">Close</button>
                                                            </div>

                                                        </div><!-- /.modal-content -->
                                                    </div><!-- /.modal-dialog -->
                                                </div><!-- /.modal -->
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
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
@endsection
@endsection
