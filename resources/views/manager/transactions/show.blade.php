{{-- Author: Chai Hao Lun--}}
@extends('layouts.app')
@section('title', 'Transaction #'.$tx->id)

@section('content')
<div class="grid" style="gap:16px">

  {{-- Transaction header --}}
  <div class="card">
    <div class="row" style="justify-content:space-between">
      <div>
        <h3 style="margin:0">Transaction #{{ $tx->id }}</h3>
        <div class="muted">On {{ $tx->transaction_date?->format('Y-m-d H:i') }}</div>
        <div class="mt">
          <span class="pill">Type: {{ $tx->transaction_type }}</span>
          <span class="pill">Amount: RM {{ number_format($tx->amount,2) }}</span>
        </div>
      </div>
      <div class="right">
        <a class="btn" href="{{ route('manager.transactions.index') }}">Back to list</a>
      </div>
    </div>
  </div>

  {{-- Order summary --}}
  <div class="grid grid-2">
    <div class="card">
      <h3 style="margin:0 0 8px">Order</h3>
      @php $o = $tx->order; @endphp
      @if($o)
        <div class="row" style="justify-content:space-between">
          <div>
            <div><strong>#{{ $o->id }}</strong> · {{ $o->status }}</div>
            <div class="muted">Placed {{ $o->order_date?->format('Y-m-d H:i') }}</div>
            <div class="mt muted">Payment: {{ $o->payment_method }}</div>
          </div>
          <div class="right" style="text-align:right">
            <div class="muted">Subtotal</div>
            <div>RM {{ number_format($o->subtotal_amount,2) }}</div>
            <div class="muted mt">Discount</div>
            <div>- RM {{ number_format($o->discount_amount,2) }}</div>
            <div class="muted mt">Shipping</div>
            <div>RM {{ number_format($o->shipping_amount ?? 0,2) }}</div>
            <hr style="border-color:#1c2346">
            <div style="font-weight:700">Total RM {{ number_format($o->total_amount,2) }}</div>
          </div>
        </div>

        <hr style="border-color:#1c2346; margin:12px 0">

        {{-- Customer & shipping --}}
        <div class="grid grid-2">
          <div>
            <div class="muted">Customer</div>
            <div>{{ $o->user?->name ?? '—' }}</div>
            <div class="muted">{{ $o->user?->email ?? '' }}</div>
          </div>
          <div>
            <div class="muted">Shipping Address</div>
            <div class="mt" style="white-space:pre-wrap">{{ $o->shipment?->shipping_address ?? '—' }}</div>
          </div>
        </div>
      @else
        <div class="muted">Order not found.</div>
      @endif
    </div>

    <div class="card">
      <h3 style="margin:0 0 8px">Items</h3>
      @if($o && $o->items->count())
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
            @foreach($o->items as $it)
              <tr>
                <td>
                  {{ $it->book->title ?? ('#'.$it->book_id) }}
                  <div class="muted" style="font-size:12px">{{ $it->book->author ?? '' }}</div>
                </td>
                <td>{{ $it->quantity }}</td>
                <td>{{ number_format($it->unit_price,2) }}</td>
                <td class="right" style="font-weight:600">
                  RM {{ number_format($it->unit_price * $it->quantity,2) }}
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      @else
        <div class="muted">No items.</div>
      @endif
    </div>
  </div>

  {{-- All transactions under this order (audit) --}}
  <div class="card">
    <h3 style="margin:0 0 8px">Order Transactions</h3>
    @if($o && $o->transactions->count())
      <table class="table">
        <thead>
          <tr>
            <th>Tx</th>
            <th>Date</th>
            <th>Type</th>
            <th class="right">Amount (RM)</th>
          </tr>
        </thead>
        <tbody>
          @foreach($o->transactions as $row)
            <tr @class([''])>
              <td>#{{ $row->id }}</td>
              <td>{{ $row->transaction_date?->format('Y-m-d H:i') }}</td>
              <td>{{ $row->transaction_type }}</td>
              <td class="right" style="font-weight:600">RM {{ number_format($row->amount,2) }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @else
      <div class="muted">No transactions for this order.</div>
    @endif
  </div>
</div>
@endsection
