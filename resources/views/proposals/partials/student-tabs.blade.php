<!-- My Research Topics Tab -->
<div id="my-topics" class="tab-content {{ $activeTab !== 'my-topics' ? 'hidden' : '' }}">
    <div class="mb-4 text-end">
        <x-primary-button type="button" x-data="" x-on:click.prevent="$dispatch('open-modal', 'new-student-proposal')">
            {{ __('Create Research Topic') }}
        </x-primary-button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($studentProposals ?? [] as $proposal)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium mb-2">{{ $proposal->title }}</h3>
                    <p class="text-sm text-gray-600 mb-4">{{ $proposal->field }}</p>
                    
                    @if($proposal->description)
                        <p class="text-gray-600 mb-4">{{ Str::limit($proposal->description, 150) }}</p>
                    @endif

                    <div class="flex space-x-4">
                        <a href="{{ route('proposals.show', $proposal) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            {{ __('View Details') }}
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-2">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-500">
                        {{ __('You haven\'t created any research topics yet.') }}
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>

<!-- Find Supervisor Tab -->
<div id="lecturers" class="tab-content {{ $activeTab !== 'lecturers' ? 'hidden' : '' }}">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($lecturers ?? [] as $lecturer)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium mb-2">{{ $lecturer->user->name }}</h3>
                    <div class="mb-4 text-sm">
                        <p><strong>{{ __('Department') }}:</strong> {{ $lecturer->department }}</p>
                        <p><strong>{{ __('Title') }}:</strong> {{ $lecturer->title }}</p>
                        <p><strong>{{ __('Specialization') }}:</strong> {{ $lecturer->specialization }}</p>
                    </div>

                    <div class="flex space-x-4">
                        <x-primary-button type="button" x-data="" x-on:click.prevent="$dispatch('open-modal', 'invite-lecturer-{{ $lecturer->id }}')">
                            {{ __('Request Supervision') }}
                        </x-primary-button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-2">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-500">
                        {{ __('No lecturers available.') }}
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div> 