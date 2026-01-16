{{-- resources/views/customer/dashboard.blade.php --}}
@extends('layouts.app')

@section('title','Customer')

@section('content')
@php
  use App\Models\Book;
  use App\Models\Category;
  use App\Models\Event as EventModel;

  // SIMPLE FALLBACKS (move to controller later if you prefer)
  $newArrivals = $newArrivals
      ?? Book::whereNull('deleted_at')->orderByDesc('created_at')->limit(6)->get();

  $popular = $popular
      ?? Book::whereNull('deleted_at')->orderByDesc('updated_at')->limit(6)->get();

  $categories = $categories
      ?? Category::orderBy('name')->limit(8)->get(['id','name']);

  $totalBooks = $totalBooks ?? Book::whereNull('deleted_at')->count();

  // Events as promos (latest 5 with images)
  $eventPromos = EventModel::whereNotNull('image_path')
      ->orderByDesc('starts_at')
      ->take(5)
      ->get(['id','title','slug','image_path']);

  $promos = $eventPromos->count()
      ? $eventPromos->map(fn($e) => [
          'img' => asset('storage/'.$e->image_path),
          'url' => route('cust.events.show', $e->slug),
          'alt' => $e->title,
        ])
      : collect([
          ['img' => asset('images/promos/merdeka-15.jpg'), 'url' => route('cust.events.index'), 'alt' => '15% Off All Books + RM5 e-Voucher'],
          ['img' => asset('images/promos/indie-week.jpg'), 'url' => route('cust.events.index'), 'alt' => 'Indie Week • Staff Picks'],
          ['img' => asset('images/promos/back-to-school.jpg'), 'url' => route('cust.events.index'), 'alt' => 'Back to School Essentials'],
          ['img' => asset('images/promos/author-spotlight.jpg'), 'url' => route('cust.events.index'), 'alt' => 'Author Spotlight'],
        ]);
@endphp

