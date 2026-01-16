{{-- Author: Chan Kah Wei --}}
@extends('layouts.app')
@section('title','Create Event • Staff')
@section('content')
<div class="wrap">
  <div class="card">
    <a href="{{ route('dashboard.staff') }}" class="pill">← Back to Staff Home</a>
    <h2>Create Event</h2>

    <form method="POST" action="{{ route('staff.events.store') }}" enctype="multipart/form-data" class="grid grid-2" style="gap:14px">
      @csrf

      <div><label>Title</label><input name="title" class="input" required></div>
      <div><label>Type</label><input name="type" class="input" required></div>
      <div><label>Delivery Mode</label><input name="delivery_mode" class="input" required></div>
      <div><label>Starts At</label><input type="datetime-local" name="starts_at" class="input" required></div>
      <div><label>Ends At</label><input type="datetime-local" name="ends_at" class="input"></div>
      <div><label>Visibility</label><input name="visibility" class="input" required></div>

      <div>
        <label>Status</label>
        <select name="status" class="input" required>
          <option value="draft">Draft</option>
          <option value="scheduled">Scheduled</option>
          <option value="sent">Sent</option>
          <option value="failed">Failed</option>
        </select>
      </div>

      <div><label>Points Reward</label><input type="number" name="points_reward" class="input" value="0"></div>

      <div style="grid-column:1/-1">
        <label>Description</label>
        <textarea name="description" class="input"></textarea>
      </div>

      {{-- Event image upload with preview --}}
      <div style="grid-column:1/-1">
        <label>Event Image</label>
        <div class="row" style="gap:12px;align-items:center;flex-wrap:wrap">
          <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp" id="eventImageInput" class="input" style="padding:10px">
          <span class="muted">Max 3MB • JPG / PNG / WebP</span>
        </div>
        <div id="eventImagePreviewWrap" class="mt" style="margin-top:10px;display:none">
          <div class="row" style="gap:10px;align-items:center">
            <img id="eventImagePreview" alt="Preview" style="height:120px;border-radius:10px;border:1px solid #1c2346">
            <span id="eventImageInfo" class="muted"></span>
          </div>
        </div>
      </div>

      <div style="grid-column:1/-1">
        <button class="btn primary">Save & Schedule</button>
      </div>
    </form>
  </div>
</div>

{{-- Preview script --}}
<script>
  const input = document.getElementById('eventImageInput');
  const wrap  = document.getElementById('eventImagePreviewWrap');
  const img   = document.getElementById('eventImagePreview');
  const info  = document.getElementById('eventImageInfo');

  input?.addEventListener('change', () => {
    const f = input.files && input.files[0];
    if (!f) { wrap.style.display = 'none'; return; }
    if (f.size > 3 * 1024 * 1024) {
      alert('Event image must be ≤ 3MB.');
      input.value = ''; wrap.style.display = 'none'; return;
    }
    const url = URL.createObjectURL(f);
    img.src = url;
    info.textContent = `${f.name} — ${(f.size/1024/1024).toFixed(2)} MB`;
    wrap.style.display = 'block';
  });
</script>
@endsection
