@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Lời mời hướng dẫn') }}</div>

                <div class="card-body">
                    {{-- Khối hiện session cũ: đã thay bằng toast ở layout --}}

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
                                    <th class="text-end"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invitations as $invitation)
                                    <tr>
                                        <td>{{ $invitation->proposal->title }}</td>
                                        <td>{{ $invitation->student?->user?->name }}</td>
                                        <td>{{ $invitation->lecturer?->user?->name }}</td>
                                        <td>
                                            @php
                                                $badge = match($invitation->status) {
                                                    'accepted' => 'success',
                                                    'pending' => 'warning',
                                                    'rejected' => 'danger',
                                                    'expired' => 'secondary',
                                                    'withdrawn' => 'secondary',
                                                    default => 'secondary'
                                                };
                                            @endphp
                                            <span class="badge bg-{{ $badge }}">
                                                {{ ucfirst($invitation->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $invitation->created_at->format('Y-m-d H:i') }}</td>
                                        <td>
                                            @if($invitation->status === 'pending' && optional(auth()->user()->lecturer)->id === $invitation->lecturer_id)
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
                                        <td class="text-end">
                                            @if($invitation->status === 'pending')
                                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="alert('Lời mời đang chờ xử lý không thể xoá.');">×</button>
                                            @else
                                                <form action="{{ route('invitations.destroy', $invitation) }}" method="POST" class="d-inline" onsubmit="return confirm('Xoá lời mời này khỏi hệ thống?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger btn-sm">×</button>
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