<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Research Management') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Tab Navigation -->
            <div class="mb-4 border-b border-gray-200">
                <ul class="flex flex-wrap -mb-px text-sm font-medium text-center">
                    <li class="me-2">
                        <a href="{{ route('dashboard') }}" 
                           class="inline-block p-4 border-b-2 rounded-t-lg {{ $activeTab === 'available' ? 'border-indigo-600 text-indigo-600' : 'border-transparent' }}">
                            {{ __('Available Proposals') }}
                        </a>
                    </li>
                    @if(Auth::user()->role === 'student')
                    <li class="me-2">
                        <a href="{{ route('my-topics') }}"
                           class="inline-block p-4 border-b-2 rounded-t-lg {{ $activeTab === 'my-topics' ? 'border-indigo-600 text-indigo-600' : 'border-transparent' }}">
                            {{ __('My Research Topics') }}
                        </a>
                    </li>
                    <li class="me-2">
                        <a href="{{ route('find-supervisor') }}"
                           class="inline-block p-4 border-b-2 rounded-t-lg {{ $activeTab === 'lecturers' ? 'border-indigo-600 text-indigo-600' : 'border-transparent' }}">
                            {{ __('Find Supervisor') }}
                        </a>
                    </li>
                    @endif
                    <li class="me-2">
                        <a href="{{ route('my-invitations') }}"
                           class="inline-block p-4 border-b-2 rounded-t-lg {{ $activeTab === 'invitations' ? 'border-indigo-600 text-indigo-600' : 'border-transparent' }}">
                            {{ Auth::user()->role === 'lecturer' ? __('Student Requests') : __('My Requests') }}
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Tab Contents -->
            <div>
                <!-- Available Proposals Tab -->
                <div id="available" class="tab-content {{ $activeTab !== 'available' ? 'hidden' : '' }}">
                    @if(Auth::user()->role === 'lecturer')
                    <div class="mb-4 text-end">
                        <x-primary-button type="button" x-data="" x-on:click.prevent="$dispatch('open-modal', 'new-lecturer-proposal')">
                            {{ __('Create Research Topic') }}
                        </x-primary-button>
                    </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @forelse($proposals ?? [] as $proposal)
                            @if($proposal->status !== 'draft')
                            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                                <div class="p-6">
                                    <h3 class="text-xl font-semibold mb-2">{{ $proposal->title }}</h3>
                                    <p class="text-gray-600 mb-4">{{ $proposal->field }}</p>
                                    
                                    <div class="mb-4">
                                        <p class="mb-1"><span class="font-semibold">{{ __('Lecturer') }}:</span> {{ $proposal->lecturer->user->name }}</p>
                                        <p><span class="font-semibold">{{ __('Department') }}:</span> {{ $proposal->lecturer->department }}</p>
                                    </div>

                                    @if($proposal->description)
                                        <p class="text-gray-600 mb-6">{{ $proposal->description }}</p>
                                    @endif

                                    <div class="flex items-center gap-4">
                                        <a href="{{ route('proposals.show', $proposal) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-gray-800 rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                            {{ __('View Details') }}
                                        </a>
                                        @if(Auth::user()->role === 'student')
                                            @php
                                                $existingRequest = App\Models\Invitation::where([
                                                    'student_id' => Auth::user()->student->id,
                                                    'proposal_id' => $proposal->id
                                                ])->first();
                                            @endphp

                                            @if(!$existingRequest && $proposal->status === 'active')
                                                <form action="{{ route('proposals.request', $proposal) }}" method="POST" class="inline">
                                                    @csrf
                                                    <x-secondary-button type="submit">
                                                        {{ __('Request to Join') }}
                                                    </x-secondary-button>
                                                </form>
                                            @elseif($existingRequest)
                                                <span class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-200 rounded-md font-semibold text-xs text-gray-700 uppercase">
                                                    {{ ucfirst($existingRequest->status) }}
                                                </span>
                                                @if($existingRequest->status === 'pending')
                                                    <form action="{{ route('proposals.withdraw-request', $existingRequest) }}" method="POST" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <x-danger-button>
                                                            {{ __('Withdraw') }}
                                                        </x-danger-button>
                                                    </form>
                                                @endif
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endif
                        @empty
                            <div class="col-span-2">
                                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                                    <div class="p-6 text-gray-500">
                                        {{ __('No proposals found.') }}
                                    </div>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>

                @if(Auth::user()->role === 'student')
                <!-- Student-specific tabs content -->
                @include('proposals.partials.student-tabs')
                @endif

                <!-- Invitations Tab -->
                <div id="invitations" class="tab-content {{ $activeTab !== 'invitations' ? 'hidden' : '' }}">
                    @if(Auth::user()->role === 'lecturer')
                        @include('proposals.partials.lecturer-invitations')
                    @else
                        @include('proposals.partials.student-invitations')
                    @endif
                </div>
            </div>

            <!-- Modals -->
            @if(Auth::user()->role === 'student')
                @include('proposals.partials.student-modals')
            @else
                @include('proposals.partials.lecturer-modals')
            @endif
        </div>
    </div>
</x-app-layout>