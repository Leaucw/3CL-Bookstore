{{-- Author: Chai Hao Lun--}}
{{-- resources/views/cart/index.blade.php (or wherever your Cart view lives) --}}
@extends('layouts.app')

@section('title','My Cart')

@section('content')
@php
  // keep view safe if controller didn't pass these
  $items    = $items ?? collect();
  $subtotal = $subtotal ?? 0.0;
  $itemCount = $items->sum(fn($i) => (int) $i->quantity);
@endphp

<div class="grid" style="gap:16px">

  {{-- Header + meta --}}
  <div class="card">
    <div class="row" style="justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px">
      <div class="row" style="gap:10px; align-items:center">
        <h2 style="margin:0">🛒 My Cart</h2>
        <span class="pill muted">{{ number_format($itemCount) }} item{{ $itemCount === 1 ? '' : 's' }}</span>
      </div>

      <div class="row" style="gap:8px; flex-wrap:wrap">
        <a href="{{ route('customer.index') }}" class="pill">← Continue Shopping</a>
        <a href="{{ route('checkout.show') }}" class="btn success">Proceed to Checkout</a>
      </div>
    </div>

    {{-- inline messages (your layout already has toasts; keep these as fallback) --}}
    @if(session('ok'))
      <div class="pill" style="border-color:#1d3f2a;background:linear-gradient(180deg,#0d1d13,#11271a);margin-top:10px">
        ✅ {{ session('ok') }}
      </div>
    @endif
    @if(session('err'))
      <div class="pill" style="border-color:#3e1d1d;background:linear-gradient(180deg,#1a0e0e,#241012);margin-top:10px">
        ⚠ {{ session('err') }}
      </div>
    @endif
  </div>

  @if($items->isEmpty())
    {{-- Empty state --}}
    <div class="card center" style="background:linear-gradient(180deg,#0f1630,#0b1128)">
      <div style="font-size:48px;line-height:1.1">🧺</div>
      <h3 style="margin:8px 0 6px">Your cart is empty</h3>
      <p class="muted mb">Browse the catalog to add your first book.</p>
      <a href="{{ route('customer.index') }}" class="btn primary">Browse Books</a>
    </div>
  @else

    {{-- Grid: items (2/3) + summary (1/3) --}}
    <div class="grid" style="grid-template-columns:2fr 1fr; gap:16px">
      {{-- Items table --}}
      <div class="card">
        <table class="table">
          <thead>
            <tr>
              <th style="width:72px">Cover</th>
              <th>Book</th>
              <th style="width:160px">Qty</th>
              <th style="width:120px">Unit (RM)</th>
              <th style="width:140px">Total (RM)</th>
              <th class="right" style="width:200px">Actions</th>
            </tr>
          </thead>
          <tbody>
          @foreach($items as $it)
            @php
              $book    = $it->book;
              $unit    = (float) ($book->price ?? 0);
              $qty     = (int) ($it->quantity ?? 1);
              $line    = $unit * $qty;
              $inStock = (int) ($book->stock ?? 0);
            @endphp
            <tr>
              <td>
                <div style="width:50px;height:72px;border-radius:8px;border:1px solid #1c2346;
                            background:#0f1533;overflow:hidden;display:flex;align-items:center;justify-content:center">
                  @if($book?->cover_image_url)
                    <img src="{{ $book->cover_image_url }}" alt="Cover" style="width:100%;height:100%;object-fit:cover">
                  @else
                    <span class="muted" style="font-size:11px">No cover</span>
                  @endif
                </div>
              </td>

              <td>
                <div style="font-weight:600">{{ $book->title ?? 'Unknown Book' }}</div>
                <div class="muted" style="font-size:12px">{{ $book->author ?? '' }}</div>
                <div class="muted" style="font-size:12px">#{{ $it->book_id }}</div>
                @if($inStock <= 3)
                  <span class="pill" style="margin-top:6px;display:inline-block;border-color:#3e2c1d;background:linear-gradient(180deg,#2a1a0e,#2f1e12);color:#fcd39b">
                    Low stock: {{ $inStock }}
                  </span>
                @endif
              </td>

              {{-- Quantity stepper --}}
              <td>
                <form method="POST" action="{{ route('cart.update', $it) }}" class="row qty-form" style="gap:6px;align-items:center">
                  @csrf
                  @method('PATCH')

                  <button type="button" class="pill qty-step" data-dir="-1" title="Decrease">−</button>

                  <input type="number" name="quantity" value="{{ $qty }}" min="1"
                         @if($inStock>0) max="{{ $inStock }}" @endif
                         class="input" style="width:64px;text-align:center;padding:6px 8px">

                  <button type="button" class="pill qty-step" data-dir="1" title="Increase">+</button>

                  <button class="btn" style="padding:8px 10px">Update</button>
                </form>
              </td>

              <td>RM {{ number_format($unit, 2) }}</td>
              <td><strong>RM {{ number_format($line, 2) }}</strong></td>

              {{-- Actions --}}
              <td class="right">
                <div class="row" style="gap:8px;justify-content:flex-end;flex-wrap:nowrap">
                  @if($book)
                    <a href="{{ route('customer.show', $book->id) }}" class="pill">View</a>
                  @endif

                  <form method="POST" action="{{ route('cart.remove', $it) }}">
                    @csrf @method('DELETE')
                    <button class="btn danger" data-confirm="Remove this item?">Remove</button>
                  </form>
                </div>
              </td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>

      {{-- Summary --}}
      <div class="card">
        <h3 style="margin:0 0 8px">Order Summary</h3>

        <div class="row" style="justify-content:space-between">
          <span class="muted">Items</span>
          <span>{{ number_format($itemCount) }}</span>
        </div>
        <div class="row" style="justify-content:space-between;margin-top:6px">
          <span class="muted">Subtotal</span>
          <span>RM {{ number_format($subtotal, 2) }}</span>
        </div>

        <div class="muted" style="font-size:12px;margin-top:10px">
          Taxes & shipping calculated at checkout.
        </div>

        <div class="row" style="gap:8px; margin-top:12px; flex-wrap:wrap">
          <a href="{{ route('customer.index') }}" class="pill">← Continue Shopping</a>
          <a href="{{ route('checkout.show') }}" class="btn success">Checkout</a>
        </div>
      </div>
    </div>
  @endif
