{{-- Author: Chiang Jong Sheng --}}

<div class="card" style="max-width:720px;margin:0 auto">
  <h3 style="margin-top:0">{{ $title }}</h3>
  @if ($errors->any())
    <div class="muted" style="color:#fca5a5">{{ implode(', ', $errors->all()) }}</div>
  @endif

  <form method="POST" action="{{ $action }}">
    @csrf
    @if(($method ?? 'POST') === 'PUT') @method('PUT') @endif

    <div class="grid grid-2">
      <div>
        <label>Role</label>
        @php $roleOld = old('role', $user->role ?? 'customer'); @endphp
        <select class="input" name="role" required>
          <option value="staff" {{ $roleOld==='staff'?'selected':'' }}>Staff</option>
          <option value="customer" {{ $roleOld==='customer'?'selected':'' }}>Customer</option>
        </select>
      </div>
      <div>
        <label>Name</label>
        <input class="input" name="name" value="{{ old('name',$user->name ?? '') }}" required>
      </div>
      <div>
        <label>Username</label>
        <input class="input" name="username" value="{{ old('username',$user->username ?? '') }}" required>
      </div>
      <div>
        <label>Email</label>
        <input class="input" type="email" name="email" value="{{ old('email',$user->email ?? '') }}" required>
      </div>
      <div>
        <label>Password {{ isset($user)?'(leave blank to keep)':'' }}</label>
        <input class="input" type="password" name="password" {{ isset($user)?'':'required' }}>
      </div>
      <div>
        <label>Confirm Password</label>
        <input class="input" type="password" name="password_confirmation" {{ isset($user)?'':'required' }}>
      </div>
      <div>
        <label>Phone</label>
        <input class="input" name="phone" value="{{ old('phone',$user->phone ?? '') }}">
      </div>
      <div>
        <label>Address</label>
        <input class="input" name="address" value="{{ old('address',$user->address ?? '') }}">
      </div>
      <div style="grid-column:1/-1">
        <label>Points (customer)</label>
        <input class="input" type="number" name="points" min="0" value="{{ old('points',$user->points ?? 0) }}">
      </div>
    </div>

    <div class="row mt">
      <button class="btn success">{{ isset($user)?'Update':'Create' }}</button>
      <a class="pill right" href="{{ route('manager.users.index') }}">Back</a>
    </div>
  </form>
</div>
