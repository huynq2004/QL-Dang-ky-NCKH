<x-modal name="new-student-proposal" :show="$errors->newProposal->isNotEmpty()" focusable>
    <form method="POST" action="{{ route('proposals.store') }}" class="p-6">
        @csrf

        <h2 class="text-lg font-medium text-gray-900 mb-4">
            {{ __('Create Research Topic') }}
        </h2>

        <!-- Research Information -->
        <div class="space-y-4">
            <div>
                <x-input-label for="title" :value="__('Research Title')" />
                <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" required autofocus />
                <x-input-error :messages="$errors->newProposal->get('title')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="field" :value="__('Research Field')" />
                <x-text-input id="field" name="field" type="text" class="mt-1 block w-full" required />
                <x-input-error :messages="$errors->newProposal->get('field')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="description" :value="__('Description')" />
                <x-textarea id="description" name="description" class="mt-1 block w-full" rows="4"></x-textarea>
                <x-input-error :messages="$errors->newProposal->get('description')" class="mt-2" />
            </div>
        </div>

        <!-- Supervisor Selection -->
        <div class="mt-6">
            <h3 class="text-sm font-medium text-gray-700 mb-2">{{ __('Supervisor Information') }}</h3>
            <div class="space-y-4">
                <div>
                    <x-input-label for="lecturer_id" :value="__('Select Supervisor')" />
                    <select id="lecturer_id" name="lecturer_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                        <option value="">{{ __('Choose a lecturer') }}</option>
                        @foreach($lecturers ?? [] as $lecturer)
                            <option value="{{ $lecturer->id }}">
                                {{ $lecturer->user->name }} - {{ $lecturer->department }}
                                ({{ $lecturer->title }})
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->newProposal->get('lecturer_id')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="message" :value="__('Message to Supervisor (Optional)')" />
                    <x-textarea id="message" name="message" class="mt-1 block w-full" rows="3" 
                        placeholder="{{ __('Introduce yourself and explain why you are interested in this research topic...') }}">
                    </x-textarea>
                    <x-input-error :messages="$errors->newProposal->get('message')" class="mt-2" />
                </div>
            </div>
        </div>

        <!-- Information Note -->
        <div class="mt-6 p-4 bg-blue-50 rounded-md">
            <p class="text-sm text-blue-600">
                {{ __('Note: Your research topic will be saved as a draft and a request will be sent to the selected supervisor. The topic will become active once the supervisor accepts your request.') }}
            </p>
        </div>

        <div class="mt-6 flex justify-end gap-4">
            <x-secondary-button x-on:click="$dispatch('close')">
                {{ __('Cancel') }}
            </x-secondary-button>

            <x-primary-button>
                {{ __('Create and Send Request') }}
            </x-primary-button>
        </div>
    </form>
</x-modal>

@foreach($lecturers ?? [] as $lecturer)
    <x-modal name="invite-lecturer-{{ $lecturer->id }}" :show="$errors->{'invite-'.$lecturer->id}->isNotEmpty()" focusable>
        <form method="POST" action="{{ route('invitations.store') }}" class="p-6">
            @csrf
            <input type="hidden" name="lecturer_id" value="{{ $lecturer->id }}">

            <h2 class="text-lg font-medium text-gray-900">
                {{ __('Request Supervision from') }} {{ $lecturer->user->name }}
            </h2>

            <div class="mt-6">
                <x-input-label for="proposal_id" :value="__('Select Research Topic')" />
                <select id="proposal_id" name="proposal_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                    <option value="">{{ __('Select a topic') }}</option>
                    @foreach($lecturer->proposals->where('status', 'active') as $proposal)
                        <option value="{{ $proposal->id }}">{{ $proposal->title }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->{'invite-'.$lecturer->id}->get('proposal_id')" class="mt-2" />
            </div>

            <div class="mt-6">
                <x-input-label for="message" :value="__('Message (Optional)')" />
                <x-textarea id="message" name="message" class="mt-1 block w-full" rows="3"></x-textarea>
                <x-input-error :messages="$errors->{'invite-'.$lecturer->id}->get('message')" class="mt-2" />
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