</div>

{{-- Qty stepper + auto-submit on change --}}
<script>
  (function () {
    // Stepper buttons
    document.querySelectorAll('.qty-form').forEach(form => {
      const input = form.querySelector('input[name="quantity"]');
      const min = Number(input.getAttribute('min') || 1);
      const maxAttr = input.getAttribute('max');
      const max = maxAttr ? Number(maxAttr) : Infinity;

      form.querySelectorAll('.qty-step').forEach(btn => {
        btn.addEventListener('click', () => {
          const dir = Number(btn.dataset.dir) || 1;
          let val = Number(input.value || 1) + dir;
          if (val < min) val = min;
          if (val > max) val = max;
          input.value = val;
        });
      });

      // Auto-submit when user manually edits qty (small delay for typing)
      let t;
      input.addEventListener('input', () => {
        clearTimeout(t);
        t = setTimeout(() => form.submit(), 450);
      });
    });
  })();
</script>
<style>
  /* White +/- buttons with subtle hover */
  .qty-step{
    color:#fff; 
    background:linear-gradient(135deg,#4f46e5,#06b6d4);
    border:none;
    padding:8px 10px;
    border-radius:10px;
    cursor:pointer;
    transition:transform .12s ease, opacity .12s ease;
  }
  .qty-step:hover{ transform:translateY(-1px) }
  .qty-step:disabled{ opacity:.6; cursor:not-allowed }

  /* Compact number input */
  .qty-input{
    width:64px;text-align:center;padding:6px 8px
  }

  /* Center the whole control inside the cell */
  .qty-cell { text-align: center; }

  /* Make the form itself inline and centered, stacked vertically */
  .qty-form {
    display: inline-flex;          /* allows centering via text-align on td */
    flex-direction: column;        /* keep − / input / + in a column */
    align-items: center;           /* center each child */
    gap: 8px;
  }

  /* Nice, centered +/- buttons */
  .qty-step{
    display: inline-grid;
    place-items: center;
    width: 34px; height: 34px;
    border-radius: 10px;
    border: 1px solid #2a3263;
    background: linear-gradient(135deg,#4f46e5,#06b6d4);
    color: #fff;                   /* white symbol */
    font-weight: 700;
    line-height: 1;
    cursor: pointer;
  }

  /* Center number text */
  .qty-input{ width: 70px; text-align: center; }
</style>

@endsection
