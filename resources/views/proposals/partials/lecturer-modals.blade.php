<x-modal name="new-lecturer-proposal" :show="$errors->isNotEmpty()" focusable>
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

@foreach($proposals ?? [] as $proposal)
    @if($proposal->lecturer_id === Auth::user()->lecturer->id)
        <x-modal name="edit-proposal-{{ $proposal->id }}" focusable>
            <form method="POST" action="{{ route('proposals.update', $proposal) }}" class="p-6">
                @csrf
                @method('PUT')

                <h2 class="text-lg font-medium text-gray-900">
                    {{ __('Edit Research Topic') }}
                </h2>

                <div class="mt-6">
                    <x-input-label for="title-{{ $proposal->id }}" :value="__('Title')" />
                    <x-text-input id="title-{{ $proposal->id }}" name="title" type="text" class="mt-1 block w-full" :value="$proposal->title" required />
                    <x-input-error class="mt-2" :messages="$errors->get('title')" />
                </div>

                <div class="mt-6">
                    <x-input-label for="field-{{ $proposal->id }}" :value="__('Field of Research')" />
                    <x-text-input id="field-{{ $proposal->id }}" name="field" type="text" class="mt-1 block w-full" :value="$proposal->field" required />
                    <x-input-error class="mt-2" :messages="$errors->get('field')" />
                </div>

                <div class="mt-6">
                    <x-input-label for="description-{{ $proposal->id }}" :value="__('Description')" />
                    <x-textarea id="description-{{ $proposal->id }}" name="description" class="mt-1 block w-full">{{ $proposal->description }}</x-textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('description')" />
                </div>

                <div class="mt-6 flex justify-end">
                    <x-secondary-button x-on:click="$dispatch('close')">
                        {{ __('Cancel') }}
                    </x-secondary-button>

                    <x-primary-button class="ms-3">
                        {{ __('Save Changes') }}
                    </x-primary-button>
                </div>
            </form>
        </x-modal>
    @endif
@endforeach 