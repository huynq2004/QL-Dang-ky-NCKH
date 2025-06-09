@if(isset($invitations) && $invitations->count() > 0)
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Student') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Proposal') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Status') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Received At') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Actions') }}</th>
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
                                    <div class="flex items-center gap-2">
                                        <form action="{{ route('invitations.accept', $invitation) }}" method="POST" class="inline">
                                            @csrf
                                            <x-primary-button>
                                                {{ __('Accept') }}
                                            </x-primary-button>
                                        </form>
                                        <form action="{{ route('invitations.reject', $invitation) }}" method="POST" class="inline">
                                            @csrf
                                            <x-danger-button>
                                                {{ __('Reject') }}
                                            </x-danger-button>
                                        </form>
                                    </div>
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
            {{ __('No student requests received yet.') }}
        </div>
    </div>
@endif 