<style>
  /* --- micro-animations / transitions --- */
  .hover-lift { transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease; }
  .hover-lift:hover { transform: translateY(-3px); box-shadow: 0 14px 34px rgba(0,0,0,.45); border-color:#2a3263; }

  .img-zoom img { transition: transform .28s ease; transform-origin: center; }
  .shelf-card:hover .img-zoom img { transform: scale(1.06); }

  .category-pill { transition: background .18s ease, transform .18s ease, border-color .18s ease; }
  .category-pill:hover { transform: translateY(-2px); border-color:#39408a; }

  /* reveal on scroll */
  .reveal { opacity: 0; transform: translateY(10px); }
  .reveal.show { opacity: 1; transform: none; transition: opacity .45s ease, transform .45s ease; }

  /* gentle floating hero badge */
  @keyframes floatY {
    0% { transform: translateY(0) rotate(0deg); }
    50% { transform: translateY(-6px) rotate(1deg); }
    100% { transform: translateY(0) rotate(0deg); }
  }
  .float-y { animation: floatY 6s ease-in-out infinite; }

  /* ===== Promo carousel styles ===== */
  .promo { position: relative; margin-bottom: 14px; }
  .promo-track {
    display: grid;
    grid-auto-flow: column;
    grid-auto-columns: 88%;
    gap: 14px;
    overflow-x: auto;
    scroll-snap-type: x mandatory;
    scroll-behavior: smooth;
    padding: 2px 6px 14px; /* room for dots */
  }
  @media (min-width: 640px) { .promo-track { grid-auto-columns: 80%; } }
  @media (min-width: 1024px){ .promo-track { grid-auto-columns: 68%; } }

  .promo-slide {
    scroll-snap-align: center;
    display: block;
    border-radius: var(--radius);
    border: 1px solid #1c2346;
    box-shadow: var(--shadow);
    background: #0f1533;
    overflow: hidden;
    transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
  }
  .promo-slide:hover { transform: translateY(-2px); border-color:#2a3263; }

  .promo-img {
    width: 100%;
    padding-top: 34%;        /* banner-ish aspect ratio */
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
  }

  .promo-btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    border: 1px solid #2a3263;
    background: #171f42;
    color: var(--text);
    width: 36px; height: 36px;
    border-radius: 50%;
    display: grid; place-items: center;
    cursor: pointer;
    box-shadow: var(--shadow);
    transition: transform .15s ease, border-color .15s ease, background .15s ease, opacity .15s ease;
    opacity: .9;
  }
  .promo-btn:hover { transform: translateY(-50%) scale(1.05); border-color:#39408a; }
  .promo-prev { left: 8px; }
  .promo-next { right: 8px; }

  .promo-dots { display:flex; gap:6px; justify-content:center; align-items:center; margin-top:8px; }
  .promo-dot {
    width: 7px; height: 7px; border-radius:999px;
    background:#2a3263; transition: background .2s ease, width .2s ease, transform .2s ease;
  }
  .promo-dot.active {
    background: linear-gradient(135deg, var(--brand), var(--brand-2));
    width: 18px; transform: translateY(-1px);
  }

  .promo-track::-webkit-scrollbar { height: 8px; }
  .promo-track::-webkit-scrollbar-track { background:#0f1533; border-radius:8px; }
  .promo-track::-webkit-scrollbar-thumb { background:#1c2346; border-radius:8px; }

  .sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0;}
</style>

<div class="grid" style="gap:16px">

  {{-- Promo carousel --}}
  <div class="promo" id="promoHero">
    <div class="promo-track" id="promoTrack" tabindex="0" role="region" aria-roledescription="carousel" aria-label="Store promotions">
      @foreach($promos as $p)
        <a class="promo-slide" href="{{ $p['url'] ?? '#' }}" aria-label="{{ $p['alt'] ?? 'Promotion' }}">
          <div class="promo-img" style="background-image:url('{{ $p['img'] ?? '' }}')"></div>
          <span class="sr-only">{{ $p['alt'] ?? '' }}</span>
        </a>
      @endforeach
    </div>
    <button type="button" class="promo-btn promo-prev" aria-label="Previous" data-dir="-1">❮</button>
    <button type="button" class="promo-btn promo-next" aria-label="Next" data-dir="1">❯</button>
    <div class="promo-dots" id="promoDots" aria-hidden="true"></div>
  </div>

  {{-- Hero --}}
  <div class="card hover-lift reveal" style="display:flex;align-items:center;justify-content:space-between;gap:18px;flex-wrap:wrap">
    <div>
      <h2 style="margin:0 0 6px 0">Welcome to <span style="color:#a5b4fc">Bookstore</span> 📚</h2>
      <p class="muted" style="max-width:620px;margin:0">
        Fresh picks, trending titles, and classics you’ll love.
      </p>
      <div class="row mt" style="gap:10px;flex-wrap:wrap">
        <a href="{{ route('customer.index') }}" class="btn primary hover-lift">Browse All Books</a>
        <span class="pill">Over {{ number_format($totalBooks) }} titles</span>
      </div>
    </div>

    <div class="float-y" style="width:240px;height:120px;border-radius:14px;border:1px solid #1c2346;
                background:linear-gradient(135deg,rgba(79,70,229,.25),rgba(6,182,212,.2));
                box-shadow:var(--shadow);display:flex;align-items:center;justify-content:center;font-size:42px;">
      📖
    </div>
  </div>

  {{-- Quick Categories --}}
  <div class="card reveal">
    <div class="row" style="justify-content:space-between;align-items:center;margin-bottom:8px">
      <h3 style="margin:0">Categories</h3>
      <a href="{{ route('customer.index') }}" class="pill hover-lift">See all</a>
    </div>
    @if($categories->count())
      <div class="row" style="gap:8px;flex-wrap:wrap">
        @foreach($categories as $c)
          <a href="{{ route('customer.index', ['category' => $c->id]) }}"
             class="pill category-pill">{{ $c->name }}</a>
        @endforeach
      </div>
    @else
      <div class="muted">No categories yet.</div>
    @endif
  </div>

  {{-- Shelf: New Arrivals --}}
  <div class="card reveal">
    <div class="row" style="justify-content:space-between;align-items:center;margin-bottom:8px">
      <h3 style="margin:0">🆕 New Arrivals</h3>
      <a href="{{ route('customer.index') }}" class="pill hover-lift">View all</a>
    </div>

    @if($newArrivals->count())
      <div class="grid grid-3" style="grid-template-columns:repeat(3,1fr);gap:14px">
        @foreach($newArrivals as $b)
          <a href="{{ route('customer.show', $b) }}" class="card shelf-card hover-lift" style="padding:14px;display:block">
            <div class="row" style="gap:12px;align-items:flex-start">
              {{-- Cover --}}
              <div class="img-zoom" style="width:70px;height:100px;border-radius:10px;border:1px solid #1c2346;
                          background:#0f1533;overflow:hidden;flex:0 0 70px;display:flex;align-items:center;justify-content:center">
                @if(!empty($b->cover_image_url))
                  <img src="{{ $b->cover_image_url }}" alt="{{ $b->title }} cover" style="width:100%;height:100%;object-fit:cover">
                @else
                  <span class="muted" style="font-size:12px">No cover</span>
                @endif
              </div>
              {{-- Meta --}}
              <div style="min-width:0">
                <div style="font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                  {{ $b->title }}
                </div>
                <div class="muted" style="font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                  {{ $b->author }}
                </div>
                <div class="mt" style="font-weight:600">RM {{ number_format($b->price, 2) }}</div>
              </div>
            </div>
          </a>
        @endforeach
      </div>
    @else
      <div class="muted">No new arrivals yet.</div>
    @endif
  </div>

  {{-- Shelf: Popular Now --}}
  <div class="card reveal">
    <div class="row" style="justify-content:space-between;align-items:center;margin-bottom:8px">
      <h3 style="margin:0">🔥 Popular Now</h3>
      <a href="{{ route('customer.index') }}" class="pill hover-lift">View all</a>
    </div>

    @if($popular->count())
      <div class="grid grid-3" style="grid-template-columns:repeat(3,1fr);gap:14px">
        @foreach($popular as $b)
          <a href="{{ route('customer.show', $b) }}" class="card shelf-card hover-lift" style="padding:14px;display:block">
            <div class="row" style="gap:12px;align-items:flex-start">
              {{-- Cover --}}
              <div class="img-zoom" style="width:70px;height:100px;border-radius:10px;border:1px solid #1c2346;
                          background:#0f1533;overflow:hidden;flex:0 0 70px;display:flex;align-items:center;justify-content:center">
                @if(!empty($b->cover_image_url))
                  <img src="{{ $b->cover_image_url }}" alt="{{ $b->title }} cover" style="width:100%;height:100%;object-fit:cover">
                @else
                  <span class="muted" style="font-size:12px">No cover</span>
                @endif
              </div>
              {{-- Meta --}}
              <div style="min-width:0">
                <div style="font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                  {{ $b->title }}
                </div>
                <div class="muted" style="font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                  {{ $b->author }}
                </div>
                <div class="mt" style="font-weight:600">RM {{ number_format($b->price, 2) }}</div>
              </div>
            </div>
          </a>
        @endforeach
      </div>
    @else
      <div class="muted">No popular titles yet.</div>
    @endif
  </div>

    {{-- Events & Announcements --}}
  <div class="card reveal">
    <div class="row" style="justify-content:space-between;align-items:center;margin-bottom:8px">
      <h3 style="margin:0">✨ Community</h3>
      <span class="muted">Stay connected with our bookstore</span>
    </div>
    <div class="grid grid-2" style="gap:14px">
      <a href="{{ route('cust.events.index') }}" class="card hover-lift" style="padding:14px;text-align:center">
        <h3 style="margin:0 0 6px 0">📅 Events</h3>
        <p class="muted" style="margin:0">Join book fairs, webinars, and more.</p>
        <div class="mt"><span class="btn primary">View Events</span></div>
      </a>
      <a href="{{ route('cust.ann.index') }}" class="card hover-lift" style="padding:14px;text-align:center">
        <h3 style="margin:0 0 6px 0">📢 Announcements</h3>
        <p class="muted" style="margin:0">Get the latest news and updates.</p>
        <div class="mt"><span class="btn primary">Read Announcements</span></div>
      </a>
    </div>
  </div>

  {{-- Simple callout --}}
  <div class="card hover-lift reveal" style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap">
    <div class="muted">Can’t find what you’re looking for?</div>
    <a href="{{ route('customer.index') }}" class="btn">Browse full catalog</a>
  </div>

</div>




<script>
  // Reveal-on-scroll using IntersectionObserver
  (function() {
    const els = document.querySelectorAll('.reveal');
    if (!('IntersectionObserver' in window) || !els.length) {
      els.forEach(el => el.classList.add('show')); // graceful fallback
      return;
    }
    const io = new IntersectionObserver((entries) => {
      entries.forEach(e => {
        if (e.isIntersecting) {
          e.target.classList.add('show');
          io.unobserve(e.target);
        }
      });
    }, { rootMargin: '0px 0px -10% 0px', threshold: 0.05 });
    els.forEach(el => io.observe(el));
  })();

  // Promo carousel: buttons, dots, swipe, autoplay
  (function(){
    const track = document.getElementById('promoTrack');
    const dotsWrap = document.getElementById('promoDots');
    if (!track || !dotsWrap) return;

    const slides = Array.from(track.children);
    let positions = slides.map(s => s.offsetLeft);
    let userInteracted = false;
    let timer = null;

    function refreshPositions(){ positions = slides.map(s => s.offsetLeft); }
    function idx() {
      const sl = track.scrollLeft;
      let best = 0, min = Infinity;
      positions.forEach((p, i) => { const d = Math.abs(p - sl); if (d < min) { min = d; best = i; } });
      return best;
    }
    function goTo(i){
      if (i < 0) i = 0;
      if (i >= slides.length) i = slides.length - 1;
      track.scrollTo({ left: positions[i], behavior: 'smooth' });
      updateDots(i);
    }

    function buildDots(){
      dotsWrap.innerHTML = '';
      slides.forEach((_, i) => {
        const dot = document.createElement('div');
        dot.className = 'promo-dot';
        dot.addEventListener('click', () => { userInteracted = true; goTo(i); });
        dotsWrap.appendChild(dot);
      });
      updateDots();
    }

    function updateDots(active = idx()){
      Array.from(dotsWrap.children).forEach((d,i)=> d.classList.toggle('active', i === active));
    }

    // Buttons
    document.querySelectorAll('#promoHero .promo-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        userInteracted = true;
        const dir = Number(btn.getAttribute('data-dir')) || 1;
        goTo(idx() + dir);
      });
    });

    // Keyboard
    track.addEventListener('keydown', (e) => {
      if (e.key === 'ArrowRight' || e.key === 'PageDown') {
        e.preventDefault(); userInteracted = true; goTo(idx() + 1);
      } else if (e.key === 'ArrowLeft' || e.key === 'PageUp') {
        e.preventDefault(); userInteracted = true; goTo(idx() - 1);
      }
    });

    // Sync on scroll/resize
    track.addEventListener('scroll', () => requestAnimationFrame(updateDots));
    window.addEventListener('resize', () => { refreshPositions(); updateDots(); });
    window.addEventListener('load',   () => { refreshPositions(); updateDots(); });

    // Autoplay (5s), pause on hover or after user interaction
    function startAuto(){
      stopAuto();
      timer = setInterval(() => {
        if (userInteracted) return;
        let next = idx() + 1;
        if (next >= slides.length) next = 0;
        goTo(next);
      }, 5000);
    }
    function stopAuto(){ if (timer) clearInterval(timer), timer = null; }

    track.addEventListener('mouseenter', stopAuto);
    track.addEventListener('mouseleave', startAuto);
    track.addEventListener('touchstart', () => { userInteracted = true; stopAuto(); }, { passive:true });

    buildDots();
    startAuto();
  })();
</script>
@endsection
