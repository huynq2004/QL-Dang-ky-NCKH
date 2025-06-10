<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Thông tin cá nhân') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Xem và cập nhật thông tin tài khoản của bạn.') }}
        </p>
    </header>

    @if($canEdit)
        <form id="send-verification" method="post" action="{{ route('verification.send') }}">
            @csrf
        </form>

        <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
            @csrf
            @method('patch')

            <div>
                <x-input-label for="name" :value="__('Họ và tên')" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            <div>
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
                <x-input-error class="mt-2" :messages="$errors->get('email')" />

                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <div>
                        <p class="text-sm mt-2 text-gray-800">
                            {{ __('Email của bạn chưa được xác thực.') }}

                            <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                {{ __('Nhấn vào đây để gửi lại email xác thực.') }}
                            </button>
                        </p>

                        @if (session('status') === 'verification-link-sent')
                            <p class="mt-2 font-medium text-sm text-green-600">
                                {{ __('Một liên kết xác thực mới đã được gửi đến email của bạn.') }}
                            </p>
                        @endif
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-4">
                <x-primary-button>{{ __('Lưu thay đổi') }}</x-primary-button>

                @if (session('status') === 'profile-updated')
                    <p
                        x-data="{ show: true }"
                        x-show="show"
                        x-transition
                        x-init="setTimeout(() => show = false, 2000)"
                        class="text-sm text-gray-600"
                    >{{ __('Đã lưu.') }}</p>
                @endif
            </div>
        </form>
    @else
        <div class="mt-6 space-y-6">
            <div>
                <label class="block font-medium text-sm text-gray-700">{{ __('Họ và tên') }}</label>
                <p class="mt-1 p-2 bg-gray-50 rounded">{{ $user->name }}</p>
            </div>

            <div>
                <label class="block font-medium text-sm text-gray-700">{{ __('Email') }}</label>
                <p class="mt-1 p-2 bg-gray-50 rounded">{{ $user->email }}</p>
            </div>

            <div>
                <a href="{{ route('profile.edit') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    {{ __('Chỉnh sửa thông tin') }}
                </a>
            </div>
        </div>
    @endif
</section>
