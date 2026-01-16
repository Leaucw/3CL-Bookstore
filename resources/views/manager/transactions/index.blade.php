{{-- Author: Chai Hao Lun--}}

@extends('layouts.app')
@section('title','Manager • Transactions')

@section('content')
<div class="grid" style="gap:16px">

  {{-- Stats --}}
  <div class="grid grid-3">
    <div class="card">
      <div class="muted">Total Transactions</div>
      <div style="font-size:24px;font-weight:700">{{ number_format($stats['count']) }}</div>
    </div>
    <div class="card">
      <div class="muted">Net Payments</div>
      <div style="font-size:24px;font-weight:700">RM {{ number_format($stats['payments'],2) }}</div>
    </div>
    <div class="card">
      <div class="muted">Total Refunds</div>
      <div style="font-size:24px;font-weight:700">RM {{ number_format($stats['refunds'],2) }}</div>
    </div>
  </div>

  {{-- Filters --}}
  <div class="card">
    <div class="right row" style="gap:8px">
      <a class="pill" href="{{ route('manager.dashboard') }}">← Back to Dashboard</a>
    </div>
    <h3 style="margin:0 0 12px"></h3>
    {{-- Search & filter form --}}
    <form class="row" method="get" action="{{ route('manager.transactions.index') }}">
      <input class="input" name="q" value="{{ request('q') }}" placeholder="Search: Tx ID / Order ID / Name / Email" style="flex:1;min-width:260px">
      <select class="input" name="type" style="max-width:200px">
        <option value="">All types</option>
        <option value="Payment" {{ request('type')==='Payment'?'selected':'' }}>Payment</option>
        <option value="Refund"  {{ request('type')==='Refund' ?'selected':'' }}>Refund</option>
      </select>
      <input class="input" type="date" name="from" value="{{ request('from') }}" style="max-width:160px">
      <input class="input" type="date" name="to"   value="{{ request('to')   }}" style="max-width:160px">
      <button class="btn">Filter</button>
      <a class="pill right" href="{{ route('manager.transactions.index') }}">Reset</a>
    </form>
  </div>

  {{-- Table --}}
  <div class="card">
    <table class="table">
      <thead>
        <tr>
          <th>Tx</th>
          <th>Order</th>
          <th>Customer</th>
          <th>Date</th>
          <th>Type</th>
          <th class="right">Amount (RM)</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @forelse($tx as $row)
          @php
            $badge = $row->transaction_type === 'Payment'
              ? 'background:#0d1d13;border-color:#1d3f2a;color:#c7f0d1'
              : 'background:#241012;border-color:#3e1d1d;color:#f2c8c8';
          @endphp
          <tr>
            <td>#{{ $row->id }}</td>
            <td>
              #{{ $row->order_id }}
              <div class="muted" style="font-size:12px">{{ $row->order?->status ?? '—' }}</div>
            </td>
            <td>
              {{ $row->order?->user?->name ?? '—' }}
              <div class="muted" style="font-size:12px">{{ $row->order?->user?->email ?? '' }}</div>
            </td>
            <td>{{ optional($row->transaction_date)->format('Y-m-d H:i') }}</td>
            <td><span class="pill" style="{{ $badge }}">{{ $row->transaction_type }}</span></td>
            <td class="right" style="font-weight:600">RM {{ number_format($row->amount,2) }}</td>
            <td class="right">
              <a class="btn" href="{{ route('manager.transactions.show', $row) }}">View</a>
            </td>
          </tr>
        @empty
          <tr><td colspan="7" class="center muted">No transactions found.</td></tr>
        @endforelse
      </tbody>
    </table>

    <div class="mt">
      {{ $tx->links() }}
    </div>
  </div>
</div>
@endsection