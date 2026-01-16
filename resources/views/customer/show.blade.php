{{-- Author: Leau Chee Way --}}
{{-- resources/views/customer/show.blade.php --}}
@extends('layouts.app')

@section('title', $book->title.' — Details')

@section('content')

  {{-- Inline styles for the quantity stepper + add-to-cart (dark theme-friendly) --}}
  <style>
    .price-badge{
      display:inline-flex;align-items:center;gap:8px;
      padding:8px 12px;border-radius:12px;border:1px solid #2a3263;background:#0f1533;
      font-weight:700;
    }
    .stock-pill--ok{ background:linear-gradient(180deg,#0d1d13,#11271a); border-color:#1d3f2a; }
    .stock-pill--no{ background:linear-gradient(180deg,#1a0e0e,#241012); border-color:#3e1d1d; }

    .qty-wrap{
      display:inline-flex; align-items:stretch; border-radius:12px; overflow:hidden;
      border:1px solid #2a3263; background:#0f1533;
    }
    .qty-btn{
      min-width:40px; padding:10px 12px; border:none; background:#11183a; color:#e5e7eb; cursor:pointer;
      transition:transform .12s ease, background .15s ease, border-color .15s ease;
    }
    .qty-btn:hover{ transform:translateY(-1px); background:#162154 }
    .qty-input{
      width:72px; text-align:center; padding:10px 8px; border-left:1px solid #1c2346; border-right:1px solid #1c2346;
      background:#0f1533; color:#e5e7eb; outline:none; border:none; font-weight:600;
    }
    .add-btn{
      display:inline-flex; align-items:center; gap:8px; padding:12px 16px; border-radius:12px; border:1px solid #2a3263;
      background:linear-gradient(135deg, #4f46e5, #06b6d4); color:#fff; cursor:pointer; transition:.15s; white-space:nowrap;
      box-shadow:var(--shadow);
    }
    .add-btn:hover{ transform:translateY(-1px) }
    .add-btn[disabled],
    .qty-wrap[aria-disabled="true"] .qty-btn{ opacity:.55; cursor:not-allowed; transform:none !important }
    .qty-wrap[aria-disabled="true"] .qty-input{ opacity:.7 }

    .hero-cover{
      background:#0f1533;border:1px solid #1c2346;border-radius:12px;padding:16px;min-height:360px;
      display:flex;align-items:center;justify-content:center;
    }
    .meta-label{ display:block;font-size:13px;color:#9aa4c2;margin-bottom:4px }
  </style>

  {{-- Top actions --}}
  <div class="row mb" style="justify-content:space-between;align-items:center">
    <h2 style="margin:0">📖 {{ $book->title }}</h2>
    <a href="{{ route('customer.index') }}" class="pill">← Back to List</a>
  </div>

  {{-- Flash (in addition to your global toasts) --}}
  @if(session('ok'))
    <div class="pill" style="margin-bottom:10px;border-color:#1d3f2a;background:linear-gradient(180deg,#0d1d13,#11271a)">✅ {{ session('ok') }}</div>
  @endif
  @if(session('err'))
    <div class="pill" style="margin-bottom:10px;border-color:#3e1d1d;background:linear-gradient(180deg,#1a0e0e,#241012)">⚠️ {{ session('err') }}</div>
  @endif

  <div class="card">
    <div class="grid grid-2" style="gap:16px">
      {{-- Left: Cover --}}
      <div class="hero-cover">
        @if($book->cover_image_url)
          <img src="{{ $book->cover_image_url }}" alt="Cover"
               style="max-height:340px;max-width:100%;object-fit:contain;border-radius:8px">
        @else
          <span class="muted">No Cover</span>
        @endif
      </div>

      {{-- Right: Info --}}
      @php
        $inStock = (int) ($book->stock ?? 0);
        $maxQty  = $inStock > 0 ? $inStock : 1;
      @endphp
      <div>

        <div class="row" style="gap:8px;flex-wrap:wrap">
          <span class="pill {{ $inStock>0 ? 'stock-pill--ok' : 'stock-pill--no' }}">
            {{ $inStock>0 ? 'In stock ('.$inStock.')' : 'Out of stock' }}
          </span>
          <span class="pill">ISBN: {{ $book->isbn }}</span>
          <span class="price-badge">RM {{ number_format($book->price,2) }}</span>
        </div>

        <div class="grid grid-2 mt" style="gap:12px">
          <div>
            <span class="meta-label">Author</span>
            <div style="font-weight:600">{{ $book->author }}</div>
          </div>
          <div>
            <span class="meta-label">Category</span>
            <div style="font-weight:600">{{ $book->categories->first()->name ?? '-' }}</div>
          </div>
          <div>
            <span class="meta-label">Genre</span>
            <div style="font-weight:600">{{ $book->genre ?? '-' }}</div>
          </div>
          <div>
            <span class="meta-label">Average Rating</span>
            <div style="font-weight:600">{{ $book->avg_rating ?? '-' }}</div>
          </div>
        </div>

        @if(!empty($book->description))
          <div class="mt">
            <span class="meta-label">Description</span>
            <p class="muted" style="margin:0;white-space:pre-line">{{ $book->description }}</p>
          </div>
        @endif

        {{-- Quantity + Add to cart --}}
        <div class="row mt" style="gap:12px;align-items:center;flex-wrap:wrap">
          <form method="POST" action="{{ route('cart.add') }}" class="row" style="gap:12px;align-items:center">
            @csrf
            <input type="hidden" name="book_id" value="{{ $book->id }}">

            <div class="qty-wrap" data-qty aria-disabled="{{ $inStock <= 0 ? 'true' : 'false' }}">
              <button type="button" class="qty-btn" aria-label="Decrease"
                      {{ $inStock <= 0 ? 'disabled' : '' }}
                      onclick="const i=this.parentElement.querySelector('input[type=number]'); i.stepDown(); i.dispatchEvent(new Event('input',{bubbles:true}));">
                −
              </button>

              <input type="number"
                     name="quantity"
                     value="1"
                     min="1"
                     max="{{ $maxQty }}"
                     class="qty-input"
                     {{ $inStock <= 0 ? 'disabled' : '' }}>

              <button type="button" class="qty-btn" aria-label="Increase"
                      {{ $inStock <= 0 ? 'disabled' : '' }}
                      onclick="const i=this.parentElement.querySelector('input[type=number]'); i.stepUp(); i.dispatchEvent(new Event('input',{bubbles:true}));">
                +
              </button>
            </div>

            <button type="submit" class="add-btn" {{ $inStock <= 0 ? 'disabled' : '' }}>
              <span>🛒</span><span>Add to Cart</span>
            </button>
          </form>

          <a href="{{ route('customer.index') }}" class="pill">← Back</a>
        </div>

        {{-- Small note if OOS --}}
        @if($inStock <= 0)
          <div class="muted" style="margin-top:8px">This title is currently unavailable.</div>
        @endif
      </div>
    </div>
  </div>

  {{-- Optional read-only reviews --}}
  @if($book->relationLoaded('reviews') && $book->reviews->count())
    <div class="card mt">
      <h3 style="margin-top:0">Reviews</h3>
      <div class="grid" style="gap:12px">
        @foreach($book->reviews as $r)
          <div class="card" style="padding:14px">
            <div class="row" style="justify-content:space-between;align-items:center">
              <strong>{{ $r->rating }}/5</strong>
              <span class="muted">{{ $r->created_at->format('d M Y') }}</span>
            </div>
            <div class="mt">{{ $r->content }}</div>
          </div>
        @endforeach
      </div>
    </div>
  @endif

  {{-- Clamp quantity + disable button when invalid --}}
  <script>
    (function(){
      const wrap = document.querySelector('[data-qty]');
      if(!wrap) return;
      const input = wrap.querySelector('input[type="number"]');
      const form  = wrap.closest('form');
      const btn   = form ? form.querySelector('.add-btn') : null;

      function clamp(){
        const min = Number(input.min || 1);
        const max = Number(input.max || 9999);
        let val   = Number(input.value || 1);
        if (isNaN(val)) val = min;
        if (val < min) val = min;
        if (val > max) val = max;
        input.value = val;

        const disabled = wrap.getAttribute('aria-disabled') === 'true' || val < min || val > max;
        if (btn) btn.disabled = disabled;
      }

      input?.addEventListener('input', clamp);
      clamp();
    })();
  </script>
@endsection
