@extends('layouts.app')
@section('content')
<h3>{{ isset($user)?'Edit User':'Create User' }}</h3>
<form method="POST" action="{{ isset($user)?route('manager.users.update',$user):route('manager.users.store') }}">
@csrf
@if(isset($user)) @method('PUT') @endif
<label>Role
<select name="role" required>
  <option value="staff" {{ old('role',$user->role??'')==='staff'?'selected':'' }}>Staff</option>
  <option value="customer" {{ old('role',$user->role??'')==='customer'?'selected':'' }}>Customer</option>
</select></label><br>
<label>Name <input name="name" value="{{ old('name',$user->name??'') }}" required></label><br>
<label>Username <input name="username" value="{{ old('username',$user->username??'') }}" required></label><br>
<label>Email <input name="email" type="email" value="{{ old('email',$user->email??'') }}" required></label><br>
<label>Password <input name="password" type="password" {{ isset($user)?'':'required' }}></label><br>
<label>Confirm <input name="password_confirmation" type="password" {{ isset($user)?'':'required' }}></label><br>
<label>Phone <input name="phone" value="{{ old('phone',$user->phone??'') }}"></label><br>
<label>Address <input name="address" value="{{ old('address',$user->address??'') }}"></label><br>
<label>Points (customer) <input type="number" name="points" value="{{ old('points',$user->points??0) }}"></label><br>
<button>{{ isset($user)?'Update':'Create' }}</button>
</form>
@endsection
