{{-- resources/views/staff/dashboard.blade.php --}}
@extends('layouts.app')

@section('title','Staff')

@section('content')
@php
  // Threshold (server-side fallback; JS below also syncs via localStorage)
  $threshold = (int) request('low', 5);

  // Prefer controller-provided stats; else safe fallbacks here.
  $totalBooks    = $stats['totalBooks']  ?? (\App\Models\Book::count());
  $lowStockCount = $stats['lowStock']    ?? (\App\Models\Book::where('stock','<', $threshold)->count());
  $outOfStockCnt = $stats['outOfStock']  ?? (\App\Models\Book::where('stock','<=',0)->count());
  $categoryCount = $stats['categories']  ?? (\App\Models\Category::count());
@endphp

<div class="grid" style="gap:16px">

  {{-- Top bar --}}
  <div class="card">
    <div class="row" style="justify-content:space-between;align-items:center">
      <div class="row" style="gap:10px;align-items:center;flex-wrap:wrap">
        <h2 style="margin:0">👋 Staff Dashboard</h2>
        <span class="pill muted">Today: <span id="todayStr"></span></span>
        <span class="pill">Shift: {{ now()->format('h:i A') }} – {{ now()->copy()->addHours(8)->format('h:i A') }}</span>
      </div>
      <div class="row" style="gap:8px;flex-wrap:wrap">
        <a href="{{ route('books.create', request()->query()) }}" class="btn primary">➕ Add Book</a>
        <a href="{{ route('books.index',  request()->query()) }}" class="btn">📚 Inventory</a>
        <a href="{{ route('books.index', array_merge(request()->query(), ['low' => request('low', $threshold)])) }}" class="pill">⚠️ Low Stock</a>
      </div>
    </div>
  </div>

  {{-- KPIs --}}
  <div class="grid grid-4" style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px">
    <div class="card">
      <div class="muted">Total Books</div>
      <div style="font-size:28px;font-weight:700;margin-top:4px">{{ number_format($totalBooks) }}</div>
    </div>
    <div class="card" style="border-color:#3e2c1d;background:linear-gradient(180deg,#2a1a0e,#2f1e12)">
      <div class="muted">Low Stock (&lt; {{ $threshold }})</div>
      <div style="font-size:28px;font-weight:700;margin-top:4px">{{ number_format($lowStockCount) }}</div>
    </div>
    <div class="card" style="border-color:#3e1d1d;background:linear-gradient(180deg,#1a0e0e,#241012)">
      <div class="muted">Out of Stock</div>
      <div style="font-size:28px;font-weight:700;margin-top:4px">{{ number_format($outOfStockCnt) }}</div>
    </div>
    <div class="card">
      <div class="muted">Categories</div>
      <div style="font-size:28px;font-weight:700;margin-top:4px">{{ number_format($categoryCount) }}</div>
    </div>
  </div>

  {{-- Threshold only --}}
  <div class="card">
    <form method="GET" action="{{ route('dashboard.staff') }}" class="row" style="gap:10px;align-items:center">
      @foreach(request()->except(['low']) as $k => $v)
        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
      @endforeach
      <label class="muted">Low stock threshold</label>
      <input class="input" type="number" min="0" name="low" id="lowInput" value="{{ request('low', $threshold) }}" style="width:120px">
      <button class="btn">Apply</button>
      <span class="muted right">Affects KPI tiles and the Low Stock list.</span>
    </form>
  </div>

  <div class="grid grid-2">
    {{-- Low stock panel (simplified) --}}
    <div class="card">
      <div class="row" style="justify-content:space-between;align-items:center;margin-bottom:8px">
        <h3 style="margin:0">⚠️ Low Stock</h3>
        <a href="{{ route('books.index', array_merge(request()->query(), ['low'=>request('low', $threshold)])) }}" class="pill">View all</a>
      </div>

      @php
        $lowList = isset($lowStock)
          ? $lowStock
          : \App\Models\Book::select('id','title','author','stock')
              ->where('stock','<', $threshold)->orderBy('stock')->limit(8)->get();
      @endphp

      @if($lowList->count())
        <ul style="list-style:none;padding:0;margin:0">
          @foreach($lowList as $b)
            <li style="padding:10px 0;border-bottom:1px solid #202750">
              <div class="row" style="gap:10px;align-items:center">
                <div style="flex:1;min-width:200px">
                  <div style="font-weight:600">{{ $b->title }}</div>
                  @if(!empty($b->author))
                    <div class="muted" style="font-size:12px">{{ $b->author }}</div>
                  @endif
                </div>
                <span class="pill"
                      style="border-color:#3e2c1d;background:linear-gradient(180deg,#2a1a0e,#2f1e12);color:#fcd39b">
                  {{ (int)($b->stock ?? 0) }}
                </span>
              </div>
            </li>
          @endforeach
        </ul>
      @else
        <div class="muted">All good—no items under the threshold.</div>
      @endif
    </div>

    {{-- Right column: Quick actions, Announcements, Report Analysis --}}
    <div class="grid" style="gap:16px">
      <div class="card">
        <h3 style="margin:0 0 8px">⚙️ Quick Actions</h3>
        <div class="row" style="gap:8px;flex-wrap:wrap">
          <a href="{{ route('books.create', request()->query()) }}" class="btn primary">Add Book</a>
          <a href="{{ route('books.index', array_merge(request()->query(), ['low'=>request('low', $threshold)])) }}" class="btn">Reorder Low Stock</a>
          <a href="{{ route('books.index', request()->query()) }}" class="pill">Manage Inventory</a>
          <a href="{{ route('staff.orders.index') }}" class="btn">🧾 Manage Orders</a>
        </div>
      </div>
      <div class="card">
      <h3 style="margin:0 0 8px">✨ Manage Community</h3>
      <div class="row" style="gap:8px;flex-wrap:wrap">
        <a href="{{ route('staff.events.index') }}" class="btn">📅 Manage Events</a>
        <a href="{{ route('staff.ann.index') }}" class="btn">📢 Manage Announcements</a>
      </div>
    </div>


      <div class="card">
        <h3 style="margin:0 0 8px">📢 Announcements</h3>
        @php
          use App\Models\Announcement;
          $announcements = \App\Models\Announcement::latest()->take(5)->get();
        @endphp

        @if($announcements->count())
          <ul style="margin:0;padding-left:18px;list-style:none">
            @foreach($announcements as $a)
              <li style="margin-bottom:10px;border-bottom:1px solid #202750;padding:6px 0">
                <strong>{{ $a->title }}</strong><br>
                <span class="muted" style="font-size:13px">
                  {{ $a->body }}
                </span>
              </li>
            @endforeach
          </ul>
        @else
          <div class="muted">No announcements yet.</div>
        @endif
      </div>

      {{-- 📈 Report Analysis --}}
      @php
        $reportAgg = $reportAgg ?? \App\Models\Book::selectRaw(
            'COALESCE(SUM(stock),0) as units,
             COALESCE(SUM(stock * price),0) as inv_value,
             COALESCE(AVG(price),0) as avg_price'
          )->first();

        $units    = (int)   ($reportAgg->units      ?? 0);
        $invValue = (float) ($reportAgg->inv_value  ?? 0.0);
        $avgPrice = (float) ($reportAgg->avg_price  ?? 0.0);
        $lowShare = $totalBooks > 0 ? round(($lowStockCount / $totalBooks) * 100, 1) : 0.0;

        $addedThisMonth   = $addedThisMonth   ?? \App\Models\Book::whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count();
        $updatedThisWeek  = $updatedThisWeek  ?? \App\Models\Book::whereBetween('updated_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
      @endphp

      <div class="card">
        <h3 style="margin:0 0 8px">📈 Report Analysis</h3>

        <div class="grid" style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px">
          <div class="card" style="padding:14px">
            <div class="muted">Units in Stock</div>
            <div style="font-size:22px;font-weight:700;margin-top:2px">{{ number_format($units) }}</div>
          </div>
          <div class="card" style="padding:14px">
            <div class="muted">Inventory Value</div>
            <div style="font-size:22px;font-weight:700;margin-top:2px">RM {{ number_format($invValue, 2) }}</div>
          </div>
          <div class="card" style="padding:14px">
            <div class="muted">Avg. Price / Book</div>
            <div style="font-size:22px;font-weight:700;margin-top:2px">RM {{ number_format($avgPrice, 2) }}</div>
          </div>
          <div class="card" style="padding:14px">
            <div class="muted">Low-Stock Share (&lt; {{ $threshold }})</div>
            <div class="row" style="align-items:center;gap:8px;margin-top:4px">
              <strong style="font-size:18px">{{ $lowShare }}%</strong>
              <div style="flex:1;height:8px;background:#202750;border-radius:999px;overflow:hidden">
                <div style="height:8px;width:{{ $lowShare }}%;background:linear-gradient(135deg,#4f46e5,#06b6d4)"></div>
              </div>
            </div>
          </div>
        </div>

        <div class="row" style="gap:12px;margin-top:12px;flex-wrap:wrap">
          <span class="pill muted">Added this month: <strong style="margin-left:4px">{{ number_format($addedThisMonth) }}</strong></span>
          <span class="pill muted">Updated this week: <strong style="margin-left:4px">{{ number_format($updatedThisWeek) }}</strong></span>
        </div>

        <div class="row" style="justify-content:flex-end;margin-top:12px;gap:8px">
          <a href="{{ route('books.index', request()->query()) }}" class="pill">Open Inventory</a>
          <a href="{{ route('books.index', array_merge(request()->query(), ['low'=>request('low', $threshold)])) }}" class="pill">See Low Stock</a>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  // Pretty date for "Today:"
  const d = new Date();
  const fmt = new Intl.DateTimeFormat(undefined, { weekday:'short', year:'numeric', month:'short', day:'numeric' });
  const el = document.getElementById('todayStr');
  if (el) el.textContent = fmt.format(d);

  // Threshold persistence (localStorage)
  (function () {
    const KEY = 'books.lowThreshold';
    const url = new URL(window.location.href);
    const qp  = url.searchParams;
    if (!qp.has('low')) {
      const saved = localStorage.getItem(KEY);
      if (saved !== null) {
        qp.set('low', saved);
        window.location.replace(url.toString());
        return;
      }
    }
    const input = document.getElementById('lowInput');
    if (input) {
      input.addEventListener('change', () => {
        localStorage.setItem(KEY, input.value || '5');
      });
    }
  })();
</script>
@endsection
