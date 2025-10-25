{{-- resources/views/bl/quiz_fib.blade.php --}}
@extends('layouts.front')
@section('title', 'Basic Listening - Fill in Blank')

@push('styles')
<style>
  :root{ --line:#e5e7eb; --muted:#64748b; --brand:#6366f1; --ok:#10b981; --warn:#f59e0b; }
  .wrap{max-width:960px;margin:1.5rem auto;background:#fff;border:1px solid var(--line);border-radius:20px;box-shadow:0 10px 30px rgba(2,6,23,.06);padding:1.25rem}
  .hdr{display:flex;justify-content:space-between;gap:.75rem;align-items:center;margin-bottom:.5rem}
  .ttl{font-weight:800;font-size:1.25rem}
  .sub{color:#334155}
  .progress{height:8px;background:#eef2ff;border-radius:999px;overflow:hidden;margin:.25rem 0 1rem}
  .progress>span{display:block;height:100%;width:0;background:linear-gradient(90deg,#6366f1,#22d3ee)}
  .timer{display:flex;align-items:center;gap:.5rem;background:#f1f5f9;border:1px solid var(--line);padding:.4rem .7rem;border-radius:10px;font-weight:700}
  .timer.warn{background:#fff7ed;border-color:#fed7aa;color:#9a3412;animation:pulse 1.15s infinite}
  @keyframes pulse{0%,100%{transform:scale(1)}50%{transform:scale(1.02)}}
  .note{background:#f8fafc;border:1px dashed var(--line);border-radius:14px;padding:12px;margin:.75rem 0}
  .para{line-height:1.9;color:#0f172a;font-size:1.05rem;border:1px solid var(--line);border-radius:16px;padding:14px 16px;white-space:pre-line}
  .fib-input{
    text-align:left;
    padding:.12rem .40rem;
    min-width:0;
    width:auto;
    line-height:1.1;
    margin:0 .08rem;
  }
  .fib-input:focus{outline:none;border-color:#60a5fa;box-shadow:0 0 0 3px rgba(37,99,235,.15)}
  .fib-input.filled{background:#f0f9ff;border-color:#38bdf8}
  .chips{display:flex;gap:.5rem;flex-wrap:wrap;margin:.6rem 0}
  .chip{background:#eef2ff;color:#3730a3;border:1px solid #e0e7ff;border-radius:999px;padding:.3rem .65rem;font-weight:700;font-size:.85rem}
  .btns{display:flex;flex-wrap:wrap;gap:.6rem;margin-top:.8rem}
  .btn{appearance:none;border:0;border-radius:12px;padding:.75rem 1.05rem;font-weight:700}
  .btn.save{background:linear-gradient(135deg,#3b82f6,#06b6d4);color:#fff}
  .btn.submit{background:linear-gradient(135deg,#059669,#10b981);color:#fff}
  .btn.gray{background:#f3f4f6;color:#111827;border:1px solid #e5e7eb}
  .btn:disabled{opacity:.65;cursor:not-allowed}
  /* modal */
  .backdrop{position:fixed;inset:0;background:rgba(2,6,23,.45);display:none;align-items:center;justify-content:center;padding:1rem;z-index:50}
  .modal{width:100%;max-width:520px;background:#fff;border-radius:18px;border:1px solid #e5e7eb;box-shadow:0 30px 60px rgba(2,6,23,.25);overflow:hidden}
  .mhd{padding:1rem 1.2rem;border-bottom:1px solid #f1f5f9;font-weight:800}
  .mbd{padding:1rem 1.2rem}
  .mft{display:flex;justify-content:flex-end;gap:.5rem;padding:1rem 1.2rem;background:#f9fafb;border-top:1px solid #f1f5f9}
  .small{font-size:.9rem;color:#475569}
  @media (max-width:640px){ .wrap{padding:1rem} .para{font-size:1rem} .fib-input{min-width:64px}}
</style>
@endpush

@section('content')
<div class="wrap">

  {{-- progress --}}
  @if(($remainingSeconds ?? 0) > 0)
    <div class="progress" aria-hidden="true"><span id="bar"></span></div>
  @endif

  <div class="hdr">
    <div>
      <div class="ttl">Fill in the Blank</div>
      <div class="sub">
        @if($attempt->session?->title)
          <strong>{{ $attempt->session->title }}</strong> •
        @endif
        Soal {{ ($currentIndex ?? 0) + 1 }} dari {{ $totalQuestions ?? 1 }}
      </div>
    </div>
    <div class="timer" id="timerBox">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M12 8v5l3 3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
      <span id="t">--:--</span>
    </div>
  </div>

  {{-- audio (opsional) --}}
  @if($question->audio_url)
    <div class="mb-3">
      <audio controls class="w-full">
        <source src="{{ \Illuminate\Support\Facades\Storage::url($question->audio_url) }}" type="audio/mpeg">
      </audio>
      <div class="small">Dengarkan audio lalu isi bagian yang kosong.</div>
    </div>
  @endif

  <div class="note small">
    <div><strong>Petunjuk:</strong></div>
    <ul style="margin-left:1rem; margin-top:.25rem">
      <li>Isi kotak kosong sesuai konteks kalimat.</li>
      <li><strong>Simpan Sementara</strong> menyimpan tanpa mengumpulkan.</li>
      <li><strong>Kumpulkan Jawaban</strong> menyelesaikan & menuju halaman hasil.</li>
    </ul>
  </div>

  {{-- form: default = save --}}
  <form id="f" method="POST" action="{{ route('bl.quiz.fib.answer', $attempt) }}">
    @csrf
    <input type="hidden" name="question_id" value="{{ $question->id }}">

    {{-- ===== Paragraf dengan blank ===== --}}
    <div class="para" id="para">
      @php
        $html = '';
        if (!empty($processedParagraph) && str_contains($processedParagraph, '<input')) {
            $html = $processedParagraph;
        } else {
            $src = $question->paragraph_text ?? $question->paragraph ?? '';
            $src = nl2br($src);
            $i = 0;
            $html = preg_replace_callback('/\[\[(\d+)\]\]|\[blank\]/', function($m) use (&$i){
                $idx = $i++;
                $name = "answers[$idx]";
                $ph   = '...';
                return '<input type="text" class="fib-input" name="'.$name.'" value="" placeholder="'.$ph.'">';
            }, $src) ?? $src;
        }
        echo $html;
      @endphp
    </div>

    <div class="chips">
      <div class="chip">Kosong: <span id="empty">0</span></div>
      <div class="chip">Terisi: <span id="filled">0</span></div>
    </div>

    <div class="btns">
      <button type="submit" class="btn save" id="saveBtn">Simpan Sementara</button>
      <button type="button" class="btn submit" id="finalBtn">Kumpulkan Jawaban</button>
      @php
        $backUrl = url()->previous();
        if (!$backUrl || $backUrl === url()->current()) {
            $backUrl = route('bl.index');
        }
      @endphp
      <a href="{{ $backUrl }}" class="btn gray">Kembali</a>
    </div>
  </form>

  {{-- modal konfirmasi --}}
  <div class="backdrop" id="md">
    <div class="modal" role="dialog" aria-modal="true">
      <div class="mhd">Kumpulkan Jawaban?</div>
      <div class="mbd">
        <p class="small">Setelah dikumpulkan, kamu akan diarahkan ke halaman riwayat.</p>
        <p class="small">Terisi: <strong id="mf">0</strong> • Kosong: <strong id="me" style="color:#9a3412">0</strong></p>
      </div>
      <div class="mft">
        <button type="button" class="btn gray" id="mc">Batal</button>
        <button type="button" class="btn submit" id="my">Ya, Kumpulkan</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
  // ======= DOM refs
  const qid = {{ (int) $question->id }};
  const att = {{ (int) $attempt->id }};
  const key = `BL_FIB_ATTEMPT_${att}_Q${qid}`;

  const form  = document.getElementById('f');
  const bar   = document.getElementById('bar');
  const tbox  = document.getElementById('timerBox');
  const ttxt  = document.getElementById('t');
  const md    = document.getElementById('md');
  const mc    = document.getElementById('mc');
  const my    = document.getElementById('my');
  const finalBtn = document.getElementById('finalBtn');
  const saveBtn  = document.getElementById('saveBtn');
  const emptyEl  = document.getElementById('empty');
  const filledEl = document.getElementById('filled');

  function inputs(){ return Array.from(document.querySelectorAll('.fib-input')); }

  // ======= Restore from localStorage
  try{
    const raw = localStorage.getItem(key);
    if(raw){
      const obj = JSON.parse(raw);
      inputs().forEach((el, i)=>{
        if(obj && Object.prototype.hasOwnProperty.call(obj, i)) el.value = obj[i];
        if (el.value && el.value.trim().length>0) el.classList.add('filled');
      });
    }
  }catch(e){ /* ignore */ }

  // ======= Recount helper
  function recount(){
    const vals = inputs().map(el => (el.value||'').trim());
    const filled = vals.filter(v=>v.length>0).length;
    const empty  = vals.length - filled;
    if (emptyEl)  emptyEl.textContent  = empty;
    if (filledEl) filledEl.textContent = filled;
    return {filled, empty};
  }
  recount();

  // ======= Local autosave
  document.addEventListener('input', function(e){
    if(!e.target.classList || !e.target.classList.contains('fib-input')) return;
    if ((e.target.value||'').trim().length>0) e.target.classList.add('filled'); else e.target.classList.remove('filled');
    const obj = {}; inputs().forEach((el,i)=>obj[i]=el.value||'');
    try{ localStorage.setItem(key, JSON.stringify(obj)); }catch(_) {}
    recount();
  });

  // ======= Server autosave (minimal, aman)
  let lastAutoSaveAt = 0;
  function autoSave(){
    if(!form) return;
    const now = Date.now();
    if (now - lastAutoSaveAt < 1000) return; // throttle
    lastAutoSaveAt = now;

    try {
      const saveUrl = "{{ route('bl.quiz.fib.answer', $attempt) }}";
      const fd = new FormData(form);
      // Non-blocking; gunakan keepalive agar tetap terkirim saat unload
      fetch(saveUrl, {
        method: 'POST',
        body: fd,
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        keepalive: true,
      }).catch(()=>{});
    } catch(e) { /* ignore */ }
  }

  // ======= Timer + progress (dipertahankan seperti versi awal)
  const remaining = {{ (int)($remainingSeconds ?? 0) }};
  const total     = {{ (int)($totalSeconds ?? ($remainingSeconds ?? 0)) }};
  let secs = remaining>0 ? remaining : 0;

  function fmt(n){
    const m=Math.floor(n/60), s=n%60;
    return (m<10?'0':'')+m+':' + (s<10?'0':'')+s;
  }

  let didAutoSaveBeforeTimeout = false;
  function tick(){
    if(ttxt) ttxt.textContent = fmt(secs);
    if(bar && total>0){
      const used = total - secs;
      const pct = Math.max(0, Math.min(100, (used/total)*100));
      bar.style.width = pct + '%';
    }
    if(secs <= 30 && tbox) tbox.classList.add('warn');

    // simpan sekali saat <= 5 detik
    if (secs <= 5 && !didAutoSaveBeforeTimeout){
      didAutoSaveBeforeTimeout = true;
      autoSave();
    }

    if(secs <= 0){
      // beri micro-delay agar autosave terkirim
      setTimeout(finalize, 120);
      return;
    }
    secs--;
    setTimeout(tick, 1000);
  }
  if(remaining>0){ tick(); } else if(ttxt){ ttxt.textContent='--:--'; }

  // ======= Submit modes
  if(form){
    form.addEventListener('submit', function(){ disable(true); });
  }

  if(finalBtn){
    finalBtn.addEventListener('click', function(e){
      e.preventDefault();
      const mF = document.getElementById('mf');
      const mE = document.getElementById('me');
      const {filled, empty} = recount();
      if(mF) mF.textContent = filled;
      if(mE) mE.textContent = empty;
      if(md) md.style.display = 'flex';
    });
  }

  if(mc){ mc.addEventListener('click', function(){ md.style.display='none'; }); }
  if(md){
    md.addEventListener('click', function(e){
      if(e.target === md) md.style.display='none';
    });
  }

  if(my){
    my.addEventListener('click', function(){
      md.style.display='none';
      autoSave();
      setTimeout(finalize, 120);
    });
  }

  function finalize(){
    if(!form || form.dataset.locked==='1') return;
    form.dataset.locked='1';
    disable(true);
    form.setAttribute('action', "{{ route('bl.submit', $attempt->quiz_id) }}");
    form.submit();
  }

  function disable(v){
    if(saveBtn) saveBtn.disabled = v;
    if(finalBtn) finalBtn.disabled = v;
    inputs().forEach(function(i){ i.readOnly = v; });
  }

  // bersihkan localStorage setelah submit (indikasi: form dikunci)
  window.addEventListener('beforeunload', function(){
    if(form && form.dataset.locked === '1'){
      try{ localStorage.removeItem(key); }catch(_){}
    }
  });
})();

// ======= Auto-fit lebar input
(function fitFibWidths(){
  const PADDING_EXTRA = 12;
  function fit(el){
    const temp = document.createElement('span');
    const cs = window.getComputedStyle(el);
    temp.style.visibility = 'hidden';
    temp.style.whiteSpace = 'pre';
    temp.style.font = cs.font;
    temp.style.letterSpacing = cs.letterSpacing;
    temp.textContent = (el.value && el.value.length ? el.value : (el.placeholder || ''));
    document.body.appendChild(temp);
    const w = Math.ceil(temp.getBoundingClientRect().width) + PADDING_EXTRA;
    document.body.removeChild(temp);
    el.style.width = Math.max(36, w) + 'px';
  }

  const fields = Array.prototype.slice.call(document.querySelectorAll('.fib-input'));
  fields.forEach(function(el){
    fit(el);
    el.addEventListener('input', function(){ fit(el); });
  });
})();
</script>
@endpush
