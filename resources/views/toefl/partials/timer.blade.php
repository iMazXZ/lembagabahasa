{{-- resources/views/toefl/partials/timer.blade.php --}}
<div id="timer-container" class="fixed top-4 right-4 z-50">
  <div class="bg-white rounded-xl shadow-lg p-4 border-2 border-gray-200">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
      </div>
      <div>
        <div class="text-xs text-gray-500 uppercase tracking-wide">Sisa Waktu</div>
        <div id="timer-display" class="text-2xl font-bold text-gray-900 font-mono">--:--</div>
      </div>
    </div>
  </div>
</div>

<script>
(function() {
  let remainingSeconds = {{ $remainingSeconds ?? 0 }};
  const timerDisplay = document.getElementById('timer-display');
  const timerContainer = document.getElementById('timer-container');
  const pingUrl = '{{ route("toefl.ping", $attempt) }}';
  const forceSubmitUrl = '{{ route("toefl.force-submit", $attempt) }}';
  const csrfToken = '{{ csrf_token() }}';

  function formatTime(seconds) {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
  }

  function updateDisplay() {
    timerDisplay.textContent = formatTime(remainingSeconds);
    
    if (remainingSeconds <= 60) {
      timerContainer.classList.add('animate-pulse');
      timerDisplay.classList.remove('text-gray-900');
      timerDisplay.classList.add('text-red-600');
    }
  }

  function tick() {
    if (remainingSeconds <= 0) {
      forceSubmit();
      return;
    }
    remainingSeconds--;
    updateDisplay();
  }

  async function ping() {
    try {
      const response = await fetch(pingUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
        },
      });
      const data = await response.json();
      
      if (data.expired) {
        window.location.href = data.redirect;
        return;
      }
      
      // Sync timer with server
      remainingSeconds = data.remaining;
      updateDisplay();
    } catch (e) {
      console.error('Ping failed:', e);
    }
  }

  async function forceSubmit() {
    try {
      const form = document.createElement('form');
      form.method = 'POST';
      form.action = forceSubmitUrl;
      
      const csrf = document.createElement('input');
      csrf.type = 'hidden';
      csrf.name = '_token';
      csrf.value = csrfToken;
      form.appendChild(csrf);
      
      document.body.appendChild(form);
      form.submit();
    } catch (e) {
      console.error('Force submit failed:', e);
    }
  }

  // Initialize
  updateDisplay();
  
  // Tick every second
  setInterval(tick, 1000);
  
  // Ping server every 20 seconds
  setInterval(ping, 20000);
})();
</script>
