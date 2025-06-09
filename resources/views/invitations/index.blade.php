@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Research Invitations') }}</div>

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
                                    <th>Proposal</th>
                                    <th>Student</th>
                                    <th>Lecturer</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
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
                                                    <button type="submit" class="btn btn-success btn-sm">Accept</button>
                                                </form>
                                                <form action="{{ route('invitations.reject', $invitation) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-danger btn-sm">Reject</button>
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