{{-- Author: Chiang Jong Sheng --}}

@extends('layouts.app')
@section('title','My Profile')

@section('content')
@php
  // Fallback in case controller didn't pass $editable
  $editable = $editable ?? (
      $user->role === 'customer'
      ? ['name','username','email','phone','address','avatar','password']
      : ['username','phone','address','avatar','password']
  );
@endphp

<div class="card" style="max-width:880px;margin:0 auto">
  <div class="row">
    <div class="row" style="gap:10px">
      <img src="{{ $user->avatar_path ? asset('storage/'.$user->avatar_path) : 'https://via.placeholder.com/64x64?text=👤' }}"
           alt="avatar" style="height:64px;width:64px;border-radius:12px;border:1px solid #2a3263;object-fit:cover" id="avatarPreview">
      <div>
        <h3 style="margin:0">{{ $user->name }} <span class="pill">{{ $user->role }}</span></h3>
        <div class="muted">
          @if($user->role==='customer') Points: <b>{{ $user->points }}</b> · @endif
          Joined {{ $user->created_at->format('Y-m-d') }}
        </div>
      </div>
    </div>
    <a class="pill right" href="{{ route($user->role.'.dashboard') }}">Back</a>
  </div>

  {{-- Tabs --}}
  <div class="row mt">
    <button class="pill" data-tab="account" style="color: rgb(104, 166, 237)">Account</button>
    <button class="pill" data-tab="security" style="color: rgb(104, 166, 237)">Security</button>
    <button class="pill" data-tab="avatar" style="color: rgb(104, 166, 237)">Avatar</button>
  </div>

  <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt">
    @csrf

    @if ($errors->any())
      <div class="muted" style="color:#fca5a5">{{ implode(', ', $errors->all()) }}</div>
    @endif
    @if(session('flash.success'))
      <div class="muted">{{ session('flash.success') }}</div>
    @endif

    {{-- ACCOUNT TAB --}}
    <div class="grid grid-2 tab tab-account">
      <div>
        <label>Name</label>
        @if(in_array('name',$editable))
          <input class="input" name="name" value="{{ old('name',$user->name) }}" required>
        @else
          {{-- Staff cannot edit name --}}
          <input class="input bg-light text-muted" value="{{ $user->name }}" disabled style="color: gray">
          <small class="text-danger" style="color: rgb(244, 97, 97)">*Please contact manager to change</small>
        @endif
      </div>
      <div>
        <label>Username</label>
        @if(in_array('username',$editable))
          <input class="input" name="username" value="{{ old('username',$user->username) }}" required>
        @else
          <input class="input" value="{{ $user->username }}" disabled>
        @endif
      </div>
      <div>
        <label>Email</label>
        @if(in_array('email',$editable))
          <input class="input" type="email" name="email" value="{{ old('email',$user->email) }}" required>
        @else
          {{-- Staff cannot edit email --}}
          <input class="input bg-light text-muted" type="email" value="{{ $user->email }}" disabled style="color: gray">
          <small class="text-danger" style="color: rgb(244, 97, 97)">*Please contact manager to change</small>
        @endif
      </div>
      <div>
        <label>Phone</label>
        @if(in_array('phone',$editable))
          <input class="input" name="phone" value="{{ old('phone',$user->phone) }}">
        @else
          <input class="input" value="{{ $user->phone }}" disabled>
        @endif
      </div>
      <div style="grid-column:1/-1">
        <label>Address</label>
        @if(in_array('address',$editable))
          <input class="input" name="address" value="{{ old('address',$user->address) }}">
        @else
          <input class="input" value="{{ $user->address }}" disabled>
        @endif
      </div>
    </div>

    {{-- SECURITY TAB --}}
    <div class="grid grid-2 tab tab-security" style="display:none">
      <div>
        <label>New Password</label>
        @if(in_array('password',$editable))
          <input class="input" type="password" name="password" id="pw" autocomplete="new-password">
          <div class="muted" id="pwMeter">Strength: —</div>
        @else
          <input class="input" type="password" value="********" disabled>
        @endif
      </div>
      <div>
        <label>Confirm Password</label>
        @if(in_array('password',$editable))
          <input class="input" type="password" name="password_confirmation" autocomplete="new-password">
        @else
          <input class="input" type="password" value="********" disabled>
        @endif
      </div>
    </div>

    {{-- AVATAR TAB --}}
    <div class="grid tab tab-avatar" style="display:none">
      <div>
        <label>Avatar</label>
        @if(in_array('avatar',$editable))
          <input class="input" type="file" name="avatar" accept="image/*" id="avatarInput">
          @if($user->avatar_path)
            <label class="row mt">
              <input type="checkbox" name="remove_avatar" value="1">
              <span class="muted">Remove current avatar</span>
            </label>
          @endif
        @else
          <input class="input" value="No changes allowed" disabled>
        @endif
      </div>
    </div>

    <div class="row mt">
      <button class="btn success">Save Changes</button>
    </div>
  </form>
</div>

<script>
  // tabs
  const tabs = document.querySelectorAll('[data-tab]');
  const panels = {
    account:  document.querySelector('.tab-account'),
    security: document.querySelector('.tab-security'),
    avatar:   document.querySelector('.tab-avatar'),
  };
  function showTab(name){
    Object.values(panels).forEach(el => el.style.display='none');
    panels[name].style.display='';
    tabs.forEach(b=> b.classList.toggle('primary', b.getAttribute('data-tab')===name));
  }
  tabs.forEach(b => b.addEventListener('click', ()=> showTab(b.getAttribute('data-tab'))));
  showTab('account');

  // avatar preview
  const input = document.getElementById('avatarInput');
  if (input) input.addEventListener('change', e => {
    const file = e.target.files?.[0]; if(!file) return;
    document.getElementById('avatarPreview').src = URL.createObjectURL(file);
  });

  // simple password meter
  const pw = document.getElementById('pw');
  if (pw) pw.addEventListener('input', ()=>{
    const v = pw.value || '';
    let s = 0; if (v.length>=8) s++; if (/[a-z]/.test(v)&&/[A-Z]/.test(v)) s++; if (/\d/.test(v)) s++; if (/\W/.test(v)) s++;
    const labels = ['—','Weak','Okay','Good','Strong'];
    document.getElementById('pwMeter').textContent = 'Strength: '+labels[s];
  });
</script>
@endsection
