{{-- Author: Chai Hao Lun--}}
{{-- resources/views/checkout/show.blade.php (or your current checkout Blade) --}}
@extends('layouts.app')

@section('header')
  <h2 class="font-semibold text-xl text-gray-800 leading-tight">Checkout</h2>
@endsection

@section('content')
@php
  // Safe fallbacks
  $items     = $items ?? collect();
  $subtotal  = (float) ($subtotal ?? 0);
  $itemCount = $items->sum(fn($i) => (int) $i->quantity);
@endphp

<style>
  .steps { display:flex; align-items:center; gap:10px; flex-wrap:wrap }
  .step { display:flex; align-items:center; gap:8px }
  .step .dot{ width:9px;height:9px;border-radius:999px;background:#2a3263 }
  .step.active .dot{ background:linear-gradient(135deg,#4f46e5,#06b6d4) }
  .sep{ width:28px;height:2px;background:#1c2346;border-radius:2px }

  .aside-sticky{ position:sticky; top:84px } /* under your sticky header */

  /* Small helper for radio-as-cards */
  .radio-card{ display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:12px;border:1px solid #2a3263;background:#0f1533;cursor:pointer;transition:.15s }
  .radio-card:hover{ border-color:#39408a; transform:translateY(-1px) }
  .radio-card input{ accent-color:#6366f1 }

  .field-label{ display:block; font-size:14px; color:#cbd5e1; margin:8px 0 6px }
  .muted-sm{ color:#9aa4c2; font-size:12px }

  .hidden{ display:none }
</style>

<div class="grid" style="gap:16px">

  {{-- Step indicator --}}
  <div class="card">
    <div class="steps">
      <div class="step active"><span class="dot"></span><span>Cart</span></div>
      <div class="sep"></div>
      <div class="step active"><span class="dot"></span><span>Checkout</span></div>
      <div class="sep"></div>
      <div class="step"><span class="dot"></span><span>Done</span></div>
    </div>

    {{-- Inline flash (your layout also shows toasts; these are gentle fallbacks) --}}
    @if(session('err'))
      <div class="pill" style="margin-top:10px;border-color:#3e1d1d;background:linear-gradient(180deg,#1a0e0e,#241012)">⚠️ {{ session('err') }}</div>
    @endif
    @if($errors->any())
      <div class="pill" style="margin-top:10px;border-color:#3e1d1d;background:linear-gradient(180deg,#1a0e0e,#241012)">
        Please fix the highlighted fields below.
      </div>
    @endif
  </div>

  {{-- Main grid: left = forms, right = summary --}}
  <div class="grid" style="grid-template-columns:2fr 1fr; gap:16px">

    {{-- LEFT: Payment + Shipping Form --}}
    <form method="POST" action="{{ route('checkout') }}" class="grid" style="gap:16px">
      @csrf

      {{-- Shipping address --}}
      <div class="card">
        <div class="row" style="justify-content:space-between;align-items:center">
          <h3 style="margin:0">📦 Shipping Address</h3>
        </div>

        <label class="field-label">Address</label>
        <textarea name="shipping_address" rows="4" class="input" required>{{ old('shipping_address', $userAddress ?? '') }}</textarea>


        @error('shipping_address')
          <div class="muted-sm" style="color:#fca5a5">{{ $message }}</div>
        @enderror

        @if(empty($userAddress))
          <div class="muted-sm" style="margin-top:6px">
            No address found in your profile. You can update it via <a href="{{ route('profile.edit') }}">Profile</a>.
          </div>
        @endif
      </div>

      {{-- Shipping method --}}
      <div class="card">
        <h3 style="margin:0 0 8px">🚚 Shipping Method</h3>
        <div class="grid" style="grid-template-columns:1fr; gap:10px">
          <label class="radio-card">
            <input type="radio" name="shipping_method" value="standard" class="input-radio" checked>
            <div>
              <div>Standard (3–5 days) — <strong>RM <span data-ship="standard">5.00</span></strong></div>
              <div class="muted-sm">Reliable & affordable</div>
            </div>
          </label>
          <label class="radio-card">
            <input type="radio" name="shipping_method" value="express" class="input-radio" {{ old('shipping_method')==='express'?'checked':'' }}>
            <div>
              <div>Express (1–2 days) — <strong>RM <span data-ship="express">12.00</span></strong></div>
              <div class="muted-sm">Fastest delivery</div>
            </div>
          </label>
        </div>
        {{-- This hidden keeps server-aware shipping amount if you want to read it --}}
        <input type="hidden" name="shipping_amount" id="shipping_amount" value="{{ old('shipping_amount','5.00') }}">
      </div>

      @php
  // Help the view decide which payment section to open if there are errors
  $pmOld = old('payment_method');
  $showE = $pmOld==='E-Wallet' || $errors->has('wallet_provider') || $errors->has('wallet_id');
  $showC = $pmOld==='Credit Card' || $errors->has('card_number') || $errors->has('exp_month') || $errors->has('exp_year') || $errors->has('cvv') || $errors->has('card_name');
  $showB = $pmOld==='Bank Transfer' || $errors->has('bank_name') || $errors->has('transfer_ref');
@endphp

<style>
  /* red ring for invalid inputs */
  .input.error{ border-color:#ef4444 !important; box-shadow:0 0 0 3px rgba(239,68,68,.25) }
  .err { color:#fca5a5; font-size:12px; margin-top:4px }
</style>

<div class="card">
  <h3 style="margin:0 0 8px">💳 Payment</h3>

  {{-- Method --}}
  <label class="field-label">Method</label>
  <select name="payment_method" id="payment_method" class="input @error('payment_method') error @enderror" required>
    <option value="">-- Select --</option>
    <option value="E-Wallet"     {{ old('payment_method')==='E-Wallet'?'selected':'' }}>E-Wallet</option>
    <option value="Credit Card"  {{ old('payment_method')==='Credit Card'?'selected':'' }}>Credit/Debit Card</option>
    <option value="Bank Transfer"{{ old('payment_method')==='Bank Transfer'?'selected':'' }}>Bank Transfer</option>
  </select>
  @error('payment_method') <div class="err">{{ $message }}</div> @enderror

  {{-- E-Wallet --}}
  <div id="pm-ewallet" class="mt {{ $showE ? '' : 'hidden' }}">
    <label class="field-label">E-Wallet</label>
    <div class="row" style="gap:10px; flex-wrap:wrap">
      <input name="wallet_provider"
             class="input @error('wallet_provider') error @enderror"
             placeholder="Wallet Provider (e.g. Touch 'n Go)"
             value="{{ old('wallet_provider') }}" style="max-width:260px">
      <input name="wallet_id"
             class="input @error('wallet_id') error @enderror"
             placeholder="Wallet ID / Phone"
             value="{{ old('wallet_id') }}" style="max-width:260px">
    </div>
    @error('wallet_provider') <div class="err">{{ $message }}</div> @enderror
    @error('wallet_id')       <div class="err">{{ $message }}</div> @enderror
  </div>

  {{-- Credit/Debit Card --}}
  <div id="pm-card" class="mt {{ $showC ? '' : 'hidden' }}">
    <label class="field-label">Card Details</label>
    <div class="row" style="gap:10px; flex-wrap:wrap">
      <input name="card_name"   class="input @error('card_name') error @enderror"   placeholder="Name on Card" value="{{ old('card_name') }}" style="max-width:260px">
      <input name="card_number" class="input @error('card_number') error @enderror" placeholder="Card Number"   value="{{ old('card_number') }}" style="max-width:260px">
      <input name="exp_month"   type="number" min="1" max="12"
             class="input @error('exp_month') error @enderror"  placeholder="MM"    value="{{ old('exp_month') }}" style="max-width:100px">
      <input name="exp_year"    type="number" min="{{ now()->year }}"
             class="input @error('exp_year') error @enderror"   placeholder="YYYY"  value="{{ old('exp_year') }}" style="max-width:120px">
      <input name="cvv"         class="input @error('cvv') error @enderror"         placeholder="CVV"           value="{{ old('cvv') }}" style="max-width:100px">
    </div>
    @error('card_name')   <div class="err">{{ $message }}</div> @enderror
    @error('card_number') <div class="err">{{ $message }}</div> @enderror
    @error('exp_month')   <div class="err">{{ $message }}</div> @enderror
    @error('exp_year')    <div class="err">{{ $message }}</div> @enderror
    @error('cvv')         <div class="err">{{ $message }}</div> @enderror
  </div>

  {{-- Bank Transfer --}}
  <div id="pm-bank" class="mt {{ $showB ? '' : 'hidden' }}">
    <label class="field-label">Bank Transfer</label>
    <div class="row" style="gap:10px; flex-wrap:wrap">
      <input name="bank_name"    class="input @error('bank_name') error @enderror"   placeholder="Bank Name" value="{{ old('bank_name') }}" style="max-width:260px">
      <input name="transfer_ref" class="input @error('transfer_ref') error @enderror" placeholder="Transfer Reference / Receipt No." value="{{ old('transfer_ref') }}" style="max-width:260px">
    </div>
    @error('bank_name')    <div class="err">{{ $message }}</div> @enderror
    @error('transfer_ref') <div class="err">{{ $message }}</div> @enderror
  </div>
</div>

<script>
  // Keep your existing toggle, but this will respect the initial (server) state on load
  (function () {
    const pm   = document.getElementById('payment_method');
    const secE = document.getElementById('pm-ewallet');
    const secC = document.getElementById('pm-card');
    const secB = document.getElementById('pm-bank');

    function togglePM(){
      const v = pm.value;
      secE.classList.toggle('hidden', v !== 'E-Wallet');
      secC.classList.toggle('hidden', v !== 'Credit Card');
      secB.classList.toggle('hidden', v !== 'Bank Transfer');
    }
    pm.addEventListener('change', togglePM);
    // initial state already set by Blade using $showE/$showC/$showB; no need to re-toggle
  })();
</script>


      {{-- Discount / Notes --}}
     {{-- 🪙 Rewards --}}
@php
  $userPoints = (int) ($userPoints ?? (auth()->user()->points ?? 0)); 
  $pointsRM   = $userPoints / 100;
@endphp

<div class="card">
  <h3 style="margin:0 0 8px">🪙 Rewards</h3>

  <div class="row" style="gap:10px;align-items:center;flex-wrap:wrap">
    <input type="hidden" name="use_points" value="0">
    <label class="radio-card" style="cursor:pointer">
      <input type="checkbox"
             id="use_points"
             name="use_points"
             value="1"
             {{ old('use_points') ? 'checked' : '' }}
             data-points="{{ $userPoints }}">
      <div>
        <div><strong>Use my points</strong></div>
        <div class="muted-sm">
          You have <strong>{{ number_format($userPoints) }}</strong> points.
        </div>
        {{-- 🔽 live hint goes here --}}
        <div id="ptsSpendHint" class="muted-sm" style="margin-top:4px"></div>
      </div>
    </label>
  </div>

  <div class="mt">
    <label class="field-label">Order Notes (optional)</label>
    <input name="order_note" class="input" placeholder="Message for the seller…" value="{{ old('order_note') }}">
  </div>
</div>


      {{-- Footer actions --}}
      <div class="row" style="justify-content:flex-end; gap:8px; flex-wrap:wrap">
        <a href="{{ route('cart.index') }}" class="pill">← Back to Cart</a>
        <button class="btn success">Make Payment</button>
      </div>
    </form>

    {{-- RIGHT: Order Summary --}}
    <aside class="aside-sticky">
      <div class="card">
        <div class="row" style="justify-content:space-between; align-items:center">
          <h3 style="margin:0">Order Summary</h3>
          <span class="pill muted">{{ number_format($itemCount) }} item{{ $itemCount===1?'':'s' }}</span>
        </div>

        @if($items->isEmpty())
          <div class="muted mt">Your cart is empty.</div>
        @else
          <div class="grid" style="gap:10px; margin-top:8px">
            @foreach($items as $it)
              @php
                $book = $it->book;
                $qty  = (int) $it->quantity;
                $unit = (float) ($book->price ?? 0);
                $line = $unit * $qty;
              @endphp
              <div class="row" style="gap:10px; align-items:center">
                <div style="width:46px;height:64px;border-radius:8px;border:1px solid #1c2346;background:#0f1533;overflow:hidden;display:flex;align-items:center;justify-content:center">
                  @if($book?->cover_image_url)
                    <img src="{{ $book->cover_image_url }}" alt="Cover" style="width:100%;height:100%;object-fit:cover">
                  @else
                    <span class="muted" style="font-size:11px">No cover</span>
                  @endif
                </div>
                <div style="flex:1;min-width:0">
                  <div style="font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $book->title ?? 'Unknown' }}</div>
                  <div class="muted-sm">x{{ $qty }} • RM {{ number_format($unit,2) }}</div>
                </div>
                <div style="font-weight:600">RM {{ number_format($line,2) }}</div>
              </div>
            @endforeach
          </div>

          <hr style="border-color:#1c2346; margin:12px 0">

          <div class="row" style="justify-content:space-between">
            <span class="muted">Subtotal</span>
            <span>RM <span id="sumSubtotal">{{ number_format($subtotal,2) }}</span></span>
          </div>
          <div class="row" style="justify-content:space-between; margin-top:6px">
            <span class="muted">Shipping</span>
            <span>RM <span id="sumShipping">5.00</span></span>
          </div>
          <div class="row" style="justify-content:space-between; margin-top:6px">
            <span class="muted">Points Redeemed</span>
            <span>− RM <span id="sumDiscount">0.00</span></span>
          </div>


          <div class="row" style="justify-content:space-between; margin-top:10px; padding-top:10px; border-top:1px solid #1c2346">
            <strong>Total</strong>
            <strong>RM <span id="sumTotal">{{ number_format($subtotal + 5 - (float)old('discount_amount',0), 2) }}</span></strong>
          </div>

          <div class="muted-sm" style="margin-top:8px">Taxes calculated at checkout (if applicable).</div>

          <div class="row" style="gap:8px; margin-top:12px; flex-wrap:wrap">
            <span class="pill">🔒 Secure checkout</span>
            <span class="pill">💳 Visa · MasterCard</span>
            <span class="pill">🏦 FPX</span>
          </div>
        @endif
      </div>
    </aside>

  </div>
</div>

<script>
  (function () {
    const subtotal        = parseFloat(@json($subtotal));
    const shipInputHidden = document.getElementById('shipping_amount');
    const shipOut         = document.getElementById('sumShipping');
    const discOut         = document.getElementById('sumDiscount');
    const totalOut        = document.getElementById('sumTotal');
    const usePointsEl     = document.getElementById('use_points');
    const hintEl          = document.getElementById('ptsSpendHint');
    const PTS_PER_RM      = 100; // 100 pts = RM 1

    const shipPrices = {
      standard: parseFloat(document.querySelector('[data-ship="standard"]').textContent || '5.00'),
      express:  parseFloat(document.querySelector('[data-ship="express"]').textContent || '12.00'),
    };

    function currentShip() {
      const val = document.querySelector('input[name="shipping_method"]:checked')?.value || 'standard';
      return shipPrices[val] ?? 5.00;
    }

    function recalc() {
      const ship      = currentShip();
      const ptsAvail  = parseInt(usePointsEl?.dataset.points || '0', 10);
      const wantsPts  = !!(usePointsEl && usePointsEl.checked);

      // Max points based on current order value (subtotal + shipping)
      const maxByTotalPts = Math.round((subtotal + ship) * PTS_PER_RM);
      const ptsToSpend    = wantsPts ? Math.min(ptsAvail, maxByTotalPts) : 0;

      const discountRM = ptsToSpend / PTS_PER_RM;
      const totalRM    = Math.max(0, subtotal + ship - discountRM);

      // Write numbers to summary
      shipOut.textContent   = ship.toFixed(2);
      shipInputHidden.value = ship.toFixed(2);
      discOut.textContent   = discountRM.toFixed(2);
      totalOut.textContent  = totalRM.toFixed(2);

      // Update the live hint
      if (hintEl) {
        if (wantsPts && ptsToSpend > 0) {
          hintEl.textContent =
            `You will spend ${ptsToSpend.toLocaleString()} pts (RM ${discountRM.toFixed(2)}) on this order.`;
        } else {
          const maxRM = (ptsAvail / PTS_PER_RM).toFixed(2);
          hintEl.textContent =
            `You can spend up to ${ptsAvail.toLocaleString()} pts (RM ${maxRM}).`;
        }
      }
    }

    document.querySelectorAll('input[name="shipping_method"]').forEach(r => r.addEventListener('change', recalc));
    if (usePointsEl) usePointsEl.addEventListener('change', recalc);

    recalc();
  })();
</script>


@endsection
