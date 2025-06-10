<x-app-layout>
    <!-- Tab Navigation -->
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-4">
        <div class="mb-4 border-b border-gray-200">
            <ul class="flex flex-wrap -mb-px text-sm font-medium text-center">
                <li class="me-2">
                    <a href="{{ route('dashboard') }}" 
                       class="inline-block p-4 border-b-2 rounded-t-lg {{ !request()->routeIs('users.*') ? 'border-indigo-600 text-indigo-600' : 'border-transparent' }}">
                        {{ __('DANH SÁCH ĐỀ TÀI') }}
                    </a>
                </li>
                <li class="me-2">
                    <a href="{{ route('users.index') }}"
                       class="inline-block p-4 border-b-2 rounded-t-lg {{ request()->routeIs('users.*') ? 'border-indigo-600 text-indigo-600' : 'border-transparent' }}">
                        {{ __('QUẢN LÝ NGƯỜI DÙNG') }}
                    </a>
                </li>
            </ul>
        </div>
    </div>

    {{ $slot }}
</x-app-layout> 