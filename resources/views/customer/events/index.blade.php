{{-- Author: Chan Kah Wei --}}
 */@extends('layouts.app')
@section('title','Events')
@section('content')
<div class="wrap">
  <a href="{{ route('customer.dashboard') }}" class="pill">Back</a>
  <h2>Upcoming Events</h2>
  <div class="grid grid-3">
    @forelse($events as $e)
      <div class="card">
        {{-- Show event image if available --}}
        @if($e->image)
          <div class="mb-2">
            <label>Event Image</label>
            <div class="row" style="gap:10px;align-items:center;flex-wrap:wrap">
              <img src="{{ asset('storage/events/'.$e->image) }}" 
                   alt="Event image" 
                   style="height:120px;border-radius:10px;border:1px solid #1c2346">
              <span class="muted">Stored file: {{ $e->image }}</span>
            </div>
          </div>
        @endif

        <h3>{{ $e->title }}</h3>
        <p class="muted">{{ $e->starts_at?->format('M d, Y H:i') }}</p>
        <p>{{ Str::limit($e->description,100) }}</p>
        <a class="btn" href="{{ route('cust.events.show',$e) }}">View Details</a>
      </div>
    @empty
      <p class="muted">No events available.</p>
    @endforelse
  </div>
  <div class="mt">{{ $events->links() }}</div>
</div>
@endsection
