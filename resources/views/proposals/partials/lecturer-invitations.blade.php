@if(isset($invitations) && $invitations->count() > 0)
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Sinh viên') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Đề tài') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Trạng thái') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Gửi lúc') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Thao tác') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($invitations as $invitation)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $invitation->student->user->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($invitation->proposal)
                                    <a href="{{ route('proposals.show', $invitation->proposal) }}" class="text-indigo-600 hover:text-indigo-900">
                                        {{ $invitation->proposal->title }}
                                    </a>
                                @else
                                    <em>{{ __('Lĩnh vực nghiên cứu') }}</em>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $cls = match($invitation->status) {
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'accepted' => 'bg-green-100 text-green-800',
                                        'rejected' => 'bg-red-100 text-red-800',
                                        'expired', 'withdrawn' => 'bg-gray-100 text-gray-800',
                                        default => 'bg-gray-100 text-gray-800'
                                    };
                                @endphp
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $cls }}">
                                    {{ ucfirst($invitation->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $invitation->created_at->format('Y-m-d H:i') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <x-secondary-button type="button" x-data="" x-on:click.prevent="$dispatch('open-modal', 'invitation-detail-{{ $invitation->id }}')">
                                        {{ __('Xem chi tiết') }}
                                    </x-secondary-button>

                                    @if($invitation->status === 'pending')
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
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Include Invitation Detail Modals -->
    @include('proposals.partials.invitation-detail-modal')
@else
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-500">
            {{ __('No student requests received yet.') }}
        </div>
    </div>
@endif 