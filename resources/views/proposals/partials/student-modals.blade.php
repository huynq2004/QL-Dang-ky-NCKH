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