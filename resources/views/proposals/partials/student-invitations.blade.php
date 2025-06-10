@if(isset($invitations) && $invitations->count() > 0)
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Giảng viên') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Đề tài') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Trạng thái') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Gửi lúc') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Thao tác') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($invitations as $invitation)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $invitation->lecturer->user->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($invitation->proposal)
                                    <a href="{{ route('proposals.show', $invitation->proposal) }}" class="text-indigo-600 hover:text-indigo-900">
                                        {{ $invitation->proposal->title }}
                                    </a>
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
                                        <x-danger-button>
                                            {{ __('Thu hồi yêu cầu') }}
                                        </x-danger-button>
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
            {{ __('Bạn chưa gửi bất kỳ yêu cầu nào.') }}
        </div>
    </div>
@endif 