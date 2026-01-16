{{-- Author: Leau Chee Way --}}
{{-- resources/views/books/edit.blade.php --}}
@extends('layouts.app')

@section('title','Edit Book')

@section('content')
@php
  $selectedCatId = old('category_id', $book->categories->first()->id ?? '');
@endphp

<div class="grid" style="gap:16px">
  {{-- Page header --}}
  <div class="card">
    <div class="row" style="justify-content:space-between;align-items:center">
      <h2 style="margin:0">✏️ Edit Book</h2>
      <div class="row" style="gap:8px">
        <a href="{{ route('books.index') }}" class="pill">← Back to list</a>
        <a href="{{ route('books.show',$book) }}" class="pill">View</a>
      </div>
    </div>
  </div>

  {{-- Errors --}}
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

  {{-- Form --}}
  <div class="card">
    <form action="{{ route('books.update',$book) }}" method="POST" enctype="multipart/form-data"
          class="grid" style="gap:14px" id="editForm">
      @csrf @method('PUT')

      <div>
        <label>Title <span class="muted">(required)</span></label>
        <input name="title" value="{{ old('title',$book->title) }}" required class="input" placeholder="Book title">
      </div>

      <div class="grid grid-2">
        <div>
          <label>Author <span class="muted">(required)</span></label>
          <input name="author" value="{{ old('author',$book->author) }}" required class="input" placeholder="Author name">
        </div>
        <div>
          <label>ISBN <span class="muted">(required)</span></label>
          <input name="isbn" value="{{ old('isbn',$book->isbn) }}" required class="input" placeholder="ISBN">
        </div>
      </div>

      <div class="grid grid-3">
        <div>
          <label>Genre</label>
          <input name="genre" value="{{ old('genre',$book->genre) }}" class="input" placeholder="Genre">
        </div>
        <div>
          <label>Price (RM) <span class="muted">(required)</span></label>
          <input type="number" step="0.01" min="0" name="price" value="{{ old('price',$book->price) }}" required class="input" id="price">
        </div>
        <div>
          <label>Stock <span class="muted">(required)</span></label>
          <input type="number" min="0" name="stock" value="{{ old('stock',$book->stock) }}" required class="input">
        </div>
      </div>

      {{-- Category (single-select) --}}
      <div>
        <label>Category</label>
        <select name="category_id" class="input">
          <option value="">-- Select a Category --</option>
          @foreach($categories as $cat)
            <option value="{{ $cat->id }}" @selected($selectedCatId == $cat->id)>{{ $cat->name }}</option>
          @endforeach
        </select>
      </div>

      <div>
        <label>Description</label>
        <textarea name="description" rows="4" class="input" placeholder="Short summary, edition notes, etc.">{{ old('description',$book->description) }}</textarea>
      </div>

      {{-- Cover image: current + replace + optional remove --}}
      <div class="grid" style="gap:10px">
        <label>Cover Image</label>

        @if($book->cover_image_url)
          <div class="row" style="gap:10px;align-items:center">
            <img src="{{ $book->cover_image_url }}" alt="Current cover"
                 style="height:100px;border-radius:10px;border:1px solid #1c2346">
            <span class="muted">Current</span>
          </div>
        @else
          <span class="muted">No current cover</span>
        @endif

        <div class="row" style="gap:12px;align-items:center;flex-wrap:wrap">
          <input type="file" name="cover_image" accept=".jpg,.jpeg,.png,.webp" id="coverInput" class="input" style="padding:10px">
          <span class="muted">Max 3MB • JPG / PNG / WebP</span>
          <label class="row" style="gap:8px;align-items:center">
            <input type="checkbox" name="remove_cover" value="1">
            <span class="muted">Remove existing cover</span>
          </label>
        </div>

        <div id="coverPreviewWrap" class="mt" style="margin-top:10px;display:none">
          <div class="row" style="gap:10px;align-items:center">
            <img id="coverPreview" alt="New preview"
                 style="height:120px;border-radius:10px;border:1px solid #1c2346">
            <span id="coverInfo" class="muted"></span>
          </div>
        </div>
      </div>

      <div class="row mt" style="justify-content:flex-end;gap:8px">
        <a href="{{ url()->previous() }}" class="pill">Cancel</a>
        <button class="btn primary">Save Changes</button>
      </div>
    </form>
  </div>
</div>

{{-- Helpers: price format, image preview, unsaved guard --}}
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

  const form = document.getElementById('editForm');
  let dirty = false;
  form.querySelectorAll('input,textarea,select').forEach(el=>{
    el.addEventListener('change', ()=> dirty = true);
    el.addEventListener('input',  ()=> dirty = true);
  });
  window.addEventListener('beforeunload', (e)=>{
    if (!dirty) return;
    e.preventDefault(); e.returnValue = '';
  });
  form.addEventListener('submit', ()=> dirty = false);
</script>
@endsection
