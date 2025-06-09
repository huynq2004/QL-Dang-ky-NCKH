<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Research Topic Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="mb-6">
                        <h1 class="text-2xl font-bold mb-2">{{ $proposal->title }}</h1>
                        <p class="text-sm text-gray-600">{{ $proposal->field }}</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div>
                            <h3 class="font-semibold mb-2">{{ __('Supervisor') }}</h3>
                            <p>{{ $proposal->lecturer->user->name }}</p>
                            <p class="text-sm text-gray-600">{{ $proposal->lecturer->department }}</p>
                            <p class="text-sm text-gray-600">{{ $proposal->lecturer->title }}</p>
                        </div>

                        <div>
                            <h3 class="font-semibold mb-2">{{ __('Status') }}</h3>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $proposal->status === 'active' ? 'bg-green-100 text-green-800' : 
                                   ($proposal->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                {{ ucfirst($proposal->status) }}
                            </span>
                        </div>
                    </div>

                    @if($proposal->description)
                        <div class="mb-6">
                            <h3 class="font-semibold mb-2">{{ __('Description') }}</h3>
                            <div class="prose max-w-none">
                                {{ $proposal->description }}
                            </div>
                        </div>
                    @endif

                    <!-- Participating Students -->
                    <div class="mb-6">
                        <h3 class="font-semibold mb-4">{{ __('Participating Students') }}</h3>
                        @php
                            $acceptedInvitations = $proposal->invitations->where('status', 'accepted');
                        @endphp
                        
                        @if($acceptedInvitations->isNotEmpty())
                            <div class="bg-white rounded-lg border border-gray-200">
                                <ul class="divide-y divide-gray-200">
                                    @foreach($acceptedInvitations as $invitation)
                                        <li class="p-4">
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <p class="font-medium text-gray-900">{{ $invitation->student->user->name }}</p>
                                                    <p class="text-sm text-gray-500">{{ $invitation->student->student_id }}</p>
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ __('Joined: ') }} {{ $invitation->updated_at->format('d/m/Y') }}
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @else
                            <p class="text-gray-500">{{ __('No students are currently participating in this research topic.') }}</p>
                        @endif
                    </div>

                    <!-- Pending Requests -->
                    @if(Auth::user()->role === 'lecturer' && Auth::user()->lecturer->id === $proposal->lecturer_id)
                        <div class="mb-6">
                            <h3 class="font-semibold mb-4">{{ __('Pending Requests') }}</h3>
                            @php
                                $pendingInvitations = $proposal->invitations->where('status', 'pending');
                            @endphp
                            
                            @if($pendingInvitations->isNotEmpty())
                                <div class="bg-white rounded-lg border border-gray-200">
                                    <ul class="divide-y divide-gray-200">
                                        @foreach($pendingInvitations as $invitation)
                                            <li class="p-4">
                                                <div class="flex items-center justify-between">
                                                    <div>
                                                        <p class="font-medium text-gray-900">{{ $invitation->student->user->name }}</p>
                                                        <p class="text-sm text-gray-500">{{ $invitation->student->student_id }}</p>
                                                    </div>
                                                    <div class="flex items-center gap-2">
                                                        <form action="{{ route('invitations.accept', $invitation) }}" method="POST" class="inline">
                                                            @csrf
                                                            <x-primary-button>
                                                                {{ __('Accept') }}
                                                            </x-primary-button>
                                                        </form>
                                                        <form action="{{ route('invitations.reject', $invitation) }}" method="POST" class="inline">
                                                            @csrf
                                                            <x-danger-button>
                                                                {{ __('Reject') }}
                                                            </x-danger-button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @else
                                <p class="text-gray-500">{{ __('No pending requests.') }}</p>
                            @endif
                        </div>
                    @endif

                    <div class="mt-6 flex justify-between">
                        <a href="{{ url()->previous() }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            {{ __('Back') }}
                        </a>

                        @if(isset($canRequest) && $canRequest)
                            <form action="{{ route('proposals.request', $proposal) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500">
                                    {{ __('Request to Join') }}
                                </button>
                            </form>
                        @elseif(isset($existingRequest))
                            <div class="flex items-center">
                                <span class="text-sm text-gray-600 mr-4">
                                    {{ __('Request Status:') }}
                                    <span class="font-semibold">{{ ucfirst($existingRequest->status) }}</span>
                                </span>
                                
                                @if($existingRequest->status === 'pending')
                                    <form action="{{ route('proposals.withdraw-request', $existingRequest) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500">
                                            {{ __('Withdraw Request') }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 