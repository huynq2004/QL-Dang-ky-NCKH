@foreach($invitations ?? [] as $invitation)
<x-modal name="invitation-detail-{{ $invitation->id }}" focusable>
    <div class="p-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">
            {{ __('Lời mời hướng dẫn') }}
        </h2>

        <!-- Student Information -->
        <div class="mb-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-2">{{ __('Student Information') }}</h3>
            <div class="bg-gray-50 rounded p-4">
                <p class="mb-2"><span class="font-medium">{{ __('Họ và tên') }}:</span> {{ $invitation->student->user->name }}</p>
                <p class="mb-2"><span class="font-medium">{{ __('ID sinh viên') }}:</span> {{ $invitation->student->student_id }}</p>
                <p class="mb-2"><span class="font-medium">{{ __('Email') }}:</span> {{ $invitation->student->user->email }}</p>
                @if($invitation->student->phone)
                    <p class="mb-2"><span class="font-medium">{{ __('Điện thoại liên hệ') }}:</span> {{ $invitation->student->phone }}</p>
                @endif
            </div>
        </div>

        <!-- Research Topic -->
        <div class="mb-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-2">{{ __('Đề tài nghiên cứu') }}</h3>
            <div class="bg-gray-50 rounded p-4">
                <p class="mb-2"><span class="font-medium">{{ __('Tiêu đề') }}:</span> {{ $invitation->proposal->title }}</p>
                <p class="mb-2"><span class="font-medium">{{ __('Lĩnh vực') }}:</span> {{ $invitation->proposal->field }}</p>
                @if($invitation->proposal->description)
                    <p class="mb-2"><span class="font-medium">{{ __('Mô tả') }}:</span></p>
                    <p class="text-gray-600">{{ $invitation->proposal->description }}</p>
                @endif
            </div>
        </div>

        <!-- Request Information -->
        <div class="mb-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-2">{{ __('Lời mời hướng dẫn') }}</h3>
            <div class="bg-gray-50 rounded p-4">
                <p class="mb-2"><span class="font-medium">{{ __('Trạng thái') }}:</span> 
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                        {{ $invitation->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                           ($invitation->status === 'accepted' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800') }}">
                        {{ ucfirst($invitation->status) }}
                    </span>
                </p>
                <p class="mb-2"><span class="font-medium">{{ __('Gửi lúc') }}:</span> {{ $invitation->created_at->format('d/m/Y H:i') }}</p>
                @if($invitation->message)
                    <p class="mb-2"><span class="font-medium">{{ __('Lời nhắn từ sinh viên') }}:</span></p>
                    <p class="text-gray-600">{{ $invitation->message }}</p>
                @endif
            </div>
        </div>

        <!-- Actions -->
        @if($invitation->status === 'pending')
            <div class="flex items-center justify-end gap-4 mt-6">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Đóng') }}
                </x-secondary-button>

                <form action="{{ route('invitations.accept', $invitation) }}" method="POST" class="inline">
                    @csrf
                    <x-primary-button>
                        {{ __('Chấp nhận') }}
                    </x-primary-button>
                </form>

                <form action="{{ route('invitations.reject', $invitation) }}" method="POST" class="inline">
                    @csrf
                    <x-danger-button>
                        {{ __('Từ chối') }}
                    </x-danger-button>
                </form>
            </div>
        @else
            <div class="flex justify-end mt-6">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Đóng') }}
                </x-secondary-button>
            </div>
        @endif
    </div>
</x-modal>
@endforeach 