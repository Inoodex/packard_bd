@extends('frontend.layouts.app')

@section('content')
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="content-page-header">
                <h5>Permission List</h5>
            </div>
        </div>
        <!-- /Page Header -->

        <div class="row">
            <div class="col">
                <div class="card">

                    <!-- <div class="card-header">
                        <div class="d-flex justify-content-end">
                            <a href="{{ route('permission.create') }}" class="btn btn-info">Create Permissions</a>
                        </div>
                    </div> -->

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead>
                                    <tr>
                                        <th>SL</th>
                                        <th>Name</th>
                                        <th>Action</th>
                                        <th>SL</th>
                                        <th>Name</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($permissions->chunk(2) as $chunk)
                                        <tr>
                                            @foreach ($chunk as $item)
                                                <td>{{ $loop->parent->index * 2 + $loop->index + 1 }}</td>
                                                <td>{{ $item->name }}</td>
                                                <td>
                                                    <div class="dropdown opacity-50">
                                                        <button class="btn btn-sm btn-light border" type="button"
                                                            disabled aria-disabled="true" title="Read only"
                                                            style="cursor: not-allowed;">
                                                            <i class="fas fa-ellipsis-v"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            @endforeach

                                            @if ($chunk->count() < 2)
                                                <!-- Fill empty cells if chunk has only 1 item -->
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>
@endsection
