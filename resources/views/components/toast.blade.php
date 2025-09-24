<div
  x-data="{ show: true }"
  x-init="setTimeout(() => show = false, 5000)"
  x-show="show"
  x-transition
  class="fixed inset-0 flex items-start justify-center pointer-events-none"
  style="z-index: 9999;"
>
  <div class="mt-6 px-4 py-3 rounded-md shadow-lg pointer-events-auto"
       :class="{
         'bg-green-600 text-white': {{ session('success') ? 'true' : 'false' }},
         'bg-red-600 text-white': {{ session('error') ? 'true' : 'false' }},
         'bg-gray-800 text-white': {{ (!session('success') && !session('error')) ? 'true' : 'false' }}
       }"
  >
    @if(session('success'))
      <span>{{ session('success') }}</span>
    @elseif(session('error'))
      <span>{{ session('error') }}</span>
    @elseif(session('status'))
      <span>{{ session('status') }}</span>
    @endif
  </div>
</div>
