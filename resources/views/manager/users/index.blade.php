{{-- Author: Chiang Jong Sheng --}}

@extends('layouts.app')
@section('title','User Manager')

@section('content')
<div class="card">
  <div class="row">
    <div class="row" style="gap:10px">
      <h3 style="margin:0">Users</h3>
      <span class="pill">Staff: {{ $counts['staff'] }}</span>
      <span class="pill">Customers: {{ $counts['customer'] }}</span>
    </div>

    <div class="right row" style="gap:8px">
      <a class="pill" href="{{ route('manager.dashboard') }}">← Back to Dashboard</a>
      <a class="btn primary" href="{{ route('manager.users.create') }}">+ New</a>
    </div>
  </div>

  {{-- Filters / Sort --}}
  <form method="GET" class="row mt" style="gap:10px">
    <input class="input" name="search" value="{{ $search ?? '' }}" placeholder="Search username, email, or name" style="min-width:260px">

    <select class="input" name="role" style="min-width:150px">
      <option value="" {{ ($role ?? '')==='' ? 'selected':'' }}>All roles</option>
      <option value="staff" {{ ($role ?? '')==='staff' ? 'selected':'' }}>Staff</option>
      <option value="customer" {{ ($role ?? '')==='customer' ? 'selected':'' }}>Customer</option>
    </select>

    <select class="input" name="sort" style="min-width:150px">
      <option value="id"         {{ ($sort ?? 'id')==='id' ? 'selected':'' }}>Sort by ID</option>
      <option value="name"       {{ ($sort ?? 'id')==='name' ? 'selected':'' }}>Sort by Name</option>
      <option value="created_at" {{ ($sort ?? 'id')==='created_at' ? 'selected':'' }}>Newest/Oldest</option>
    </select>

    <select class="input" name="dir" style="min-width:130px">
      <option value="asc"  {{ ($dir ?? 'desc')==='asc'  ? 'selected':'' }}>Ascending</option>
      <option value="desc" {{ ($dir ?? 'desc')==='desc' ? 'selected':'' }}>Descending</option>
    </select>

    <select class="input" name="perPage" style="min-width:110px">
      @foreach([10,12,25,50,100] as $pp)
        <option value="{{ $pp }}" {{ ($perPage ?? 12)===$pp ? 'selected':'' }}>{{ $pp }}/page</option>
      @endforeach
    </select>

    <button class="btn" type="submit">Apply</button>
    @if(($search ?? '')!=='' || ($role ?? '')!=='' || ($sort ?? 'id')!=='id' || ($dir ?? 'desc')!=='desc' || ($perPage ?? 12)!==12)
      <a class="pill" href="{{ route('manager.users.index') }}">Reset</a>
    @endif

    {{-- Optional: export keeps current filters --}}
    <a class="btn right" href="{{ route('manager.users.export', request()->query()) }}">Export CSV</a>
  </form>

  {{-- Table --}}
  <div class="mt">
    <table class="table">
      <thead>
        <tr>
          @php $toggleDir = ($dir ?? 'desc') === 'asc' ? 'desc' : 'asc'; @endphp
          <th>
            <a href="{{ route('manager.users.index', array_merge(request()->query(), ['sort'=>'id','dir'=> (($sort ?? 'id')==='id' ? $toggleDir : 'asc')])) }}">
              ID {!! ($sort ?? 'id')==='id' ? (($dir ?? 'desc')==='asc'?'↑':'↓') : '' !!}
            </a>
          </th>
          <th>Role</th>
          <th>
            <a href="{{ route('manager.users.index', array_merge(request()->query(), ['sort'=>'name','dir'=> (($sort ?? 'id')==='name' ? $toggleDir : 'asc')])) }}">
              Name {!! ($sort ?? 'id')==='name' ? (($dir ?? 'desc')==='asc'?'↑':'↓') : '' !!}
            </a>
          </th>
          <th>Username</th>
          <th>Email</th>
          <th>Points</th>
          <th>Created</th>
          <th class="right">Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse($users as $u)
          <tr>
            <td>{{ $u->id }}</td>
            <td><span class="pill">{{ $u->role }}</span></td>
            <td>{{ $u->name }}</td>
            <td>{{ $u->username }}</td>
            <td>{{ $u->email }}</td>
            <td>{{ $u->points }}</td>
            <td>{{ $u->created_at->format('Y-m-d') }}</td>
            <td class="right">
              <a class="pill" href="{{ route('manager.users.edit',$u) }}">Edit</a>

              @if($u->role !== 'manager' && $u->id !== auth()->id())
                <form action="{{ route('manager.users.destroy', $u) }}"
                      method="POST"
                      class="inline"
                      onsubmit="return confirm('Delete user {{ $u->name }}? This cannot be undone.');">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="pill pill-danger">Delete</button>
                </form>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="8" class="muted">No users found…</td></tr>
        @endforelse
      </tbody>
    </table>

    <div class="mt">
      {{ $users->links() }}
    </div>
  </div>
</div>
@endsection
