{{-- Author: Chan Kah Wei --}}
@extends('layouts.app')
@section('title',$event->title)
@section('content')
<div class="wrap">
  <div class="card">
    {{-- Success prompt --}}
    @if(session('ok'))
      <div class="card" style="border-left:4px solid var(--ok);margin-bottom:12px">
        {{ session('ok') }}
      </div>
    @endif
    <a href="{{ route('cust.events.index') }}" class="pill">Back</a>
    <h2>{{ $event->title }}</h2>
    @if($event->image_path)
      <img src="{{ asset('storage/'.$event->image_path) }}" 
          alt="{{ $event->title }}" 
          style="width:100%;max-height:300px;object-fit:cover;border-radius:10px;margin-bottom:14px">
    @endif
    <p><strong>When:</strong> {{ $event->starts_at->format('Y-m-d H:i') }} 
      @if($event->ends_at) - {{ $event->ends_at->format('Y-m-d H:i') }} @endif</p>
    <p><strong>Mode:</strong> {{ ucfirst($event->delivery_mode) }}</p>
    <p><strong>Description:</strong> {{ $event->description }}</p>
    <p><strong>Points Reward:</strong> {{ $event->points_reward }}</p>

    <form method="POST" action="{{ route('cust.events.register',$event) }}">
      @csrf
      <button class="btn primary">Join Event</button>
    </form>
  </div>
</div>
@endsection
