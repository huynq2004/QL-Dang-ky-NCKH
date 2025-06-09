<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Research Management') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(Auth::user()->role === 'student')
                <!-- Tab Navigation -->
                <div class="mb-4 border-b border-gray-200">
                    <ul class="flex flex-wrap -mb-px text-sm font-medium text-center">
                        <li class="me-2">
                            <a href="{{ route('dashboard') }}" 
                               class="inline-block p-4 border-b-2 rounded-t-lg {{ $activeTab === 'available' ? 'border-indigo-600 text-indigo-600' : 'border-transparent' }}">
                                {{ __('Available Proposals') }}
                            </a>
                        </li>
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
                        <li class="me-2">
                            <a href="{{ route('my-invitations') }}"
                               class="inline-block p-4 border-b-2 rounded-t-lg {{ $activeTab === 'invitations' ? 'border-indigo-600 text-indigo-600' : 'border-transparent' }}">
                                {{ __('My Requests') }}
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Tab Contents -->
                <div>
                    <!-- Available Proposals Tab -->
                    <div id="available" class="tab-content {{ $activeTab !== 'available' ? 'hidden' : '' }}">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @forelse($proposals ?? [] as $proposal)
                                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                                    <div class="p-6">
                                        <h3 class="text-lg font-medium mb-2">{{ $proposal->title }}</h3>
                                        <p class="text-sm text-gray-600 mb-4">{{ $proposal->field }}</p>
                                        
                                        <div class="mb-4 text-sm">
                                            <p><strong>{{ __('Lecturer') }}:</strong> {{ $proposal->lecturer->user->name }}</p>
                                            <p><strong>{{ __('Department') }}:</strong> {{ $proposal->lecturer->department }}</p>
                                        </div>

                                        @if($proposal->description)
                                            <p class="text-gray-600 mb-4">{{ Str::limit($proposal->description, 150) }}</p>
                                        @endif

                                        <div class="flex space-x-4">
                                            <a href="{{ route('proposals.show', $proposal) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                                {{ __('View Details') }}
                                            </a>
                                            <form action="{{ route('proposals.request', $proposal) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500">
                                                    {{ __('Request to Join') }}
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
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

                    <!-- Invitations Tab -->
                    <div id="invitations" class="tab-content {{ $activeTab !== 'invitations' ? 'hidden' : '' }}">
                        @if(isset($invitations) && $invitations->count() > 0)
                            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Lecturer') }}</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Proposal') }}</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Status') }}</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Sent At') }}</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($invitations as $invitation)
                                                <tr>
                                                    <td class="px-6 py-4 whitespace-nowrap">{{ $invitation->lecturer->user->name }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        @if($invitation->proposal)
                                                            {{ $invitation->proposal->title }}
                                                        @else
                                                            <em>{{ __('General Research Interest') }}</em>
                                                        @endif
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                            {{ $invitation->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                                               ($invitation->status === 'accepted' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800') }}">
                                                            {{ ucfirst($invitation->status) }}
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">{{ $invitation->created_at->format('Y-m-d H:i') }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        @if($invitation->status === 'pending')
                                                            <form action="{{ route('proposals.withdraw-request', $invitation) }}" method="POST" class="inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500">
                                                                    {{ __('Withdraw Request') }}
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @else
                            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                                <div class="p-6 text-gray-500">
                                    {{ __('You haven\'t sent any requests yet.') }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Modals -->
                <x-modal name="new-student-proposal" :show="$errors->isNotEmpty()" focusable>
                    <form method="POST" action="{{ route('proposals.store') }}" class="p-6">
                        @csrf

                        <h2 class="text-lg font-medium text-gray-900">
                            {{ __('Create Research Topic') }}
                        </h2>

                        <div class="mt-6">
                            <x-input-label for="title" :value="__('Title')" />
                            <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" required autofocus />
                            <x-input-error class="mt-2" :messages="$errors->get('title')" />
                        </div>

                        <div class="mt-6">
                            <x-input-label for="field" :value="__('Field of Research')" />
                            <x-text-input id="field" name="field" type="text" class="mt-1 block w-full" required />
                            <x-input-error class="mt-2" :messages="$errors->get('field')" />
                        </div>

                        <div class="mt-6">
                            <x-input-label for="description" :value="__('Description')" />
                            <x-textarea id="description" name="description" class="mt-1 block w-full"></x-textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('description')" />
                        </div>

                        <div class="mt-6">
                            <x-input-label for="lecturer_id" :value="__('Preferred Supervisor')" />
                            <select id="lecturer_id" name="lecturer_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="">{{ __('Select a lecturer') }}</option>
                                @foreach($lecturers ?? [] as $lecturer)
                                    <option value="{{ $lecturer->id }}">
                                        {{ $lecturer->user->name }} ({{ $lecturer->department }})
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('lecturer_id')" />
                        </div>

                        <div class="mt-6 flex justify-end">
                            <x-secondary-button x-on:click="$dispatch('close')">
                                {{ __('Cancel') }}
                            </x-secondary-button>

                            <x-primary-button class="ms-3">
                                {{ __('Create') }}
                            </x-primary-button>
                        </div>
                    </form>
                </x-modal>

                @foreach($lecturers ?? [] as $lecturer)
                    <x-modal name="invite-lecturer-{{ $lecturer->id }}" focusable>
                        <form method="POST" action="{{ route('proposals.store') }}" class="p-6">
                            @csrf
                            <input type="hidden" name="lecturer_id" value="{{ $lecturer->id }}">

                            <h2 class="text-lg font-medium text-gray-900">
                                {{ __('Request Supervision from') }} {{ $lecturer->user->name }}
                            </h2>

                            <div class="mt-6">
                                <x-input-label for="title" :value="__('Research Topic Title')" />
                                <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" required autofocus />
                                <x-input-error class="mt-2" :messages="$errors->get('title')" />
                            </div>

                            <div class="mt-6">
                                <x-input-label for="field" :value="__('Field of Research')" />
                                <x-text-input id="field" name="field" type="text" class="mt-1 block w-full" required />
                                <x-input-error class="mt-2" :messages="$errors->get('field')" />
                            </div>

                            <div class="mt-6">
                                <x-input-label for="description" :value="__('Description')" />
                                <x-textarea id="description" name="description" class="mt-1 block w-full"></x-textarea>
                                <x-input-error class="mt-2" :messages="$errors->get('description')" />
                            </div>

                            <div class="mt-6 flex justify-end">
                                <x-secondary-button x-on:click="$dispatch('close')">
                                    {{ __('Cancel') }}
                                </x-secondary-button>

                                <x-primary-button class="ms-3">
                                    {{ __('Send Request') }}
                                </x-primary-button>
                            </div>
                        </form>
                    </x-modal>
                @endforeach
            @else
                <!-- Lecturer/Admin View -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @if(Auth::user()->role === 'lecturer')
                        <div class="mb-4 text-end col-span-2">
                            <x-primary-button type="button" x-data="" x-on:click.prevent="$dispatch('open-modal', 'new-proposal')">
                                {{ __('Add New Proposal') }}
                            </x-primary-button>
                        </div>
                    @endif

                    @forelse($proposals ?? [] as $proposal)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium mb-2">{{ $proposal->title }}</h3>
                                <p class="text-sm text-gray-600 mb-4">{{ $proposal->field }}</p>
                                
                                <div class="mb-4 text-sm">
                                    <p><strong>{{ __('Lecturer') }}:</strong> {{ $proposal->lecturer->user->name }}</p>
                                    <p><strong>{{ __('Department') }}:</strong> {{ $proposal->lecturer->department }}</p>
                                    @if(Auth::user()->isAdmin())
                                        <p><strong>{{ __('Status') }}:</strong> {{ ucfirst($proposal->status) }}</p>
                                    @endif
                                </div>

                                @if($proposal->description)
                                    <p class="text-gray-600 mb-4">{{ Str::limit($proposal->description, 150) }}</p>
                                @endif

                                <div class="flex space-x-4">
                                    <a href="{{ route('proposals.show', $proposal) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                        {{ __('View Details') }}
                                    </a>
                                    @if(Auth::user()->role === 'lecturer' && $proposal->lecturer_id === Auth::user()->lecturer->id)
                                        <x-secondary-button x-data="" x-on:click.prevent="$dispatch('open-modal', 'edit-proposal-{{ $proposal->id }}')">
                                            {{ __('Edit') }}
                                        </x-secondary-button>
                                    @endif
                                </div>
                            </div>
                        </div>
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
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        // Remove the old tab handling JavaScript since we're using links now
    </script>
    @endpush
</x-app-layout> 