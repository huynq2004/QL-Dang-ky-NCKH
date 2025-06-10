<!-- New Research Topic Modal -->
<x-modal name="new-lecturer-proposal" :show="$errors->newProposal->isNotEmpty()" focusable>
    <form method="POST" action="{{ route('proposals.store') }}" class="p-6">
        @csrf

        <h2 class="text-lg font-medium text-gray-900 mb-4">
            {{ __('Tạo đề tài nghiên cứu') }}
        </h2>

        <!-- Title -->
        <div class="mt-4">
            <x-input-label for="title" :value="__('Tiêu đề đề tài nghiên cứu')" />
            <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" required />
            <x-input-error :messages="$errors->newProposal->get('title')" class="mt-2" />
        </div>

        <!-- Field -->
        <div class="mt-4">
            <x-input-label for="field" :value="__('Lĩnh vực nghiên cứu')" />
            <x-text-input id="field" name="field" type="text" class="mt-1 block w-full" required />
            <x-input-error :messages="$errors->newProposal->get('field')" class="mt-2" />
        </div>

        <!-- Description -->
        <div class="mt-4">
            <x-input-label for="description" :value="__('Mô tả')" />
            <x-textarea id="description" name="description" class="mt-1 block w-full" rows="4"></x-textarea>
            <x-input-error :messages="$errors->newProposal->get('description')" class="mt-2" />
        </div>

        <!-- Information Note -->
        <div class="mt-6 p-4 bg-blue-50 rounded-md">
            <p class="text-sm text-blue-600">
                {{ __('Lưu ý: Đề tài nghiên cứu sẽ được tạo ở trạng thái hoạt động và sẵn sàng cho sinh viên đăng ký.') }}
            </p>
        </div>

        <div class="mt-6 flex justify-end">
            <x-secondary-button x-on:click="$dispatch('close')">
                {{ __('Hủy') }}
            </x-secondary-button>

            <x-primary-button class="ml-3">
                {{ __('Tạo') }}
            </x-primary-button>
        </div>
    </form>
</x-modal>

<!-- Edit Research Topic Modal -->
@foreach($lecturerProposals ?? [] as $proposal)
<x-modal name="edit-proposal-{{ $proposal->id }}" :show="$errors->{'edit-'.$proposal->id}->isNotEmpty()" focusable>
    <form method="POST" action="{{ route('proposals.update', $proposal) }}" class="p-6">
        @csrf
        @method('PUT')

        <!-- Title -->
        <div class="mt-4">
            <x-input-label for="title" :value="__('Tiêu đề đề tài nghiên cứu')" />
            <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="$proposal->title" required />
            <x-input-error :messages="$errors->{'edit-'.$proposal->id}->get('title')" class="mt-2" />
        </div>

        <!-- Field -->
        <div class="mt-4">
            <x-input-label for="field" :value="__('Lĩnh vực nghiên cứu')" />
            <x-text-input id="field" name="field" type="text" class="mt-1 block w-full" :value="$proposal->field" required />
            <x-input-error :messages="$errors->{'edit-'.$proposal->id}->get('field')" class="mt-2" />
        </div>

        <!-- Description -->
        <div class="mt-4">
            <x-input-label for="description" :value="__('Mô tả')" />
            <x-textarea id="description" name="description" class="mt-1 block w-full" rows="4">{{ $proposal->description }}</x-textarea>
            <x-input-error :messages="$errors->{'edit-'.$proposal->id}->get('description')" class="mt-2" />
        </div>

        <!-- Status -->
        <div class="mt-4">
            <x-input-label for="status" :value="__('Trạng thái')" />
            <select id="status" name="status" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                <option value="draft" {{ $proposal->status === 'draft' ? 'selected' : '' }}>{{ __('Nháp') }}</option>
                <option value="active" {{ $proposal->status === 'active' ? 'selected' : '' }}>{{ __('Hoạt động') }}</option>
                <option value="completed" {{ $proposal->status === 'completed' ? 'selected' : '' }}>{{ __('Completed') }}</option>
                <option value="cancelled" {{ $proposal->status === 'cancelled' ? 'selected' : '' }}>{{ __('Cancelled') }}</option>
            </select>
            <x-input-error :messages="$errors->{'edit-'.$proposal->id}->get('status')" class="mt-2" />
        </div>

        <div class="mt-6 flex justify-end">
            <x-secondary-button x-on:click="$dispatch('close')">
                {{ __('Hủy') }}
            </x-secondary-button>

            <x-primary-button class="ml-3">
                {{ __('Cập nhật') }}
            </x-primary-button>
        </div>
    </form>
</x-modal>

<!-- Delete Research Topic Modal -->
<x-modal name="delete-proposal-{{ $proposal->id }}" focusable>
    <form method="POST" action="{{ route('proposals.destroy', $proposal) }}" class="p-6">
        @csrf
        @method('DELETE')

        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Bạn có chắc chắn muốn xóa đề tài này không?') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Sau khi xóa đề tài này, tất cả dữ liệu và tài nguyên liên quan sẽ bị xóa vĩnh viễn.') }}
        </p>

        <div class="mt-6 flex justify-end">
            <x-secondary-button x-on:click="$dispatch('close')">
                {{ __('Hủy') }}
            </x-secondary-button>

            <x-danger-button class="ml-3">
                {{ __('Xóa đề tài') }}
            </x-danger-button>
        </div>
    </form>
</x-modal>
@endforeach 