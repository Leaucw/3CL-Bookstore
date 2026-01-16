{{-- Author: Chiang Jong Sheng --}}

@extends('layouts.app')
@section('title','Register (Customer)')
@section('content')
<div class="card" style="max-width:720px;margin:0 auto">
  <h3 style="margin-top:0">Create Customer Account</h3>
  <form method="POST" action="{{ route('register') }}">
    @csrf
    <div class="grid grid-2">
      <div>
        <label>Name</label>
        <input class="input" name="name" value="{{ old('name') }}" required>
      </div>
      <div>
        <label>Username</label>
        <input class="input" name="username" value="{{ old('username') }}" required autocomplete="username">
      </div>
      <div>
        <label>Email</label>
        <input class="input" type="email" name="email" value="{{ old('email') }}" required autocomplete="email">
      </div>
      <div>
        <label>Phone</label>
        <input class="input" name="phone" value="{{ old('phone') }}">
      </div>
      <div class="grid-2" style="grid-column:1/-1;display:grid;gap:16px">
        <div>
          <label>Password</label>
          <input class="input" type="password" name="password" required autocomplete="new-password">
        </div>
        <div>
          <label>Confirm Password</label>
          <input class="input" type="password" name="password_confirmation" required autocomplete="new-password">
        </div>
      </div>
      <div style="grid-column:1/-1">
        <label>Address</label>
        <input class="input" name="address" value="{{ old('address') }}">
      </div>
    </div>
    @if ($errors->any())
      <div class="mt muted" style="color:#fca5a5">{{ implode(', ', $errors->all()) }}</div>
    @endif
    <div class="row mt">
      <button class="btn success" type="submit">Create Account</button>
      <a class="pill right" href="{{ route('home') }}">Back</a>
    </div>
  </form>
</div>
@endsection
