@if(session('success') || session('error') || session('status'))
<div id="toast-notification" 
  style="
    position: fixed;
    top: 1rem;
    right: 1rem;
    z-index: 9999;
    padding: 0.75rem 1rem;
    border-radius: 0.375rem;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    max-width: 400px;
    pointer-events: auto;
    @if(session('success'))
      background-color: #059669;
      color: white;
    @elseif(session('error'))
      background-color: #dc2626;
      color: white;
    @else
      background-color: #1f2937;
      color: white;
    @endif
    animation: slideInRight 0.3s ease-out;
  "
>
  <div style="display: flex; align-items: center; justify-content: space-between;">
    <span style="flex: 1; font-weight: 500;">
      @if(session('success'))
        {{ session('success') }}
      @elseif(session('error'))
        {{ session('error') }}
      @elseif(session('status'))
        {{ session('status') }}
      @endif
    </span>
    <button onclick="hideToast()" style="
      margin-left: 1rem;
      color: white;
      background: none;
      border: none;
      cursor: pointer;
      padding: 0.25rem;
      border-radius: 0.25rem;
    " onmouseover="this.style.backgroundColor='rgba(255,255,255,0.1)'" onmouseout="this.style.backgroundColor='transparent'">
      <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
      </svg>
    </button>
  </div>
</div>

<style>
  @keyframes slideInRight {
    from {
      transform: translateX(100%);
      opacity: 0;
    }
    to {
      transform: translateX(0);
      opacity: 1;
    }
  }
  
  @keyframes slideOutRight {
    from {
      transform: translateX(0);
      opacity: 1;
    }
    to {
      transform: translateX(100%);
      opacity: 0;
    }
  }
</style>

<script>
  function hideToast() {
    const toast = document.getElementById('toast-notification');
    if (toast) {
      toast.style.animation = 'slideOutRight 0.3s ease-in-out';
      setTimeout(function() {
        toast.remove();
      }, 300);
    }
  }
  
  // Auto hide sau 5 giây
  setTimeout(function() {
    hideToast();
  }, 5000);
</script>
@endif
