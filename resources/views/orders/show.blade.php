{{-- Author: Chai Hao Lun--}}
{{-- resources/views/orders/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Order #'.$order->id)

@section('header')
  <h2 class="font-semibold text-xl text-gray-800 leading-tight">
    Order #{{ $order->id }}
  </h2>
@endsection

@section('content')
@php
  // Safe helpers
  $items      = $order->items ?? collect();
  $itemCount  = $items->sum(fn($i) => (int) $i->quantity);
  $statusRaw  = (string) ($order->status ?? '');
  $status     = strtolower($statusRaw);

  // Status pill styles
  $statusStyle = 'border-color:#1d2d45;background:linear-gradient(180deg,#0d1423,#0f1628)'; // default/info
  if (in_array($status, ['pending','awaiting payment'])) {
      $statusStyle = 'border-color:#3b2a12;background:linear-gradient(180deg,#20150a,#281a0c);color:#fcd39b';
  } elseif (in_array($status, ['paid','processing'])) {
      $statusStyle = 'border-color:#1d3f2a;background:linear-gradient(180deg,#0d1d13,#11271a)'; // green
  } elseif (in_array($status, ['shipped','in transit'])) {
      $statusStyle = 'border-color:#1d2d45;background:linear-gradient(180deg,#0d1423,#0f1628)'; // blue-ish
  } elseif (in_array($status, ['delivered','completed'])) {
      $statusStyle = 'border-color:#1d3f2a;background:linear-gradient(180deg,#0d1d13,#11271a)'; // green
  } elseif (in_array($status, ['cancelled','canceled','refunded','failed'])) {
      $statusStyle = 'border-color:#3e1d1d;background:linear-gradient(180deg,#1a0e0e,#241012)'; // red
  }

  // Timeline steps
  $steps = ['Placed','Paid','Shipped','Delivered'];
  $activeIndex = 0; // 0-based
  if (in_array($status, ['pending','awaiting payment']))      $activeIndex = 0;
  elseif (in_array($status, ['paid','processing']))           $activeIndex = 1;
  elseif (in_array($status, ['shipped','in transit']))        $activeIndex = 2;
  elseif (in_array($status, ['delivered','completed']))       $activeIndex = 3;
  elseif (in_array($status, ['cancelled','canceled','refunded','failed'])) $activeIndex = 0; // show "Placed" only

  // Dates for milestones (optional)
  $placedAt    = $order->order_date ?? null;
  $shippedAt   = optional($order->shipment)->shipped_date ?? null;
  $deliveredAt = optional($order->shipment)->delivery_date ?? null;

  // Amounts
  $subtotal = (float) ($order->subtotal_amount ?? 0);
  $discount = (float) ($order->discount_amount ?? 0);
  $total    = (float) ($order->total_amount ?? 0);
@endphp

<style>
  .timeline { display:flex; align-items:center; gap:10px; flex-wrap:wrap }
  .t-step   { display:flex; align-items:center; gap:8px }
  .t-dot    { width:12px; height:12px; border-radius:999px; border:2px solid #2a3263; background:transparent }
  .t-step.active .t-dot { border-color:transparent; background: linear-gradient(135deg, var(--brand), var(--brand-2)); box-shadow:0 0 0 2px rgba(99,102,241,.25) }
  .t-sep    { width:34px; height:2px; background:#1c2346; border-radius:2px }
  .muted-sm { color:#9aa4c2; font-size:12px }
  @media print {
    header, .btn, .pill, a[href]:after { display:none !important; }
    body { background:white; }
    .card { box-shadow:none !important; border-color:#ddd !important }
  }
</style>

<div class="grid" style="gap:16px">

  {{-- Top summary / actions --}}
  <div class="card">
    <div class="row" style="justify-content:space-between; align-items:flex-start; gap:12px; flex-wrap:wrap">
      <div class="grid" style="gap:6px">
        <div class="row" style="gap:8px; align-items:center; flex-wrap:wrap">
          <h3 style="margin:0">Order #{{ $order->id }}</h3>
          <span class="pill" style="{{ $statusStyle }}">{{ $statusRaw }}</span>
        </div>
        <div class="muted">
          Placed: {{ $placedAt?->format('Y-m-d H:i') ?? '—' }} • Payment: {{ $order->payment_method ?? '—' }}
        </div>

        {{-- timeline --}}
        <div class="timeline" style="margin-top:6px">
          @foreach($steps as $i => $s)
            <div class="t-step {{ $i <= $activeIndex ? 'active' : '' }}">
              <div class="t-dot"></div>
              <div class="muted-sm">{{ $s }}</div>
            </div>
            @if($i < count($steps)-1)
              <div class="t-sep"></div>
            @endif
          @endforeach
        </div>
      </div>

      <div class="row" style="gap:8px; flex-wrap:wrap">
        <button class="btn" onclick="window.print()">🧾 Print</button>
        <a href="{{ route('orders.index') }}" class="pill">← Back to Orders</a>
        <a href="{{ route('customer.index') }}" class="btn">Continue Shopping</a>
      </div>
    </div>
  </div>

  {{-- Main grid: left detail / right totals --}}
  <div class="grid" style="grid-template-columns:2fr 1fr; gap:16px">

    {{-- LEFT: Items + Shipment + Transactions --}}
    <div class="grid" style="gap:16px">

      {{-- Items --}}
      <div class="card">
        <div class="row" style="justify-content:space-between; align-items:center; margin-bottom:8px">
          <h3 style="margin:0">Items</h3>
          <span class="pill muted">{{ number_format($itemCount) }} item{{ $itemCount===1?'':'s' }}</span>
        </div>

        <table class="table">
          <thead>
            <tr>
              <th style="width:72px">Cover</th>
              <th>Title</th>
              <th style="width:90px">Qty</th>
              <th style="width:120px">Unit</th>
              <th style="width:140px">Total</th>
            </tr>
          </thead>
          <tbody>
            @foreach($items as $it)
              @php
                $book = $it->book;
                $qty  = (int) $it->quantity;
                $unit = (float) $it->unit_price;
                $line = $unit * $qty;
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
                  <div style="font-weight:600">{{ $book->title ?? ('Book #'.$it->book_id) }}</div>
                  <div class="muted-sm">{{ $book->author ?? '' }}</div>
                </td>
                <td>{{ $qty }}</td>
                <td>RM {{ number_format($unit,2) }}</td>
                <td><strong>RM {{ number_format($line,2) }}</strong></td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      {{-- Shipment --}}
      <div class="card">
        <div class="row" style="justify-content:space-between; align-items:center; margin-bottom:8px">
          <h3 style="margin:0">Shipment</h3>
          @if(optional($order->shipment)->tracking_no)
            <span class="pill">Tracking: {{ $order->shipment->tracking_no }}</span>
          @endif
        </div>

        <div class="grid" style="grid-template-columns:1fr 1fr; gap:12px">
          <div>
            <div class="muted-sm">Address</div>
            <div style="white-space:pre-line">{{ $order->shipment->shipping_address ?? '—' }}</div>
            @if($order->shipment?->shipping_address)
              <button class="pill" style="margin-top:8px; color:white" onclick="copyAddr()">Copy address</button>
            @endif
          </div>
          <div>
            <div class="row" style="justify-content:space-between">
              <span class="muted-sm">Shipped</span>
              <span>{{ optional($order->shipment->shipped_date)->format('Y-m-d H:i') ?? '—' }}</span>
            </div>
            <div class="row" style="justify-content:space-between; margin-top:6px">
              <span class="muted-sm">Delivered</span>
              <span>{{ optional($order->shipment->delivery_date)->format('Y-m-d H:i') ?? '—' }}</span>
            </div>
          </div>
        </div>
      </div>

      {{-- Transactions --}}
      <div class="card">
        <h3 style="margin:0 0 8px">Transactions</h3>
        @if(($order->transactions ?? collect())->count())
          <table class="table">
            <thead>
              <tr>
                <th>Date</th>
                <th>Type</th>
                <th class="right" style="width:160px">Amount</th>
              </tr>
            </thead>
            <tbody>
              @foreach($order->transactions as $tx)
                <tr>
                  <td>{{ $tx->transaction_date?->format('Y-m-d H:i') ?? '—' }}</td>
                  <td>{{ $tx->transaction_type }}</td>
                  <td class="right">RM {{ number_format((float)$tx->amount,2) }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        @else
          <div class="muted">No transactions recorded.</div>
        @endif
      </div>

    </div>

    {{-- RIGHT: Totals --}}
    <aside class="card" style="position:sticky; top:84px; height:max-content">
      <h3 style="margin:0 0 8px">Summary</h3>

      <div class="row" style="justify-content:space-between">
        <span class="muted">Items</span>
        <span>{{ number_format($itemCount) }}</span>
      </div>

      <div class="row" style="justify-content:space-between; margin-top:6px">
        <span class="muted">Subtotal</span>
        <span>RM {{ number_format($subtotal,2) }}</span>
      </div>

      <div class="row" style="justify-content:space-between; margin-top:6px">
            <span class="muted">Shipping</span>
            <span>RM <span id="sumShipping">{{ number_format($order->shipping_amount,2) }}</span></span>
      </div>

      <div class="row" style="justify-content:space-between; margin-top:6px">
        <span class="muted">Discount</span>
        <span>− RM {{ number_format($discount,2) }}</span>
      </div>

      <div class="row" style="justify-content:space-between; margin-top:10px; padding-top:10px; border-top:1px solid #1c2346">
        <strong>Total</strong>
        <strong>RM {{ number_format($total,2) }}</strong>
      </div>

      <div class="muted-sm" style="margin-top:8px">
        Payment method: <strong>{{ $order->payment_method ?? '—' }}</strong>
      </div>

    
      <div class="mt-2 text-sm text-gray-700" style="margin-top:20px"><strong>Notes:</strong>   
        <div class="muted-sm" style="margin-top:8px; margin-bottom:30px">
          {{ $order->notes ?? '—' }}
        </div>  
      </div>

      <div class="row" style="gap:8px; margin-top:12px; flex-wrap:wrap">
        <a href="{{ route('orders.index') }}" class="pill">← Back to Orders</a>
        <a href="{{ route('customer.index') }}" class="btn">Continue Shopping</a>
      </div>
    </aside>

  </div>
</div>

<script>
  function copyAddr(){
    const txt = @json(optional($order->shipment)->shipping_address ?? '');
    if (!txt) return;
    navigator.clipboard.writeText(txt).then(()=>{
      // quick toast
      const el = document.createElement('div');
      el.className = 'toast info';
      el.innerHTML = '<div><div class="title">Copied</div><div>Shipping address copied.</div></div><button class="close" onclick="this.closest(\'.toast\').remove()">✕</button>';
      (document.getElementById('toasts') || document.body).appendChild(el);
      setTimeout(()=> el.remove(), 2500);
    });
  }
</script>
@endsection
