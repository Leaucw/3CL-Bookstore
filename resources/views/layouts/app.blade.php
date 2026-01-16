<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>@yield('title','Bookstore')</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <style>
    :root{
      --bg:#0b1020; --card:#121836; --muted:#9aa4c2; --text:#e5e7eb;
      --brand:#4f46e5; --brand-2:#06b6d4; --ok:#22c55e; --warn:#f59e0b; --danger:#ef4444;
      --ring:#6366f1; --shadow:0 10px 30px rgba(0,0,0,.35); --radius:14px;
    }
    *{box-sizing:border-box}
    body{margin:0;background:linear-gradient(120deg,#0b1020,#11172f) fixed;color:var(--text);
      font-family: system-ui,-apple-system,Segoe UI,Roboto,Arial}
    a{color:#a5b4fc;text-decoration:none} a:hover{color:#c7d2fe;text-decoration:underline}
    header{display:flex;align-items:center;justify-content:space-between;padding:18px 24px;
      border-bottom:1px solid #1f2547;background:rgba(9,13,28,.7);backdrop-filter:blur(6px);
      position:sticky;top:0;z-index:20}
    .brand{display:flex;gap:10px;align-items:center}
    .logo{width:28px;height:28px;border-radius:8px;background:linear-gradient(135deg,#4f46e5,#06b6d4)}
    .wrap{max-width:1000px;margin:32px auto;padding:0 18px}
    .card{background:linear-gradient(180deg,#121836,#0f1630);border:1px solid #1c2346;box-shadow:var(--shadow);
      border-radius:var(--radius);padding:24px}
    .grid{display:grid;gap:16px}
    .grid-2{grid-template-columns:repeat(2,1fr)}
    .grid-3{grid-template-columns:repeat(3,1fr)}
    .btn{display:inline-flex;align-items:center;gap:8px;padding:10px 16px;border-radius:10px;border:1px solid #2a3263;
      background:#171f42;color:#e5e7eb;cursor:pointer;transition:.15s}
    .btn:hover{transform:translateY(-1px);border-color:#39408a}
    .btn.primary{background:linear-gradient(135deg,#4f46e5,#06b6d4);border:none}
    .btn.success{background:linear-gradient(135deg,#16a34a,#22c55e);border:none}
    .btn.danger{background:linear-gradient(135deg,#ef4444,#f97316);border:none}
    .pill{display:inline-block;padding:6px 12px;border-radius:999px;border:1px solid #2a3263;background:#0f1533;font-size:13px}
    /* red variant for pill buttons */
    .pill-danger{
      background: var(--danger);
      border-color: var(--danger);
      color:#fff;
    }
    .pill-danger:hover{
      filter: brightness(0.92);
    }
    .input{width:100%;padding:12px 14px;border:1px solid #2a3263;background:#0f1533;color:var(--text);border-radius:10px;outline:none}
    .input:focus{border-color:var(--ring);box-shadow:0 0 0 3px rgba(99,102,241,.25)}
    label{display:block;font-size:14px;color:#cbd5e1;margin:8px 0 6px}
    .muted{color:var(--muted);font-size:14px}
    .row{display:flex;gap:12px;align-items:center;flex-wrap:wrap}
    .right{margin-left:auto}
    .mt{margin-top:16px}.mb{margin-bottom:16px}.center{text-align:center}

    .table{width:100%;border-collapse:collapse;border-radius:12px;overflow:hidden}
    .table th,.table td{padding:12px 10px;border-bottom:1px solid #202750}
    .table th{font-weight:600;color:#cbd5e1;text-align:left;background:#10173a}
    .table tr:hover td{background:#0f1533}

    /* Toasts */
    .toast-wrap{position:fixed;right:16px;top:16px;display:flex;flex-direction:column;gap:10px;z-index:50}
    .toast{min-width:260px;max-width:440px;padding:12px 14px;border-radius:12px;box-shadow:var(--shadow);
      display:flex;align-items:flex-start;gap:10px;border:1px solid #1d2345;background:#0f1533;opacity:0;transform:translateY(-8px);animation:slideIn .25s forwards}
    .toast.success{border-color:#1d3f2a;background:linear-gradient(180deg,#0d1d13,#11271a)}
    .toast.error{border-color:#3e1d1d;background:linear-gradient(180deg,#1a0e0e,#241012)}
    .toast.info{border-color:#1d2d45;background:linear-gradient(180deg,#0d1423,#0f1628)}
    .toast .title{font-weight:600}
    .close{margin-left:auto;background:transparent;border:none;color:#9aa4c2;cursor:pointer}
    @keyframes slideIn{to{opacity:1;transform:translateY(0)}}

    /* Pagination quick style */
    nav[role="navigation"] > div > span, nav[role="navigation"] a{
      display:inline-block;margin-right:6px;padding:6px 10px;border-radius:8px;border:1px solid #2a3263;background:#0f1533;color:#cbd5e1
    }
    nav[role="navigation"] a:hover{border-color:#39408a}
  </style>
</head>
<body>
<header>
  <div class="brand">
    <div class="logo"></div>
    <strong>Bookstore</strong>

    {{-- ▼ Add this: show only for logged-in customers --}}
    @auth
      @if(auth()->user()->role === 'customer')
        <div class="row" style="margin-left:12px">
          <a href="{{ route('orders.index') }}" class="pill" title="My Orders">📦 My Orders</a>
          <a href="{{ route('cart.index') }}" class="pill" title="Cart">🛒 Cart</a>
        </div>
      @endif
    @endauth
    {{-- ▲ End added --}}
  </div>

  <nav class="row">
    @auth
      @if(auth()->user()->role === 'manager')
        {{-- Manager: ONLY Manage Users + Logout --}}
        <a href="{{ route('manager.users.index') }}" class="pill">Manage Users</a>
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button class="btn">Logout</button>
        </form>
      @else
        {{-- Staff/Customer: show Profile + Logout --}}
        <a href="{{ route('profile.edit') }}" class="pill">Profile</a>
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button class="btn">Logout</button>
        </form>
      @endif
    @else
      <a href="{{ route('register') }}" class="btn success">Register</a>
      <a href="{{ route('login.manager') }}" class="pill">Manager</a>
      <a href="{{ route('login.staff') }}" class="pill">Staff</a>
      <a href="{{ route('login.customer') }}" class="pill">Customer</a>
    @endauth
  </nav>
</header>


<div class="wrap">@yield('content')</div>

{{-- Toasts --}}
<div class="toast-wrap" id="toasts">
  @if(session('flash.success'))
    <div class="toast success">
      <div><div class="title">Success</div><div>{{ session('flash.success') }}</div></div>
      <button class="close" onclick="this.closest('.toast').remove()">✕</button>
    </div>
  @endif
  @if(session('flash.error'))
    <div class="toast error">
      <div><div class="title">Error</div><div>{{ session('flash.error') }}</div></div>
      <button class="close" onclick="this.closest('.toast').remove()">✕</button>
    </div>
  @endif
  @if(session('flash.info'))
    <div class="toast info">
      <div><div class="title">Info</div><div>{{ session('flash.info') }}</div></div>
      <button class="close" onclick="this.closest('.toast').remove()">✕</button>
    </div>
  @endif
</div>

<script>
  // auto dismiss toasts
  addEventListener('load',()=> {
    document.querySelectorAll('.toast').forEach(t=> setTimeout(()=> t.remove(), 3200));
    // simple confirm hook
    document.querySelectorAll('[data-confirm]').forEach(btn=>{
      btn.addEventListener('click', e=>{
        if(!confirm(btn.getAttribute('data-confirm'))) e.preventDefault();
      });
    });
  });
</script>
</body>
</html>
