{{-- Author: Chai Hao Lun--}}
@extends('layouts.app')
@section('title','Order #'.$order->id)

@section('content')
<div class="grid" style="gap:16px">

  <div class="card">
    <div class="row" style="justify-content:space-between;align-items:center">
      <div>
        <h2 style="margin:0">Order #{{ $order->id }}</h2>
        <div class="muted">Placed {{ $order->order_date?->format('Y-m-d H:i') }}</div>
        <div class="mt">
          <span class="pill">Status: {{ $order->status }}</span>
          <span class="pill">Payment: {{ $order->payment_method }}</span>
        </div>
      </div>
      <a href="{{ route('staff.orders.index') }}" class="pill">← Back to list</a>
    </div>
  </div>

  <div class="grid grid-2">
    {{-- Left: Items --}}
    <div class="card">
      <h3 style="margin:0 0 8px">Items</h3>
      <table class="table">
        <thead>
          <tr>
            <th>Book</th>
            <th>Qty</th>
            <th>Unit (RM)</th>
            <th class="right">Line (RM)</th>
          </tr>
        </thead>
        <tbody>
          @foreach($order->items as $it)
            <tr>
              <td>
                <div style="display:flex;gap:10px;align-items:center">
                  <div style="width:40px;height:56px;border-radius:8px;border:1px solid #1c2346;background:#0f1533;overflow:hidden;display:flex;align-items:center;justify-content:center">
                    @if($it->book?->cover_image_url)
                      <img src="{{ $it->book->cover_image_url }}" alt="cover" style="width:100%;height:100%;object-fit:cover">
                    @else
                      <span class="muted" style="font-size:11px">No cover</span>
                    @endif
                  </div>
                  <div>
                    <div style="font-weight:600">{{ $it->book->title ?? ('#'.$it->book_id) }}</div>
                    <div class="muted" style="font-size:12px">{{ $it->book->author ?? '' }}</div>
                  </div>
                </div>
              </td>
              <td>{{ $it->quantity }}</td>
              <td>{{ number_format($it->unit_price,2) }}</td>
              <td class="right" style="font-weight:600">RM {{ number_format($it->unit_price * $it->quantity,2) }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    {{-- Right: Customer + Shipment + Totals + Actions --}}
    <div class="grid" style="gap:16px">
      <div class="card">
        <h3 style="margin:0 0 8px">Customer</h3>
        <div>{{ $order->user?->name ?? '—' }}</div>
        <div class="muted">{{ $order->user?->email ?? '' }}</div>
      </div>

      <div class="card">
        <h3 style="margin:0 0 8px">Shipping Address</h3>
        <div style="white-space:pre-wrap">{{ $order->shipment?->shipping_address ?? '—' }}</div>
      </div>

      <div class="card">
        <h3 style="margin:0 0 8px">Totals</h3>
        <div class="row" style="justify-content:space-between"><span class="muted">Subtotal</span><span>RM {{ number_format($order->subtotal_amount,2) }}</span></div>
        <div class="row" style="justify-content:space-between"><span class="muted">Shipping</span><span>RM {{ number_format($order->shipping_amount ?? 0,2) }}</span></div>
        <div class="row" style="justify-content:space-between"><span class="muted">Discount</span><span>− RM {{ number_format($order->discount_amount,2) }}</span></div>
        <hr style="border-color:#1c2346">
        <div class="row" style="justify-content:space-between"><strong>Total</strong><strong>RM {{ number_format($order->total_amount,2) }}</strong></div>
        @if(!empty($order->notes))
          <div class="mt muted">Notes: {{ $order->notes }}</div>
        @endif
      </div>

      {{-- Quick actions --}}
      @php
        $canShip     = $order->status === 'Processing';
        $canArrive   = $order->status === 'Shipped';
        $canComplete = $order->status === 'Arrived';
        $canCancel   = $order->status === 'Processing';
      @endphp
      <div class="card">
        <h3 style="margin:0 0 8px">Actions</h3>
        <div class="row" style="gap:8px;flex-wrap:wrap">
          @if($canShip)
            <form method="POST" action="{{ route('orders.ship', $order) }}">@csrf @method('PATCH') <button class="btn">Ship</button></form>
          @endif
          @if($canArrive)
            <form method="POST" action="{{ route('orders.arrive', $order) }}">@csrf @method('PATCH') <button class="btn">Mark Arrived</button></form>
          @endif
          @if($canComplete)
            <form method="POST" action="{{ route('orders.complete', $order) }}">@csrf @method('PATCH') <button class="btn success">Complete</button></form>
          @endif
          @if($canCancel)
            <form method="POST" action="{{ route('orders.cancel', $order) }}"
                  onsubmit="return confirm('Cancel this order?');">
              @csrf @method('PATCH')
              <button class="btn danger">Cancel</button>
            </form>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- Audit: all transactions --}}
  <div class="card">
    <h3 style="margin:0 0 8px">Transactions</h3>
    @if($order->transactions->count())
      <table class="table">
        <thead>
          <tr><th>Tx</th><th>Date</th><th>Type</th><th class="right">Amount (RM)</th></tr>
        </thead>
        <tbody>
          @foreach($order->transactions as $t)
            <tr>
              <td>#{{ $t->id }}</td>
              <td>{{ $t->transaction_date?->format('Y-m-d H:i') }}</td>
              <td>{{ $t->transaction_type }}</td>
              <td class="right" style="font-weight:600">RM {{ number_format($t->amount,2) }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @else
      <div class="muted">No transactions.</div>
    @endif
  </div>
</div>
@endsection
