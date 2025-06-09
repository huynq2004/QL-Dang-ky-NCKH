@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    {{ __('Research Proposals') }}
                    <a href="{{ route('proposals.create') }}" class="btn btn-primary btn-sm">Create New Proposal</a>
                </div>

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
                                    <th>Title</th>
                                    <th>Student</th>
                                    <th>Lecturer</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($proposals as $proposal)
                                    <tr>
                                        <td>{{ $proposal->title }}</td>
                                        <td>{{ $proposal->student->name }}</td>
                                        <td>{{ $proposal->lecturer->name }}</td>
                                        <td>
                                            <span class="badge bg-{{ $proposal->status === 'approved' ? 'success' : ($proposal->status === 'pending' ? 'warning' : 'danger') }}">
                                                {{ ucfirst($proposal->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $proposal->created_at->format('Y-m-d H:i') }}</td>
                                        <td>
                                            <a href="{{ route('proposals.show', $proposal) }}" class="btn btn-info btn-sm">View</a>
                                            @if($proposal->student_id === auth()->id())
                                                <a href="{{ route('proposals.edit', $proposal) }}" class="btn btn-warning btn-sm">Edit</a>
                                                <form action="{{ route('proposals.destroy', $proposal) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
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