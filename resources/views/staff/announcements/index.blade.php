{{-- Author: Chan Kah Wei --}}
@extends('layouts.app')
@section('title','Announcements • Staff')
@section('content')
<div class="wrap">
  @if(session('ok'))<div class="card" style="border-left:4px solid var(--ok)">{{ session('ok') }}</div>@endif

  <div class="row" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
    <a href="{{ route('dashboard.staff') }}" class="pill">← Back to Staff Home</a>
    <h2>Manage Announcements</h2>
    <a class="btn primary" href="{{ route('staff.ann.create') }}">➕ Create Announcement</a>
  </div>

  <div class="card">
    <table style="width:100%">
      <thead><tr><th>Title</th><th>Status</th><th>Created</th><th>Action</th></tr></thead>
      <tbody>
      @forelse($announcements as $a)
        <tr>
          <td>{{ $a->title }}</td>
          <td><span class="pill">{{ $a->status ?? 'draft' }}</span></td>
          <td>{{ $a->created_at->format('Y-m-d H:i') }}</td>
          <td>
            <a class="btn" href="{{ route('staff.ann.edit',$a) }}">✏ Edit</a>
            <form method="POST" action="{{ route('staff.ann.destroy',$a) }}" style="display:inline">
              @csrf @method('DELETE')
              <button class="btn danger" onclick="return confirm('Delete this announcement?')">🗑 Delete</button>
            </form>
          </td>
        </tr>
      @empty
        <tr><td colspan="4" class="muted">No announcements yet.</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
  <div class="mt">{{ $announcements->links() }}</div>
</div>
@endsection
