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
                        <a href="{{ route('admin.users.index') }}"
                           class="inline-block p-4 border-b-2 rounded-t-lg border-indigo-600 text-indigo-600">
                            {{ __('QUẢN LÝ NGƯỜI DÙNG') }}
                        </a>
                    </li>
                </ul>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if(session('success') || session('error') || session('status'))
                        @include('components.toast')
                    @endif

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
        <form method="POST" action="{{ route('admin.users.store') }}" class="p-6" 
            x-data="{ 
                role: '{{ old('role', 'student') }}',
                name: '',
                email: '',
                password: '',
                password_confirmation: '',
                student_id: '',
                lecturer_id: '',
                errors: {
                    name: '',
                    email: '',
                    password: '',
                    password_confirmation: '',
                    student_id: '',
                    lecturer_id: ''
                },
                validateForm() {
                    let isValid = true;
                    this.errors = {
                        name: '',
                        email: '',
                        password: '',
                        password_confirmation: '',
                        student_id: '',
                        lecturer_id: ''
                    };

                    if (!this.name) {
                        this.errors.name = 'Vui lòng nhập họ và tên';
                        isValid = false;
                    }

                    if (!this.email) {
                        this.errors.email = 'Vui lòng nhập email';
                        isValid = false;
                    } else if (!this.email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
                        this.errors.email = 'Email không hợp lệ';
                        isValid = false;
                    }

                    if (!this.password) {
                        this.errors.password = 'Vui lòng nhập mật khẩu';
                        isValid = false;
                    } else if (this.password.length < 8) {
                        this.errors.password = 'Mật khẩu phải có ít nhất 8 ký tự';
                        isValid = false;
                    }

                    if (!this.password_confirmation) {
                        this.errors.password_confirmation = 'Vui lòng xác nhận mật khẩu';
                        isValid = false;
                    } else if (this.password !== this.password_confirmation) {
                        this.errors.password_confirmation = 'Mật khẩu xác nhận không khớp';
                        isValid = false;
                    }

                    if (this.role === 'student' && !this.student_id) {
                        this.errors.student_id = 'Vui lòng nhập mã sinh viên';
                        isValid = false;
                    }

                    if (this.role === 'lecturer' && !this.lecturer_id) {
                        this.errors.lecturer_id = 'Vui lòng nhập mã giảng viên';
                        isValid = false;
                    }

                    return isValid;
                }
            }"
            @submit.prevent="if (validateForm()) $el.submit();"
        >
            @csrf

            <h2 class="text-lg font-medium text-gray-900">
                {{ __('Thêm người dùng mới') }}
            </h2>

            <div class="mt-6">
                <x-input-label for="name" :value="__('Họ và tên')" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" x-model="name" />
                <p class="mt-2 text-sm text-red-600" x-text="errors.name" x-show="errors.name"></p>
            </div>

            <div class="mt-6">
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" x-model="email" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                <p class="mt-2 text-sm text-red-600" x-text="errors.email" x-show="errors.email"></p>
            </div>

            <div class="mt-6">
                <x-input-label for="role" :value="__('Vai trò')" />
                <select id="role" name="role" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" x-model="role">
                    <option value="student">Sinh viên</option>
                    <option value="lecturer">Giảng viên</option>
                    <option value="admin">Quản trị viên</option>
                </select>
            </div>

            <div class="mt-6" x-show="role === 'student'">
                <x-input-label for="student_id" :value="__('Mã sinh viên')" />
                <x-text-input id="student_id" name="student_id" type="text" class="mt-1 block w-full" x-model="student_id" />
                <x-input-error :messages="$errors->get('student_id')" class="mt-2" />
                <p class="mt-2 text-sm text-red-600" x-text="errors.student_id" x-show="errors.student_id"></p>
            </div>

            <div class="mt-6" x-show="role === 'lecturer'">
                <x-input-label for="lecturer_id" :value="__('Mã giảng viên')" />
                <x-text-input id="lecturer_id" name="lecturer_id" type="text" class="mt-1 block w-full" x-model="lecturer_id" />
                <x-input-error :messages="$errors->get('lecturer_id')" class="mt-2" />
                <p class="mt-2 text-sm text-red-600" x-text="errors.lecturer_id" x-show="errors.lecturer_id"></p>
            </div>

            <div class="mt-6">
                <x-input-label for="password" :value="__('Mật khẩu')" />
                <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" x-model="password" />
                <p class="mt-2 text-sm text-red-600" x-text="errors.password" x-show="errors.password"></p>
            </div>

            <div class="mt-6">
                <x-input-label for="password_confirmation" :value="__('Xác nhận mật khẩu')" />
                <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" x-model="password_confirmation" />
                <p class="mt-2 text-sm text-red-600" x-text="errors.password_confirmation" x-show="errors.password_confirmation"></p>
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
            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="p-6" x-data="{ role: '{{ $user->role }}' }">
                @csrf
                @method('PUT')

                <h2 class="text-lg font-medium text-gray-900">
                    {{ __('Chỉnh sửa thông tin người dùng') }}
                </h2>

                <div class="mt-6">
                    <x-input-label for="name-{{ $user->id }}" :value="__('Họ và tên')" />
                    <x-text-input id="name-{{ $user->id }}" name="name" type="text" class="mt-1 block w-full" :value="$user->name" required />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div class="mt-6">
                    <x-input-label for="email-{{ $user->id }}" :value="__('Email')" />
                    <x-text-input id="email-{{ $user->id }}" name="email" type="email" class="mt-1 block w-full" :value="$user->email" required />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div class="mt-6">
                    <x-input-label for="role-{{ $user->id }}" :value="__('Vai trò')" />
                    <select id="role-{{ $user->id }}" name="role" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" x-model="role">
                        <option value="student" {{ $user->role === 'student' ? 'selected' : '' }}>Sinh viên</option>
                        <option value="lecturer" {{ $user->role === 'lecturer' ? 'selected' : '' }}>Giảng viên</option>
                        <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                    <x-input-error :messages="$errors->get('role')" class="mt-2" />
                </div>

                <!-- Student ID field - shown for current students or when changing to student -->
                <div class="mt-6" x-show="role === 'student'">
                    <x-input-label for="student_id-{{ $user->id }}" :value="__('Mã sinh viên')" />
                    <x-text-input 
                        id="student_id-{{ $user->id }}" 
                        name="student_id" 
                        type="text" 
                        class="mt-1 block w-full" 
                        :value="$user->student->student_id ?? ''"
                        x-bind:required="role === 'student'"
                    />
                    <x-input-error :messages="$errors->get('student_id')" class="mt-2" />
                </div>

                <!-- Lecturer ID field - shown for current lecturers or when changing to lecturer -->
                <div class="mt-6" x-show="role === 'lecturer'">
                    <x-input-label for="lecturer_id-{{ $user->id }}" :value="__('Mã giảng viên')" />
                    <x-text-input 
                        id="lecturer_id-{{ $user->id }}" 
                        name="lecturer_id" 
                        type="text" 
                        class="mt-1 block w-full" 
                        :value="$user->lecturer->lecturer_id ?? ''"
                        x-bind:required="role === 'lecturer'"
                    />
                    <x-input-error :messages="$errors->get('lecturer_id')" class="mt-2" />
                </div>

                <div class="mt-6">
                    <x-input-label for="password-{{ $user->id }}" :value="__('Mật khẩu mới (để trống nếu không đổi)')" />
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
            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="p-6">
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