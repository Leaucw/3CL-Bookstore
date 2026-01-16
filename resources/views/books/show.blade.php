{{-- Author: Leau Chee Way --}}
{{-- resources/views/books/show.blade.php --}}
@extends('layouts.app')

@section('title', $book->title)

@section('content')
@php
  $stock = (int) ($book->stock ?? 0);
  $lowThreshold = (int) request('low', 5);
  $isLow  = $stock > 0 && $stock < $lowThreshold;
  $isOut  = $stock <= 0;
  $avg    = (float) ($book->avg_rating ?? 0);
@endphp

<div class="grid" style="gap:16px">
  {{-- Book header card --}}
  <div class="card">
    <div class="row" style="justify-content:space-between;align-items:center;margin-bottom:12px">
      <div class="row" style="gap:10px;align-items:center">
        <h2 style="margin:0">📖 {{ $book->title }}</h2>
        @if($book->categories && $book->categories->count())
          <div class="row" style="gap:6px;flex-wrap:wrap">
            @foreach($book->categories->take(3) as $cat)
              <span class="pill">{{ $cat->name }}</span>
            @endforeach
            @if($book->categories->count() > 3)
              <span class="pill muted">+{{ $book->categories->count()-3 }}</span>
            @endif
          </div>
        @endif
      </div>

      <div class="row" style="gap:8px">
        <a href="{{ route('books.index') }}" class="pill">← Back</a>
        <a href="{{ route('books.edit',$book) }}" class="btn">Edit</a>
        <form action="{{ route('books.destroy',$book) }}" method="POST">
          @csrf @method('DELETE')
          <button class="btn danger" data-confirm="Delete this book?">Delete</button>
        </form>
      </div>
    </div>

    <div class="grid grid-2">
      {{-- Cover --}}
      <div class="card" style="padding:16px; display:flex; align-items:center; justify-content:center; min-height:240px">
        @if($book->cover_image_url)
          <img src="{{ $book->cover_image_url }}" alt="Cover"
               style="max-height:300px; max-width:100%; border-radius:12px; border:1px solid #1c2346">
        @else
          <div class="muted">No cover image</div>
        @endif
      </div>

      {{-- Facts --}}
      <div class="grid" style="gap:10px">
        <div class="row" style="gap:8px;align-items:center">
          <div class="pill muted">Author</div>
          <div>{{ $book->author }}</div>
        </div>
        <div class="row" style="gap:8px;align-items:center">
          <div class="pill muted">ISBN</div>
          <div>{{ $book->isbn }}</div>
        </div>
        <div class="row" style="gap:8px;align-items:center">
          <div class="pill muted">Genre</div>
          <div>{{ $book->genre ?? '-' }}</div>
        </div>
        <div class="row" style="gap:8px;align-items:center">
          <div class="pill muted">Price</div>
          <div><strong>RM {{ number_format($book->price,2) }}</strong></div>
        </div>

        {{-- Stock status --}}
        <div class="row" style="gap:8px;align-items:center">
          <div class="pill muted">Stock</div>
          <div>{{ $stock }}</div>
          @if($isOut)
            <span class="pill" style="border-color:#3e1d1d;background:linear-gradient(180deg,#1a0e0e,#241012);color:#fca5a5">Out of stock</span>
          @elseif($isLow)
            <span class="pill" style="border-color:#3e2c1d;background:linear-gradient(180deg,#2a1a0e,#2f1e12);color:#fcd39b">Low</span>
          @else
            <span class="pill" style="border-color:#1d3f2a;background:linear-gradient(180deg,#0d1d13,#11271a);color:#bdf7c4">In stock</span>
          @endif
        </div>

        {{-- Rating --}}
        <div class="row" style="gap:8px;align-items:center">
          <div class="pill muted">Rating</div>
          <div class="row" style="gap:6px;align-items:center">
            <div aria-label="Average rating" title="{{ number_format($avg,1) }}/5" style="letter-spacing:2px">
              @for($i=1;$i<=5;$i++)
                @php $filled = $avg >= $i - 0.5; @endphp
                <span style="color:#f59e0b">{{ $filled ? '★' : '☆' }}</span>
              @endfor
            </div>
            <span class="muted">{{ number_format($avg,1) }}/5</span>
          </div>
        </div>
      </div>
    </div>

    {{-- Description --}}
    <div class="mt" style="margin-top:18px">
      <h3 style="margin:0 0 6px">Description</h3>
      <p class="muted" style="white-space:pre-line;margin:0">
        {!! e($book->description ?: 'No description provided.') !!}
      </p>
    </div>
  </div>
  
  {{-- Reviews --}}
  <div class="card">
    <div class="row" style="justify-content:space-between;align-items:center;margin-bottom:10px">
      <h3 style="margin:0">Reviews</h3>
      <span class="pill muted">Total: {{ $book->reviews->count() }}</span>
    </div>

    {{-- Review submission form for authenticated customers --}}
    @auth('web')
      @if(auth()->user()->role === 'customer')
        <form action="{{ route('reviews.store', $book) }}" method="POST" class="grid" style="gap:8px;margin-bottom:16px">
          @csrf
          <div class="row" style="align-items:center;gap:6px">
            <label for="rating" class="pill muted">Rating</label>
            <select name="rating" id="rating" required>
              <option value="">Select</option>
              @for($i=1;$i<=5;$i++)
                <option value="{{ $i }}">{{ $i }} Star{{ $i>1?'s':'' }}</option>
              @endfor
            </select>
          </div>
          <div class="grid" style="gap:4px">
            <label for="content" class="pill muted">Comment (optional)</label>
            <textarea name="content" id="content" rows="3" placeholder="Write your review..." maxlength="1000" style="width:100%">{{ old('content') }}</textarea>
          </div>
          <button type="submit" class="btn">Submit Review</button>
        </form>
      @endif
    @endauth

    {{-- Display existing reviews --}}
    @forelse($book->reviews as $r)
      <div style="padding:12px 0;border-bottom:1px solid #202750">
        {{-- Case 1: Deleted by manager --}}
        @if($r->deleted_by_manager ?? false)
          @if(auth()->check() && auth()->id() === $r->user_id)
            <div class="muted">
              ❌ Your review was deleted by Manager.
              <form method="POST" action="{{ route('reviews.dismissDeleteMsg', $r->id) }}">
                @csrf
                <button type="submit" class="btn small">OK</button>
              </form>
            </div>
          @endif

        {{-- Case 2: Hidden --}}
        @elseif($r->hidden)
          @if(auth()->check() && auth()->id() === $r->user_id)
            <div class="muted">⚠ This review has been hidden by Manager. You may edit it:</div>
            {{-- reuse your edit form partial --}}
            @include('reviews._form', ['review' => $r, 'book' => $book])
          @endif

        {{-- Case 3 & 4: Visible --}}
        @else
          <div class="row" style="gap:8px;align-items:center;margin-bottom:6px">
            <strong>{{ $r->rating }}/5</strong>
            <div style="letter-spacing:2px;color:#f59e0b">
              @for($i=1;$i<=5;$i++)
                <span>{{ $r->rating >= $i ? '★' : '☆' }}</span>
              @endfor
            </div>
            @if(property_exists($r,'created_at') && $r->created_at)
              <span class="muted">• {{ $r->created_at->format('d M Y') }}</span>
            @endif
          </div>
          <div class="muted">{{ e($r->content) }}</div>

          {{-- Flag/unflag button (only for other customers) --}}
          @auth
            @if(auth()->user()->role === 'customer' && auth()->id() !== $r->user_id)
              @if(!$r->flagged)
                <form method="POST" action="{{ route('reviews.flag',$r) }}">
                  @csrf
                  <button class="btn small">🚩 Flag</button>
                </form>
              @else
                <form method="POST" action="{{ route('reviews.unflag',$r) }}">
                  @csrf
                  <button class="btn small muted">Unflag</button>
                </form>
              @endif
            @endif
          @endauth
        @endif
      </div>
    @empty
      <div class="muted">No reviews yet.</div>
    @endforelse
  </div>
  
  {{-- Manager actions for flagged/hidden reviews --}}
