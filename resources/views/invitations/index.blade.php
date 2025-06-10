@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Lời mời hướng dẫn') }}</div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Đề tài</th>
                                    <th>Sinh viên</th>
                                    <th>Giảng viên</th>
                                    <th>Trạng thái</th>
                                    <th>Ngày tạo</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invitations as $invitation)
                                    <tr>
                                        <td>{{ $invitation->proposal->title }}</td>
                                        <td>{{ $invitation->student->name }}</td>
                                        <td>{{ $invitation->lecturer->name }}</td>
                                        <td>
                                            <span class="badge bg-{{ $invitation->status === 'accepted' ? 'success' : ($invitation->status === 'pending' ? 'warning' : 'danger') }}">
                                                {{ ucfirst($invitation->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $invitation->created_at->format('Y-m-d H:i') }}</td>
                                        <td>
                                            @if($invitation->status === 'pending' && $invitation->lecturer_id === auth()->id())
                                                <form action="{{ route('invitations.accept', $invitation) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success btn-sm">Chấp nhận</button>
                                                </form>
                                                <form action="{{ route('invitations.reject', $invitation) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-danger btn-sm">Từ chối</button>
                                                </form>
                                            @endif
                                        </td>
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