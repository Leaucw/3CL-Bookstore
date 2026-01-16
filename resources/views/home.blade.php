@extends('layouts.app')
@section('title','Bookstore • Home')
@section('content')
<div class="grid grid-2">
  <div class="card">
    <h2 style="margin:0 0 8px">Welcome to Bookstore</h2>
    <p class="muted">Register as a customer or log in as Manager / Staff / Customer.</p>
    @guest
      <div class="row mt">
        <a class="btn success" href="{{ route('register') }}">Create Customer Account</a>
        <span class="pill">or</span>
        <a class="btn" href="{{ route('login.customer') }}">Customer Login</a>
      </div>
      <div class="row mt">
        <a class="pill" href="{{ route('login.staff') }}">Staff Login</a>
        <a class="pill" href="{{ route('login.manager') }}">Manager Login</a>
      </div>
    @else
      <div class="mt">Logged in as <b>{{ auth()->user()->username }}</b> ({{ auth()->user()->role }})</div>
    @endguest
  </div>
  <div class="card">
    <h3 style="margin-top:0">Features</h3>
    <ul>
      <li>3 separate login flows (Manager / Staff / Customer)</li>
      <li>Customer member points</li>
      <li>Manager: CRUD staff & customers</li>
      <li>Profile editing (manager locked)</li>
    </ul>
  </div>
</div>
@endsection
