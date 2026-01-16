{{-- Author: Chiang Jong Sheng --}}

@extends('layouts.app')
@section('title','Customer Login')
@section('content')
<div class="card" style="max-width:520px;margin:0 auto">
  <h3 style="margin-top:0">Customer Login</h3>
  <form method="POST" action="{{ route('login.role','customer') }}">
    @csrf
    <label>Email</label>
    <input class="input" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email">
    @error('email')<div class="muted" style="color:#fca5a5">{{ $message }}</div>@enderror

    <label class="mt">Password</label>
    <input class="input" name="password" type="password" required autocomplete="current-password">

    <div class="row mt">
      <button class="btn primary" type="submit">Login</button>
      <a class="pill right" href="{{ route('home') }}">Back</a>
    </div>
  </form>
</div>
@endsection
