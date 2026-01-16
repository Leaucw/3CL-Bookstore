{{-- Author: Chiang Jong Sheng --}}

@extends('layouts.app')
@section('title','User Details')
@section('content')
<div class="card" style="max-width:720px;margin:0 auto">
  <h3 style="margin-top:0">{{ $user->name }} <span class="pill">{{ $user->role }}</span></h3>
  <p class="muted">Username: {{ $user->username }}</p>
  <p class="muted">Email: {{ $user->email }}</p>
  <p class="muted">Phone: {{ $user->phone }}</p>
  <p class="muted">Address: {{ $user->address }}</p>
  @if($user->role === 'customer')
    <p class="muted">Points: <b>{{ $user->points }}</b></p>
  @endif
  <div class="row mt">
    <a class="btn" href="{{ route('manager.users.edit',$user) }}">Edit</a>
    <a class="pill right" href="{{ route('manager.users.index') }}">Back</a>
  </div>
</div>
@endsection
