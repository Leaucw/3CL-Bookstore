{{-- Author: Chai Hao Lun--}}
@extends('layouts.app')
@section('title','Staff • Manage Orders')

@section('content')
<div class="grid" style="gap:16px">

  <div class="card">
    <div class="row" style="justify-content:space-between;align-items:center">
      <h2 style="margin:0">🧾 Orders</h2>
      <a href="{{ route('dashboard.staff') }}" class="pill">← Back to Staff Home</a>
    </div>
  </div>

  <div class="card">
    <form class="row" method="get" action="{{ route('staff.orders.index') }}">
      <input class="input" name="q" value="{{ request('q') }}" placeholder="Search: Order ID / Name / Email" style="flex:1;min-width:240px">
      <select class="input" name="status" style="max-width:200px">
        <option value="">All statuses</option>
        @foreach(['Processing','Shipped','Arrived','Completed','Cancelled'] as $st)
          <option value="{{ $st }}" @selected(request('status')===$st)>{{ $st }}</option>
        @endforeach
      </select>
      <input class="input" type="date" name="from" value="{{ request('from') }}" style="max-width:160px">
      <input class="input" type="date" name="to"   value="{{ request('to')   }}" style="max-width:160px">
      <button class="btn">Filter</button>
      <a class="pill right" href="{{ route('staff.orders.index') }}">Reset</a>
    </form>
  </div>

  <div class="card">
    <table class="table">
      <thead>
        <tr>
          <th>Order</th>
          <th>Date</th>
          <th>Customer</th>
          <th>Status</th>
          <th class="right">Total (RM)</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @forelse($orders as $o)
          @php
            $canShip     = $o->status === 'Processing';
            $canArrive   = $o->status === 'Shipped';
            $canComplete = $o->status === 'Arrived';
            $canCancel   = $o->status === 'Processing';
          @endphp
          <tr>
            <td>#{{ $o->id }}</td>
            <td>{{ optional($o->order_date)->format('Y-m-d H:i') }}</td>
            <td>
              {{ $o->user?->name ?? '—' }}
              <div class="muted" style="font-size:12px">{{ $o->user?->email ?? '' }}</div>
            </td>
            <td>
              <span class="pill">{{ $o->status }}</span>
            </td>
            <td class="right" style="font-weight:600">RM {{ number_format($o->total_amount,2) }}</td>
            <td class="right">
              <div class="row" style="gap:6px;justify-content:flex-end;flex-wrap:wrap">
                <a class="btn" href="{{ route('staff.orders.show', $o) }}">View</a>

                @if($canShip)
                  <form method="POST" action="{{ route('orders.ship', $o) }}">
                    @csrf @method('PATCH')
                    <button class="btn">Ship</button>
                  </form>
                @endif

                @if($canArrive)
                  <form method="POST" action="{{ route('orders.arrive', $o) }}">
                    @csrf @method('PATCH')
                    <button class="btn">Arrive</button>
                  </form>
                @endif

                @if($canComplete)
                  <form method="POST" action="{{ route('orders.complete', $o) }}">
                    @csrf @method('PATCH')
                    <button class="btn success">Complete</button>
                  </form>
                @endif

                @if($canCancel)
                  <form method="POST" action="{{ route('orders.cancel', $o) }}"
                        onsubmit="return confirm('Cancel this order?');">
                    @csrf @method('PATCH')
                    <button class="btn danger">Cancel</button>
                  </form>
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="center muted">No orders.</td></tr>
        @endforelse
      </tbody>
    </table>

    <div class="mt">
      {{ $orders->links() }}
    </div>
  </div>
</div>
@endsection
