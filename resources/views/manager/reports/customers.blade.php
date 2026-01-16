{{-- Author: Chiang Jong Sheng --}}
@extends('layouts.app')
@section('title','Customer Registrations • '.$monthName)

@section('content')
<div class="card">
  <div class="row">
    <h3 style="margin:0">Customer Registrations ({{ $monthName }})</h3>
    <div class="right row" style="gap:8px">
      <a class="pill" href="{{ route('manager.dashboard') }}">← Back to Dashboard</a>
    </div>
  </div>

  {{-- Month picker --}}
  <form method="GET" class="row mt" style="gap:10px">
    <label class="muted">Select month</label>
    <input class="input" type="month" name="month" value="{{ $monthParam }}">
    <button class="btn" type="submit">Apply</button>
  </form>

  {{-- KPIs --}}
  <div class="grid grid-3 mt">
    <div class="card">
      <div class="muted">Total customers this month</div>
      <div style="font-size:28px;font-weight:700;margin-top:6px">{{ $total }}</div>
    </div>
    <div class="card">
      <div class="muted">First day</div>
      <div style="font-size:18px;margin-top:6px">{{ $start->format('Y-m-d') }}</div>
    </div>
    <div class="card">
      <div class="muted">Last day</div>
      <div style="font-size:18px;margin-top:6px">{{ $end->format('Y-m-d') }}</div>
    </div>
  </div>

  {{-- Mini bar chart (pure CSS/HTML) --}}
  <div class="card mt">
    <div class="row" style="justify-content:space-between;align-items:center">
      <div class="muted">Daily registrations</div>
      <div class="muted" style="font-size:12px">Max/day: {{ $max }}</div>
    </div>

    <div style="margin-top:12px; border:1px solid #1c2346; border-radius:12px; padding:12px">
      <div style="display:flex; align-items:flex-end; gap:6px; height:180px;">
        @foreach($data as $i => $count)
          @php
            $h = intval(($count / $max) * 160); // 160px max bar height
          @endphp
          <div title="Day {{ $labels[$i] }}: {{ $count }}"
               style="width:18px; height:{{ $h }}px; background:linear-gradient(180deg,#4f46e5,#06b6d4);
                      border-radius:6px 6px 0 0; border:1px solid #2a3263; box-shadow:0 2px 6px rgba(0,0,0,.25)">
          </div>
        @endforeach
      </div>
      {{-- X axis labels every ~5 days for readability --}}
      <div style="display:flex; gap:6px; margin-top:6px">
        @foreach($labels as $i => $d)
          <div style="width:18px; text-align:center; font-size:10px; color:#9aa4c2">
            @if($i % 5 === 0) {{ $d }} @endif
          </div>
        @endforeach
      </div>
    </div>

    {{-- Table view --}}
    <div class="mt">
      <table class="table">
        <thead><tr><th style="width:120px">Date</th><th>New customers</th></tr></thead>
        <tbody>
          @foreach($data as $i => $count)
            <tr>
              <td>{{ $start->copy()->addDays($i)->format('Y-m-d') }}</td>
              <td>{{ $count }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

  </div>
</div>
@endsection
