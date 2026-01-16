{{-- Author: Leau Chee Way --}}
{{-- resources/views/books/create.blade.php --}}
@extends('layouts.app')

@section('title','Add Book')

@section('content')
<div class="grid" style="gap:16px">
  {{-- Page header card --}}
  <div class="card">
    <div class="row" style="justify-content:space-between;align-items:center">
      <h2 style="margin:0">➕ Add Book</h2>
      <a href="{{ route('books.index') }}" class="pill">← Back to list</a>
    </div>
  </div>

  {{-- Validation errors (inline, themed) --}}
  @if ($errors->any())
    <div class="card" style="border-color:#3e1d1d;background:linear-gradient(180deg,#1a0e0e,#241012)">
      <div class="row" style="gap:8px;align-items:center;margin-bottom:6px">
        <strong>Error</strong>
        <span class="muted">Please fix the following and try again</span>
      </div>
      <ul style="margin:0;padding-left:18px">
        @foreach ($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- Form card --}}
  <div class="card">
    <form action="{{ route('books.store') }}" method="POST" enctype="multipart/form-data" class="grid" style="gap:14px">
      @csrf

      <div>
        <label>Title <span class="muted">(required)</span></label>
        <input name="title" value="{{ old('title') }}" required class="input" placeholder="e.g., Clean Architecture">
      </div>

      <div class="grid grid-2">
        <div>
          <label>Author <span class="muted">(required)</span></label>
          <input name="author" value="{{ old('author') }}" required class="input" placeholder="e.g., Robert C. Martin">
        </div>
        <div>
          <label>ISBN <span class="muted">(required)</span></label>
          <input name="isbn" value="{{ old('isbn') }}" required class="input" placeholder="e.g., 9780134494166">
        </div>
      </div>

      <div class="grid grid-3">
        <div>
          <label>Genre</label>
          <input name="genre" value="{{ old('genre') }}" class="input" placeholder="e.g., Software / Engineering">
        </div>
        <div>
          <label>Price (RM) <span class="muted">(required)</span></label>
          <input type="number" step="0.01" min="0" name="price" value="{{ old('price', 0) }}" required class="input" id="price">
        </div>
        <div>
          <label>Stock <span class="muted">(required)</span></label>
          <input type="number" min="0" name="stock" value="{{ old('stock', 0) }}" required class="input">
        </div>
      </div>

      {{-- Category (single-select) --}}
      <div>
        <label>Category</label>
        <select name="category_id" class="input">
          <option value="">-- Select a Category --</option>
          @foreach($categories as $cat)
            <option value="{{ $cat->id }}" @selected(old('category_id') == $cat->id)>{{ $cat->name }}</option>
          @endforeach
        </select>
      </div>

      <div>
        <label>Description</label>
        <textarea name="description" rows="4" class="input" placeholder="Short summary, edition notes, etc.">{{ old('description') }}</textarea>
      </div>

      {{-- Cover upload with preview --}}
      <div>
        <label>Cover Image</label>
        <div class="row" style="gap:12px;align-items:center;flex-wrap:wrap">
          <input type="file" name="cover_image" accept=".jpg,.jpeg,.png,.webp" id="coverInput" class="input" style="padding:10px">
          <span class="muted">Max 3MB • JPG / PNG / WebP</span>
        </div>
        <div id="coverPreviewWrap" class="mt" style="margin-top:10px;display:none">
          <div class="row" style="gap:10px;align-items:center">
            <img id="coverPreview" alt="Preview" style="height:120px;border-radius:10px;border:1px solid #1c2346">
            <span id="coverInfo" class="muted"></span>
          </div>
        </div>
      </div>

      <div class="row mt" style="justify-content:flex-end;gap:8px">
        <a href="{{ route('books.index') }}" class="pill">Cancel</a>
        <button class="btn primary">Save Book</button>
      </div>
    </form>
  </div>
</div>

{{-- Small helpers: price formatting & image preview --}}
<script>
  document.getElementById('price')?.addEventListener('blur', e => {
    const v = parseFloat(e.target.value);
    if (!isNaN(v)) e.target.value = v.toFixed(2);
  });

  const input = document.getElementById('coverInput');
  const wrap  = document.getElementById('coverPreviewWrap');
  const img   = document.getElementById('coverPreview');
  const info  = document.getElementById('coverInfo');

  input?.addEventListener('change', () => {
    const f = input.files && input.files[0];
    if (!f) { wrap.style.display = 'none'; return; }
    if (f.size > 3 * 1024 * 1024) {
      alert('Cover image must be ≤ 3MB.');
      input.value = ''; wrap.style.display = 'none'; return;
    }
    const url = URL.createObjectURL(f);
    img.src = url;
    info.textContent = `${f.name} — ${(f.size/1024/1024).toFixed(2)} MB`;
    wrap.style.display = 'block';
  });
</script>
@endsection
