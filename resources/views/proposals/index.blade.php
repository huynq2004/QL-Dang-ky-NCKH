<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Tab Navigation -->
            <div class="mb-4 border-b border-gray-200">
                <ul class="flex flex-wrap -mb-px text-sm font-medium text-center">
                    @if(Auth::user()->role === 'admin')
                        <li class="me-2">
                            <a href="{{ route('dashboard') }}" 
                               class="inline-block p-4 border-b-2 rounded-t-lg {{ $activeTab === 'available' ? 'border-indigo-600 text-indigo-600' : 'border-transparent' }}">
                                {{ __('ĐỀ TÀI NGHIÊN CỨU') }}
                            </a>
                        </li>
                        <li class="me-2">
                            <a href="{{ route('users.index') }}"
                               class="inline-block p-4 border-b-2 rounded-t-lg {{ $activeTab === 'users' ? 'border-indigo-600 text-indigo-600' : 'border-transparent' }}">
                                {{ __('QUẢN LÝ NGƯỜI DÙNG') }}
                            </a>
                        </li>
                    @else
                        <li class="me-2">
                            <a href="{{ route('dashboard') }}" 
                               class="inline-block p-4 border-b-2 rounded-t-lg {{ $activeTab === 'available' ? 'border-indigo-600 text-indigo-600' : 'border-transparent' }}">
                                {{ __('ĐỀ TÀI NGHIÊN CỨU') }}
                            </a>
                        </li>
                        <li class="me-2">
                            <a href="{{ route('my-topics') }}"
                               class="inline-block p-4 border-b-2 rounded-t-lg {{ $activeTab === 'my-topics' ? 'border-indigo-600 text-indigo-600' : 'border-transparent' }}">
                                {{ __('ĐANG THAM GIA') }}
                            </a>
                        </li>
                        @if(Auth::user()->role === 'student')
                        <li class="me-2">
                            <a href="{{ route('find-supervisor') }}"
                               class="inline-block p-4 border-b-2 rounded-t-lg {{ $activeTab === 'lecturers' ? 'border-indigo-600 text-indigo-600' : 'border-transparent' }}">
                                {{ __('TÌM GIẢNG VIÊN') }}
                            </a>
                        </li>
                        @endif
                        <li class="me-2">
                            <a href="{{ route('my-invitations') }}"
                               class="inline-block p-4 border-b-2 rounded-t-lg {{ $activeTab === 'invitations' ? 'border-indigo-600 text-indigo-600' : 'border-transparent' }}">
                                {{ Auth::user()->role === 'lecturer' ? __('LỜI MỜI HƯỚNG DẪN') : __('YÊU CẦU THAM GIA') }}
                            </a>
                        </li>
                    @endif
                </ul>
            </div>

            <!-- Tab Contents -->
            <div>
                <!-- Available Proposals Tab -->
                <div id="available" class="tab-content {{ $activeTab !== 'available' ? 'hidden' : '' }}">
                    @if(Auth::user()->role === 'lecturer')
                    <div class="mb-4 text-end">
                        <x-primary-button type="button" x-data="" x-on:click.prevent="$dispatch('open-modal', 'new-lecturer-proposal')">
                            {{ __('Tạo đề tài nghiên cứu') }}
                        </x-primary-button>
                    </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @forelse($proposals ?? [] as $proposal)
                            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                                <div class="p-6">
                                    <h3 class="text-xl font-semibold mb-2">{{ $proposal->title }}</h3>
                                    <p class="text-gray-600 mb-4">{{ $proposal->field }}</p>
                                    
                                    <div class="mb-4">
                                        <p class="mb-1"><span class="font-semibold">{{ __('Giảng viên') }}:</span> {{ $proposal->lecturer->user->name }}</p>
                                        <p><span class="font-semibold">{{ __('Khoa/Bộ môn') }}:</span> {{ $proposal->lecturer->department }}</p>
                                    </div>

                                    @if($proposal->description)
                                        <p class="text-gray-600 mb-6">{{ $proposal->description }}</p>
                                    @endif

                                    <div class="flex items-center gap-4">
                                        <a href="{{ route('proposals.show', $proposal) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-gray-800 rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                            {{ __('Xem chi tiết') }}
                                        </a>
                                        @if(Auth::user()->role === 'student' && $proposal->status === 'active')
                                            @php
                                                $existingRequest = App\Models\Invitation::where([
                                                    'student_id' => Auth::user()->student->id,
                                                    'proposal_id' => $proposal->id
                                                ])->first();
                                            @endphp

                                            @if(!$existingRequest)
                                                <form action="{{ route('proposals.request', $proposal) }}" method="POST" class="inline">
                                                    @csrf
                                                    <x-secondary-button type="submit">
                                                        {{ __('Yêu cầu tham gia') }}
                                                    </x-secondary-button>
                                                </form>
                                            @elseif($existingRequest)
                                                <span class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-200 rounded-md font-semibold text-xs text-gray-700 uppercase">
                                                    {{ ucfirst($existingRequest->status) }}
                                                </span>
                                                @if($existingRequest->status === 'pending')
                                                    <form action="{{ route('proposals.withdraw-request', $existingRequest) }}" method="POST" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <x-danger-button>
                                                            {{ __('Thu hồi yêu cầu') }}
                                                        </x-danger-button>
                                                    </form>
                                                @endif
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-2">
                                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                                    <div class="p-6 text-gray-500">
                                        {{ __('Không tìm thấy đề tài nghiên cứu.') }}
                                    </div>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- My Research Topics Tab -->
                <div id="my-topics" class="tab-content {{ $activeTab !== 'my-topics' ? 'hidden' : '' }}">
                    @if(Auth::user()->role === 'lecturer')
                    <div class="mb-4 text-end">
                        <x-primary-button type="button" x-data="" x-on:click.prevent="$dispatch('open-modal', 'new-lecturer-proposal')">
                            {{ __('Tạo đề tài nghiên cứu') }}
                        </x-primary-button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @forelse($lecturerProposals ?? [] as $proposal)
                            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                                <div class="p-6">
                                    <h3 class="text-xl font-semibold mb-2">{{ $proposal->title }}</h3>
                                    <p class="text-gray-600 mb-4">{{ $proposal->field }}</p>
                                    
                                    <div class="mb-4">
                                        <p class="mb-1"><span class="font-semibold">{{ __('Status') }}:</span> {{ ucfirst($proposal->status) }}</p>
                                        @if($proposal->description)
                                            <p class="text-gray-600 mt-4">{{ $proposal->description }}</p>
                                        @endif
                                    </div>

                                    <div class="flex items-center gap-4">
                                        <a href="{{ route('proposals.show', $proposal) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-gray-800 rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                            {{ __('Xem chi tiết') }}
                                        </a>
                                        <x-secondary-button type="button" x-data="" x-on:click.prevent="$dispatch('open-modal', 'edit-proposal-{{ $proposal->id }}')">
                                            {{ __('Sửa') }}
                                        </x-secondary-button>
                                        @if($proposal->invitations->isEmpty())
                                            <x-danger-button type="button" x-data="" x-on:click.prevent="$dispatch('open-modal', 'delete-proposal-{{ $proposal->id }}')">
                                                {{ __('Xóa') }}
                                            </x-danger-button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-2">
                                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                                    <div class="p-6 text-gray-500">
                                        {{ __('Bạn chưa tạo bất kỳ đề tài nghiên cứu nào.') }}
                                    </div>
                                </div>
                            </div>
                        @endforelse
                    </div>
                    @else
                        <div class="mb-4 text-end">
                            <x-primary-button type="button" x-data="" x-on:click.prevent="$dispatch('open-modal', 'new-student-proposal')">
                                {{ __('Tạo đề tài nghiên cứu') }}
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
                                                {{ __('Xem chi tiết') }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-span-2">
                                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                                        <div class="p-6 text-gray-500">
                                            {{ __('Bạn chưa tạo bất kỳ đề tài nghiên cứu nào.') }}
                                        </div>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    @endif
                </div>

                @if(Auth::user()->role === 'student')
                <!-- Find Supervisor Tab -->
                <div id="lecturers" class="tab-content {{ $activeTab !== 'lecturers' ? 'hidden' : '' }}">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @forelse($lecturers ?? [] as $lecturer)
                            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                                <div class="p-6">
                                    <h3 class="text-lg font-medium mb-2">{{ $lecturer->user->name }}</h3>
                                    <div class="mb-4 text-sm">
                                        <p><strong>{{ __('Khoa/Bộ môn') }}:</strong> {{ $lecturer->department }}</p>
                                        <p><strong>{{ __('Chức vụ') }}:</strong> {{ $lecturer->title }}</p>
                                        <p><strong>{{ __('Chuyên ngành') }}:</strong> {{ $lecturer->specialization }}</p>
                                    </div>

                                    <div class="flex space-x-4">
                                        <x-primary-button type="button" x-data="" x-on:click.prevent="$dispatch('open-modal', 'invite-lecturer-{{ $lecturer->id }}')">
                                            {{ __('Yêu cầu hướng dẫn') }}
                                        </x-primary-button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-2">
                                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                                    <div class="p-6 text-gray-500">
                                        {{ __('Không có giảng viên nào.') }}
                                    </div>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
                @endif

                <!-- Invitations Tab -->
                <div id="invitations" class="tab-content {{ $activeTab !== 'invitations' ? 'hidden' : '' }}">
                    @if(Auth::user()->role === 'lecturer')
                        @include('proposals.partials.lecturer-invitations')
                    @else
                        @include('proposals.partials.student-invitations')
                    @endif
                </div>
            </div>

            <!-- Modals -->
            @if(Auth::user()->role === 'student')
                @include('proposals.partials.student-modals')
            @else
                @include('proposals.partials.lecturer-modals')
            @endif
        </div>
    </div>
</x-app-layout>