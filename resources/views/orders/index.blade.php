{{-- Author: Chai Hao Lun--}}
{{-- resources/views/orders/index.blade.php --}}
@extends('layouts.app')

@section('title','My Orders')

@section('header')
  <h2 class="font-semibold text-xl text-gray-800 leading-tight">My Orders</h2>
@endsection

@section('content')
@php
  // Helpers for status pills
  $statusStyle = function (?string $s): string {
    $s = strtolower((string)$s);
    // defaults (info)
    $style = 'border-color:#1d2d45;background:linear-gradient(180deg,#0d1423,#0f1628)';
    if (in_array($s, ['pending','awaiting payment'])) {
      $style = 'border-color:#3b2a12;background:linear-gradient(180deg,#20150a,#281a0c);color:#fcd39b';
    } elseif (in_array($s, ['processing','paid'])) {
      $style = 'border-color:#1d3f2a;background:linear-gradient(180deg,#0d1d13,#11271a)';
    } elseif (in_array($s, ['shipped','in transit'])) {
      $style = 'border-color:#1d2d45;background:linear-gradient(180deg,#0d1423,#0f1628)';
    } elseif (in_array($s, ['delivered','completed'])) {
      $style = 'border-color:#1d3f2a;background:linear-gradient(180deg,#0d1d13,#11271a)';
    } elseif (in_array($s, ['cancelled','canceled','refunded','failed'])) {
      $style = 'border-color:#3e1d1d;background:linear-gradient(180deg,#1a0e0e,#241012)';
    }
    return $style;
  };
@endphp

<div class="grid" style="gap:16px">

  {{-- Top bar / quick actions --}}
  <div class="card">
    <div class="row" style="justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
      <div class="row" style="gap:10px;align-items:center">
        <h3 style="margin:0">Your Orders</h3>
        <span class="pill muted">Total: {{ number_format($orders->total()) }}</span>
        <span class="pill muted">This page: {{ number_format($orders->count()) }}</span>
      </div>
      <div class="row" style="gap:8px;flex-wrap:wrap">
        <a href="{{ route('customer.index') }}" class="pill">← Back to Shop</a>
      </div>
    </div>

    {{-- inline flash (your layout toasts will also show) --}}
    @if(session('ok'))
      <div class="pill" style="margin-top:10px;border-color:#1d3f2a;background:linear-gradient(180deg,#0d1d13,#11271a)">✅ {{ session('ok') }}</div>
    @endif
    @if(session('err'))
      <div class="pill" style="margin-top:10px;border-color:#3e1d1d;background:linear-gradient(180deg,#1a0e0e,#241012)">⚠️ {{ session('err') }}</div>
    @endif
  </div>

  @if($orders->count() === 0)
    {{-- Empty state --}}
    <div class="card center" style="background:linear-gradient(180deg,#0f1630,#0b1128);">
      <div style="font-size:48px;line-height:1.1">📭</div>
      <h3 style="margin:8px 0 6px">No orders yet</h3>
      <p class="muted mb">Browse the store to place your first order.</p>
      <a href="{{ route('customer.index') }}" class="btn primary">Start Shopping</a>
    </div>
  @else
    {{-- Orders table --}}
    <div class="card">
      <table class="table">
        <thead>
          <tr>
            <th style="width:84px">Order</th>
            <th style="width:160px">Date</th>
            <th style="width:140px">Status</th>
            <th style="width:140px">Total (RM)</th>
            <th>Shipping Address</th>
            <th class="right" style="width:220px">Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach($orders as $o)
            @php
              $processing = ($o->status === 'Processing');
              $firstItem = optional($o->items)->first();
              $cover = $firstItem?->book?->cover_image_url;
            @endphp
            <tr>
              {{-- Order id + tiny cover hint --}}
              <td>
                <div class="row" style="gap:8px;align-items:center">
                  <div style="width:34px;height:48px;border-radius:6px;border:1px solid #1c2346;background:#0f1533;overflow:hidden;display:flex;align-items:center;justify-content:center">
                    @if($cover)
                      <img src="{{ $cover }}" alt="Cover" style="width:100%;height:100%;object-fit:cover">
                    @else
                      <span class="muted" style="font-size:10px">—</span>
                    @endif
                  </div>
                  <div style="font-weight:600">#{{ $o->id }}</div>
                </div>
              </td>

              <td>{{ optional($o->order_date)->format('Y-m-d H:i') }}</td>

              <td>
                <span class="pill" style="{{ $statusStyle($o->status) }}">
                  {{ $o->status }}
                </span>
              </td>

              <td><strong>RM {{ number_format($o->total_amount,2) }}</strong></td>

              <td>
                <div class="muted" style="white-space:pre-wrap;max-width:520px">{{ $o->shipment->shipping_address ?? '—' }}</div>

                {{-- Change address (only when processing) --}}
                @if($processing)
                  <details style="margin-top:8px">
                    <summary class="muted" style="cursor:pointer">Change address</summary>
                    <form method="POST" action="{{ route('orders.address', $o) }}" class="mt" style="display:grid;gap:8px;max-width:560px">
                      @csrf @method('PATCH')
                      <textarea name="shipping_address" rows="2" required class="input">{{ $o->shipment->shipping_address ?? '' }}</textarea>
                      <div>
                        <button class="btn">Save</button>
                      </div>
                    </form>
                  </details>
                @endif
              </td>

              <td class="right">
                <div class="row" style="gap:8px;justify-content:flex-end;flex-wrap:wrap">
                  <a href="{{ route('orders.show', $o) }}" class="pill">View</a>

                  @if($processing)
                    <form method="POST" action="{{ route('orders.cancel', $o) }}" onsubmit="return confirm('Cancel this order?')">
                      @csrf @method('PATCH')
                      <button class="btn danger">Cancel</button>
                    </form>
                  @endif
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>

      {{-- Pagination --}}
      <div class="mt">
        {{ $orders->links() }}
      </div>
    </div>
  @endif

  {{-- Bottom back link (kept for convenience) --}}
  <div class="row" style="justify-content:flex-end">
    <a href="{{ route('customer.index') }}" class="pill">← Back to Shop</a>
  </div>
</div>
@endsection
