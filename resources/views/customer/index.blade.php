{{-- Author: Leau Chee Way --}}
{{-- resources/views/customer/index.blade.php --}}
@extends('layouts.app')

@section('title','Browse Books')

@section('content')

  <style>
    .qty-wrap{
      display:inline-flex; align-items:stretch; border-radius:10px; overflow:hidden;
      border:1px solid #2a3263; background:#0f1533;
    }
    .qty-btn{
      min-width:36px; padding:8px 10px; border:none; background:#11183a; color:#e5e7eb; cursor:pointer;
      transition:transform .12s ease, background .15s ease, border-color .15s ease;
    }
    .qty-btn:hover{ transform:translateY(-1px); background:#162154 }
    .qty-input{
      width:64px; text-align:center; padding:8px 6px; border-left:1px solid #1c2346; border-right:1px solid #1c2346;
      background:#0f1533; color:#e5e7eb; outline:none; border:none;
    }
    .add-btn{
      display:inline-flex; align-items:center; gap:8px; padding:10px 14px; border-radius:10px; border:1px solid #2a3263;
      background:linear-gradient(135deg, #4f46e5, #06b6d4); color:#fff; cursor:pointer; transition:.15s; white-space:nowrap;
    }
    .add-btn:hover{ transform:translateY(-1px) }
    .add-btn[disabled],
    .qty-wrap[aria-disabled="true"] .qty-btn{ opacity:.55; cursor:not-allowed; transform:none !important }
    .qty-wrap[aria-disabled="true"] .qty-input{ opacity:.7 }

    .card-actions{ display:flex; gap:8px; align-items:center; justify-content:flex-end; flex-wrap:wrap }
    .price-pill{ background:#0f1533; border:1px solid #1c2346; padding:6px 10px; border-radius:999px; font-weight:600 }
  </style>

  {{-- Search --}}
  <div class="card mb">
    <form method="get" class="row" autocomplete="off" style="gap:10px; align-items:center">
      <input class="input" name="search" value="{{ request('search') }}" placeholder="Search title, author, ISBN">
      <button class="btn right">Search</button>
      @if(request('search'))
        <a href="{{ route('customer.index') }}" class="pill">Clear</a>
      @endif
    </form>
  </div>

  @if($books->count())
    <div class="grid grid-3">
      @foreach($books as $book)
        @php
          $inStock = (int) ($book->stock ?? 0);
          $maxQty  = $inStock > 0 ? $inStock : 1;
        @endphp
        <div class="card" style="display:flex;flex-direction:column;gap:12px">
          {{-- Cover --}}
          <div class="center" style="background:#0f1533;border:1px solid #1c2346;border-radius:12px;padding:12px;height:230px;display:flex;align-items:center;justify-content:center;">
            @if($book->cover_image_url)
              <img src="{{ $book->cover_image_url }}" alt="Cover" style="max-height:100%;max-width:100%;object-fit:contain;">
            @else
              <span class="muted">No Cover</span>
            @endif
          </div>

          {{-- Meta --}}
          <div>
            <div class="row">
              <h3 style="margin:0">{{ $book->title }}</h3>
              <span class="pill right"
                    style="background:{{ $inStock>0 ? 'linear-gradient(180deg,#0d1d13,#11271a)' : 'linear-gradient(180deg,#1a0e0e,#241012)' }};
                           border-color:{{ $inStock>0 ? '#1d3f2a' : '#3e1d1d' }}">
                {{ $inStock>0 ? 'In stock ('.$inStock.')' : 'Out of stock' }}
              </span>
            </div>
            <div class="muted">by {{ $book->author }}</div>
            <div class="muted">Category: {{ $book->categories->first()->name ?? '-' }}</div>

            @if(!empty($book->description))
              <p class="muted" style="margin-top:8px;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;">
                {{ $book->description }}
              </p>
            @endif
          </div>

          {{-- Actions --}}
          <div class="row" style="align-items:center;gap:8px">
            <div class="price-pill">RM {{ number_format($book->price,2) }}</div>
            <div class="right card-actions">
              <a href="{{ route('customer.show',$book) }}" class="pill">View Details</a>

              <form method="POST" action="{{ route('cart.add') }}" class="row" style="gap:10px; align-items:center">
                @csrf
                <input type="hidden" name="book_id" value="{{ $book->id }}">

                {{-- Quantity stepper --}}
                <div class="qty-wrap"
                     data-qty
                     aria-disabled="{{ $inStock <= 0 ? 'true' : 'false' }}">
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

                {{-- Add to Cart --}}
                <button type="submit"
                        class="add-btn"
                        {{ $inStock <= 0 ? 'disabled' : '' }}>
                  <span>🛒</span> <span>Add to Cart</span>
                </button>
              </form>
            </div>
          </div>
        </div>
      @endforeach
    </div>

    <div class="mt">
      {{ $books->withQueryString()->links() }}
    </div>
  @else
    <div class="card center muted">No books available right now.</div>
  @endif

  <script>
    (function(){
      document.querySelectorAll('[data-qty]').forEach(wrap => {
        const input = wrap.querySelector('input[type="number"]');
        if(!input) return;

        const form  = wrap.closest('form');
        const addBtn= form ? form.querySelector('.add-btn') : null;

        function clamp(){
          const min = Number(input.min || 1);
          const max = Number(input.max || 9999);
          let val   = Number(input.value || 1);
          if (isNaN(val)) val = min;
          if (val < min) val = min;
          if (val > max) val = max;
          input.value = val;
          if (addBtn){
            const disabled = wrap.getAttribute('aria-disabled') === 'true' || val < min || val > max;
            addBtn.disabled = disabled;
          }
        }
        input.addEventListener('input', clamp);
        clamp();
      });
    })();
  </script>
@endsection
