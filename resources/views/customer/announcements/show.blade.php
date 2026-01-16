{{-- Author: Chan Kah Wei --}}
@extends('layouts.app')
@section('title',$announcement->title)
@section('content')
<div class="wrap">
  <a href="{{ route('cust.ann.index') }}" class="pill">Back</a>
  <div class="card">
    <h2>{{ $announcement->title }}</h2>
    <p class="muted">{{ $announcement->created_at->format('M d, Y H:i') }}</p>
    <p>{{ $announcement->body }}</p>
  </div>
</div>
@endsection
