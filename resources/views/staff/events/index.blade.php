{{-- Author: Chan Kah Wei --}}
@extends('layouts.app')
@section('title','Events • Staff')
@section('content')
<div class="wrap">
  @if(session('ok'))<div class="card" style="border-left:4px solid var(--ok)">{{ session('ok') }}</div>@endif

  <div class="row" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
    <a href="{{ route('dashboard.staff') }}" class="pill">← Back to Staff Home</a>
    <h2>Manage Events</h2>
    <a class="btn primary" href="{{ route('staff.events.create') }}">➕ Create Event</a>
  </div>

  <div class="card">
    <table style="width:100%">
      <thead><tr>
        <th>Title</th><th>Starts</th><th>Ends</th><th>Status</th><th>Points</th><th>Action</th>
      </tr></thead>
      <tbody>
      @forelse($events as $e)
        <tr>
          <td>{{ $e->title }}</td>
          <td>{{ $e->starts_at?->format('Y-m-d H:i') }}</td>
          <td>{{ $e->ends_at?->format('Y-m-d H:i') }}</td>
          <td><span class="pill">{{ $e->status }}</span></td>
          <td>{{ $e->points_reward }}</td>
          <td>
            <a class="btn" href="{{ route('staff.events.edit',$e) }}">✏ Edit</a>
            <form method="POST" action="{{ route('staff.events.destroy',$e) }}" style="display:inline">
              @csrf @method('DELETE')
              <button class="btn danger" onclick="return confirm('Delete this event?')">🗑 Delete</button>
            </form>
          </td>
        </tr>
      @empty
        <tr><td colspan="6" class="muted">No events yet.</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
  <div class="mt">{{ $events->links() }}</div>
</div>
@endsection
