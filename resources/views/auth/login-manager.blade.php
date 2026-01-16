{{-- Author: Chiang Jong Sheng --}}

@extends('layouts.app')
@section('title','Manager Login')
@section('content')
<div class="card" style="max-width:520px;margin:0 auto">
  <h3 style="margin-top:0">Manager Login</h3>
  <form method="POST" action="{{ route('login.role','manager') }}">
    @csrf
    <label>Username</label>
    <input class="input" name="username" value="{{ old('username') }}" required autofocus autocomplete="username">
    @error('username')<div class="muted" style="color:#fca5a5">{{ $message }}</div>@enderror

    <label class="mt">Password</label>
    <input class="input" name="password" type="password" required autocomplete="current-password">

    <div class="row mt">
      <button class="btn primary" type="submit">Login</button>
      <a class="pill right" href="{{ route('home') }}">Back</a>
    </div>
  </form>
</div>
@endsection
