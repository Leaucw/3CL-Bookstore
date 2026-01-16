{{-- Author: Chan Kah Wei --}}
@extends('layouts.app')
@section('title','Announcements')
@section('content')
<div class="wrap">
  <a href="{{ route('customer.dashboard') }}" class="pill">Back</a>
  <h2>Latest Announcements</h2>
  <div class="grid grid-2">
    @forelse($announcements as $a)
      <div class="card">
        <h3>{{ $a->title }}</h3>
        <p class="muted">{{ $a->created_at->format('M d, Y H:i') }}</p>
        <a class="btn" href="{{ route('cust.ann.show',$a) }}">Read More</a>
      </div>
    @empty
      <p class="muted">No announcements available.</p>
    @endforelse
  </div>
  <div class="mt">{{ $announcements->links() }}</div>
</div>
@endsection
