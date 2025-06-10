<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Tab Navigation -->
            <div class="mb-4 border-b border-gray-200">
                <ul class="flex flex-wrap -mb-px text-sm font-medium text-center">
                    <li class="me-2">
                        <a href="{{ route('dashboard') }}" 
                           class="inline-block p-4 border-b-2 rounded-t-lg border-transparent">
                            {{ __('DANH SÁCH ĐỀ TÀI') }}
                        </a>
                    </li>
                    <li class="me-2">
                        <a href="{{ route('users.index') }}"
                           class="inline-block p-4 border-b-2 rounded-t-lg border-indigo-600 text-indigo-600">
                            {{ __('QUẢN LÝ NGƯỜI DÙNG') }}
                        </a>
                    </li>
                </ul>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-semibold">{{ __('Quản lý người dùng') }}</h2>
                        <x-primary-button type="button" x-data="" x-on:click.prevent="$dispatch('open-modal', 'create-user')">
                            {{ __('Thêm người dùng') }}
                        </x-primary-button>
                    </div>

                    <!-- Users Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Họ và tên</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vai trò</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng thái</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($users as $user)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $user->email }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-800' : 
                                                   ($user->role === 'lecturer' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800') }}">
                                                {{ ucfirst($user->role) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                @if($user->role === 'student' && $user->student)
                                                    {{ $user->student->student_id }}
                                                @elseif($user->role === 'lecturer' && $user->lecturer)
                                                    {{ $user->lecturer->lecturer_id }}
                                                @elseif($user->role === 'admin')
                                                    {{ $user->id }}
                                                @else
                                                    -
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $user->email_verified_at ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $user->email_verified_at ? 'Đã xác thực' : 'Chưa xác thực' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <x-secondary-button type="button" x-data="" x-on:click.prevent="$dispatch('open-modal', 'edit-user-{{ $user->id }}')">
                                                {{ __('Chỉnh sửa') }}
                                            </x-secondary-button>
                                            @if($user->id !== Auth::id())
                                                <x-danger-button type="button" x-data="" x-on:click.prevent="$dispatch('open-modal', 'delete-user-{{ $user->id }}')">
                                                    {{ __('Xóa') }}
                                                </x-danger-button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($users->hasPages())
                        <div class="mt-4">
                            {{ $users->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Create User Modal -->
    <x-modal name="create-user" focusable>
        <form method="POST" action="{{ route('users.store') }}" class="p-6" x-data="{ role: '{{ old('role', 'student') }}' }">
            @csrf

            <h2 class="text-lg font-medium text-gray-900">
                {{ __('Thêm người dùng mới') }}
            </h2>

            @if ($errors->any())
                <div class="mb-4">
                    <div class="font-medium text-red-600">
                        {{ __('Đã xảy ra lỗi!') }}
                    </div>

                    <ul class="mt-3 list-disc list-inside text-sm text-red-600">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mt-6">
                <x-input-label for="name" :value="__('Họ và tên')" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" required :value="old('name')" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div class="mt-6">
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" required :value="old('email')" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="mt-6">
                <x-input-label for="role" :value="__('Vai trò')" />
                <select id="role" name="role" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" x-model="role">
                    <option value="student" {{ old('role') === 'student' ? 'selected' : '' }}>Sinh viên</option>
                    <option value="lecturer" {{ old('role') === 'lecturer' ? 'selected' : '' }}>Giảng viên</option>
                    <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Quản trị viên</option>
                </select>
                <x-input-error :messages="$errors->get('role')" class="mt-2" />
            </div>

            <div class="mt-6" x-show="role === 'student'">
                <x-input-label for="student_id" :value="__('Mã sinh viên')" />
                <x-text-input id="student_id" name="student_id" type="text" class="mt-1 block w-full" :value="old('student_id')" x-bind:required="role === 'student'" />
                <x-input-error :messages="$errors->get('student_id')" class="mt-2" />
            </div>

            <div class="mt-6" x-show="role === 'lecturer'">
                <x-input-label for="lecturer_id" :value="__('Mã giảng viên')" />
                <x-text-input id="lecturer_id" name="lecturer_id" type="text" class="mt-1 block w-full" :value="old('lecturer_id')" x-bind:required="role === 'lecturer'" />
                <x-input-error :messages="$errors->get('lecturer_id')" class="mt-2" />
            </div>

            <div class="mt-6">
                <x-input-label for="password" :value="__('Mật khẩu')" />
                <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" required />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="mt-6">
                <x-input-label for="password_confirmation" :value="__('Xác nhận mật khẩu')" />
                <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" required />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button type="button" x-on:click="$dispatch('close')">
                    {{ __('Hủy') }}
                </x-secondary-button>

                <x-primary-button class="ml-3">
                    {{ __('Tạo') }}
                </x-primary-button>
            </div>
        </form>
    </x-modal>

    <!-- Edit User Modals -->
    @foreach($users as $user)
        <x-modal name="edit-user-{{ $user->id }}" focusable>
            <form method="POST" action="{{ route('users.update', $user) }}" class="p-6" x-data="{ role: '{{ $user->role }}' }">
                @csrf
                @method('PUT')

                <h2 class="text-lg font-medium text-gray-900">
                    {{ __('Chỉnh sửa thông tin người dùng') }}
                </h2>

                <div class="mt-6">
                    <x-input-label for="name-{{ $user->id }}" :value="__('Name')" />
                    <x-text-input id="name-{{ $user->id }}" name="name" type="text" class="mt-1 block w-full" :value="$user->name" required />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div class="mt-6">
                    <x-input-label for="email-{{ $user->id }}" :value="__('Email')" />
                    <x-text-input id="email-{{ $user->id }}" name="email" type="email" class="mt-1 block w-full" :value="$user->email" required />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div class="mt-6">
                    <x-input-label for="role-{{ $user->id }}" :value="__('Role')" />
                    <select id="role-{{ $user->id }}" name="role" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" x-model="role">
                        <option value="student" {{ $user->role === 'student' ? 'selected' : '' }}>Sinh viên</option>
                        <option value="lecturer" {{ $user->role === 'lecturer' ? 'selected' : '' }}>Giảng viên</option>
                        <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                    <x-input-error :messages="$errors->get('role')" class="mt-2" />
                </div>

                @if($user->role === 'student' && $user->student)
                    <div class="mt-6">
                        <x-input-label for="student_id-{{ $user->id }}" :value="__('ID')" />
                        <x-text-input id="student_id-{{ $user->id }}" type="text" class="mt-1 block w-full bg-gray-100" :value="$user->student->student_id" disabled />
                    </div>
                @elseif($user->role === 'lecturer' && $user->lecturer)
                    <div class="mt-6">
                        <x-input-label for="lecturer_id-{{ $user->id }}" :value="__('ID')" />
                        <x-text-input id="lecturer_id-{{ $user->id }}" type="text" class="mt-1 block w-full bg-gray-100" :value="$user->lecturer->lecturer_id" disabled />
                    </div>
                @elseif($user->role === 'admin')
                    <div class="mt-6">
                        <x-input-label for="user_id-{{ $user->id }}" :value="__('ID')" />
                        <x-text-input id="user_id-{{ $user->id }}" type="text" class="mt-1 block w-full bg-gray-100" :value="$user->id" disabled />
                    </div>
                @endif

                <div class="mt-6" x-show="role === 'student' && '{{ $user->role }}' !== 'student'">
                    <x-input-label for="student_id-new-{{ $user->id }}" :value="__('ID')" />
                    <x-text-input id="student_id-new-{{ $user->id }}" name="student_id" type="text" class="mt-1 block w-full" required x-bind:required="role === 'student'" />
                    <x-input-error :messages="$errors->get('student_id')" class="mt-2" />
                </div>

                <div class="mt-6" x-show="role === 'lecturer' && '{{ $user->role }}' !== 'lecturer'">
                    <x-input-label for="lecturer_id-new-{{ $user->id }}" :value="__('ID')" />
                    <x-text-input id="lecturer_id-new-{{ $user->id }}" name="lecturer_id" type="text" class="mt-1 block w-full" required x-bind:required="role === 'lecturer'" />
                    <x-input-error :messages="$errors->get('lecturer_id')" class="mt-2" />
                </div>

                <div class="mt-6">
                    <x-input-label for="password-{{ $user->id }}" :value="__('New Password (leave blank to keep current)')" />
                    <x-text-input id="password-{{ $user->id }}" name="password" type="password" class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div class="mt-6 flex justify-end">
                    <x-secondary-button x-on:click="$dispatch('close')">
                        {{ __('Hủy') }}
                    </x-secondary-button>

                    <x-primary-button class="ml-3">
                        {{ __('Lưu') }}
                    </x-primary-button>
                </div>
            </form>
        </x-modal>

        <!-- Delete User Modal -->
        <x-modal name="delete-user-{{ $user->id }}" focusable>
            <form method="POST" action="{{ route('users.destroy', $user) }}" class="p-6">
                @csrf
                @method('DELETE')

                <h2 class="text-lg font-medium text-gray-900">
                    {{ __('Xóa người dùng') }}
                </h2>

                <p class="mt-1 text-sm text-gray-600">
                    {{ __('Bạn có chắc chắn muốn xóa người dùng này không? Hành động này không thể hoàn tác.') }}
                </p>

                <div class="mt-6 flex justify-end">
                    <x-secondary-button x-on:click="$dispatch('close')">
                        {{ __('Hủy') }}
                    </x-secondary-button>

                    <x-danger-button class="ml-3">
                        {{ __('Xóa') }}
                    </x-danger-button>
                </div>
            </form>
        </x-modal>
    @endforeach
</x-app-layout> 