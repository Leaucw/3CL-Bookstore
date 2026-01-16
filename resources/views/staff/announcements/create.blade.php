{{-- Author: Chan Kah Wei --}}
@extends('layouts.app')
@section('title','Create Announcement • Staff')
@section('content')
<div class="wrap">
  <div class="card">
    <a href="{{ route('dashboard.staff') }}" class="pill">← Back to Staff Home</a>
    <h2>Create Announcement</h2>
    <form method="POST" action="{{ route('staff.ann.store') }}">
      @csrf
      <div><label>Title</label><input name="title" class="input" required></div>
      <div><label>Body</label><textarea name="body" class="input" rows="5" required></textarea></div>
      <div>
        <label>Status</label>
        <select name="status" class="input" required>
          <option value="draft"     {{ old('status', $announcement->status ?? '') == 'draft' ? 'selected' : '' }}>Draft</option>
          <option value="scheduled" {{ old('status', $announcement->status ?? '') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
          <option value="sent"      {{ old('status', $announcement->status ?? '') == 'sent' ? 'selected' : '' }}>Sent</option>
          <option value="failed"    {{ old('status', $announcement->status ?? '') == 'failed' ? 'selected' : '' }}>Failed</option>
        </select>
      </div>
      <div><label>Channels (optional)</label><input name="channels[]" class="input" placeholder="e.g. mail,sms"></div>
      <div><button class="btn primary">Save & Publish</button></div>
    </form>
  </div>
</div>
@endsection
