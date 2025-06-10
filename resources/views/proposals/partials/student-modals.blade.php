<x-modal name="new-student-proposal" :show="$errors->newProposal->isNotEmpty()" focusable>
    <form method="POST" action="{{ route('proposals.store') }}" class="p-6">
        @csrf

        <h2 class="text-lg font-medium text-gray-900 mb-4">
            {{ __('Tạo đề tài nghiên cứu') }}
        </h2>

        <!-- Research Information -->
        <div class="space-y-4">
            <div>
                <x-input-label for="title" :value="__('Tiêu đề đề tài nghiên cứu')" />
                <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" required autofocus />
                <x-input-error :messages="$errors->newProposal->get('title')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="field" :value="__('Lĩnh vực nghiên cứu')" />
                <x-text-input id="field" name="field" type="text" class="mt-1 block w-full" required />
                <x-input-error :messages="$errors->newProposal->get('field')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="description" :value="__('Mô tả')" />
                <x-textarea id="description" name="description" class="mt-1 block w-full" rows="4"></x-textarea>
                <x-input-error :messages="$errors->newProposal->get('description')" class="mt-2" />
            </div>
        </div>

        <!-- Supervisor Selection -->
        <div class="mt-6">
            <h3 class="text-sm font-medium text-gray-700 mb-2">{{ __('Thông tin giảng viên') }}</h3>
            <div class="space-y-4">
                <div>
                    <x-input-label for="lecturer_id" :value="__('Chọn giảng viên')" />
                    <select id="lecturer_id" name="lecturer_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                        <option value="">{{ __('Chọn giảng viên') }}</option>
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
                    <x-input-label for="message" :value="__('Lời nhắn tới giảng viên (Tùy chọn)')" />
                    <x-textarea id="message" name="message" class="mt-1 block w-full" rows="3" 
                        placeholder="{{ __('Giới thiệu bản thân và giải thích tại sao bạn quan tâm đến đề tài này...') }}">
                    </x-textarea>
                    <x-input-error :messages="$errors->newProposal->get('message')" class="mt-2" />
                </div>
            </div>
        </div>

        <!-- Information Note -->
        <div class="mt-6 p-4 bg-blue-50 rounded-md">
            <p class="text-sm text-blue-600">
                {{ __('Lưu ý: Đề tài nghiên cứu của bạn sẽ được lưu dưới dạng nháp và yêu cầu sẽ được gửi đến giảng viên đã chọn. Đề tài sẽ trở thành hoạt động khi giảng viên chấp nhận yêu cầu của bạn.') }}
            </p>
        </div>

        <div class="mt-6 flex justify-end gap-4">
            <x-secondary-button x-on:click="$dispatch('close')">
                {{ __('Hủy') }}
            </x-secondary-button>

            <x-primary-button>
                {{ __('Tạo và gửi yêu cầu') }}
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
                {{ __('Yêu cầu hướng dẫn từ') }} {{ $lecturer->user->name }}
            </h2>

            <div class="mt-6">
                <x-input-label for="proposal_id" :value="__('Chọn đề tài nghiên cứu')" />
                <select id="proposal_id" name="proposal_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                    <option value="">{{ __('Chọn đề tài') }}</option>
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
                    {{ __('Hủy') }}
                </x-secondary-button>

                <x-primary-button class="ms-3">
                    {{ __('Gửi yêu cầu') }}
                </x-primary-button>
            </div>
        </form>
    </x-modal>
@endforeach 