@auth
  @if(auth()->user()->role === 'manager' || auth()->user()->role === 'staff')
    <div class="card mt">
      <h3>Flagged / Hidden Reviews Management</h3>

      @forelse($book->reviews->where('flagged', true)->orWhere('hidden', true) as $r)
        <div style="padding:12px 0;border-bottom:1px solid #202750">
          <div class="row" style="gap:8px;align-items:center;margin-bottom:6px">
            <strong>{{ $r->rating }}/5</strong>
            <div style="letter-spacing:2px;color:#f59e0b">
              @for($i=1;$i<=5;$i++)
                <span>{{ $r->rating >= $i ? '★' : '☆' }}</span>
              @endfor
            </div>
            @if(property_exists($r,'created_at') && $r->created_at)
              <span class="muted">• {{ $r->created_at->format('d M Y') }}</span>
            @endif
          </div>

          <div class="muted">{{ e($r->content) }}</div>

          <div class="row" style="gap:6px;margin-top:6px">
            {{-- Hide / Unhide --}}
            @if($r->hidden)
              <form method="POST" action="{{ route('reviews.unhide', $r) }}">
    @csrf
    @method('PATCH')
    <button class="btn small">Unhide</button>
</form>

            @else
              <form method="POST" action="{{ route('reviews.hide', $r) }}">
    @csrf
    @method('PATCH')
    <button class="btn small danger">Hide</button>
</form>

            @endif

            {{-- Trust --}}
            @if(!$r->manager_trusted)
              <form method="POST" action="{{ route('reviews.managerTrust', $r) }}">
                @csrf
                <button class="btn small">Trust</button>
              </form>
            @endif

            {{-- Delete --}}
            <form method="POST" action="{{ route('reviews.managerDelete', $r) }}">
              @csrf
              @method('DELETE')
              <button class="btn small danger">Delete</button>
            </form>
          </div>
        </div>
      @empty
        <div class="muted">No flagged or hidden reviews.</div>
      @endforelse
    </div>
  @endif
@endauth
</div>
@endsection