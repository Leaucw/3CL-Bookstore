{{-- Author: Leau Chee Way --}}
{{-- resources/views/books/index.blade.php --}}
@extends('layouts.app')

@section('title','Books')

@section('content')
<div class="card">
  {{-- Header row --}}
  <div class="row mb" style="justify-content:space-between; align-items:center;">
    <div class="row" style="gap:10px; align-items:center;">
      <h2 style="margin:0;">📚 Books</h2>
      <span class="pill muted">Total: {{ number_format($books->total()) }}</span>
    </div>
    <div class="row" style="gap:8px;">
      {{-- Back button --}}
      <a href="{{ route('dashboard.staff') }}" class="pill">← Back</a>
      <a href="{{ route('books.create') }}" class="btn primary">➕ Add Book</a>
    </div>
  </div>

  {{-- Controls --}}
  @php($threshold = (int) request('low', 5))
  <form method="GET" class="row mb" style="gap:10px; align-items:center;">
    {{-- Keep existing query params except low & page --}}
    @foreach(request()->except(['low','page']) as $k => $v)
      <input type="hidden" name="{{ $k }}" value="{{ $v }}">
    @endforeach

    <label class="muted">Low threshold</label>
    <input type="number" min="0" name="low" value="{{ $threshold }}" class="input" style="width:100px">
    <button class="btn">Apply</button>

    <div class="right muted">
      Rows with <span class="pill" style="border-color:transparent;background:linear-gradient(180deg,#1a0e0e,#241012);color:#fca5a5;">low stock</span>
      mean stock &lt; {{ $threshold }}.
    </div>
  </form>

  {{-- Table --}}
  @if($books->count())
    <div class="grid">
      <table class="table">
        <thead>
          <tr>
            <th style="width:80px">Cover</th>
            <th>Title</th>
            <th>Author</th>
            <th>ISBN</th>
            <th>Price</th>
            <th>Stock</th>
            <th class="right" style="width:260px">Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach($books as $book)
            @php($isLow = (int)($book->stock ?? 0) < $threshold)
            <tr style="{{ $isLow ? 'background:linear-gradient(180deg,#1a0e0e,#241012);' : '' }}">
              <td>
                @if($book->cover_image_url ?? false)
                  <img src="{{ $book->cover_image_url }}" alt="Cover" style="height:60px;border-radius:8px;border:1px solid #1c2346;">
                @else
                  <div class="muted" style="font-size:12px">No cover</div>
                @endif
              </td>
              <td>{{ $book->title }}</td>
              <td class="muted">{{ $book->author }}</td>
              <td class="muted">{{ $book->isbn }}</td>
              <td>RM {{ number_format($book->price,2) }}</td>
              <td>
                {{ (int)($book->stock ?? 0) }}
                @if($isLow)
                  <span class="pill" style="border-color:#3e1d1d;background:linear-gradient(180deg,#1a0e0e,#241012);color:#fca5a5;margin-left:8px">Low</span>
                @endif
              </td>
              <td class="right">
                <div class="row" style="gap:8px;justify-content:flex-end;flex-wrap:nowrap">
                  <a href="{{ route('books.show',$book) }}" class="pill">View</a>
                  <a href="{{ route('books.edit',$book) }}" class="btn">Edit</a>
                  <form action="{{ route('books.destroy',$book) }}" method="POST" onsubmit="return confirm('Delete this book?')">
                    @csrf @method('DELETE')
                    <button class="btn danger">Delete</button>
                  </form>
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>

      {{-- Pagination keeps filters --}}
      <div class="mt">
        {{ $books->withQueryString()->links() }}
      </div>
    </div>
  @else
    {{-- Empty state --}}
    <div class="card center" style="background:linear-gradient(180deg,#0f1630,#0b1128);">
      <div style="font-size:48px;line-height:1.1">📭</div>
      <h3 style="margin:8px 0 6px">No books found</h3>
      <p class="muted mb">Try adjusting your search or add a new book.</p>
      <a href="{{ route('books.create') }}" class="btn primary">Add your first book</a>
    </div>
  @endif
</div>

{{-- LocalStorage helper to persist threshold --}}
<script>
(function () {
  const KEY = 'books.lowThreshold';
  const url = new URL(window.location.href);
  const qp = url.searchParams;

  if (!qp.has('low')) {
    const saved = localStorage.getItem(KEY);
    if (saved !== null) {
      qp.set('low', saved);
      window.location.replace(url.toString());
      return;
    }
  }

  const input = document.querySelector('input[name="low"]');
  if (input) {
    input.addEventListener('change', () => {
      localStorage.setItem(KEY, input.value || '5');
    });
  }
})();
</script>
@endsection
