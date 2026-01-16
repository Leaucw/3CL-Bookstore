{{-- Author: Chan Kah Wei --}}
@extends('layouts.app')
@section('title','Edit Announcement • Staff')
@section('content')
<div class="wrap">
  <div class="card">
    <a href="{{ route('staff.ann.index') }}" class="pill">Back</a>
    <h2>Edit Announcement</h2>
    <form method="POST" action="{{ route('staff.ann.update',$announcement) }}">
      @csrf @method('PUT')
      <div><label>Title</label><input name="title" class="input" value="{{ $announcement->title }}" required></div>
      <div><label>Body</label><textarea name="body" class="input" rows="5">{{ $announcement->body }}</textarea></div>
      <div>
        <label>Status</label>
        <select name="status" class="input" required>
          <option value="draft"     {{ old('status', $announcement->status ?? '') == 'draft' ? 'selected' : '' }}>Draft</option>
          <option value="scheduled" {{ old('status', $announcement->status ?? '') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
          <option value="sent"      {{ old('status', $announcement->status ?? '') == 'sent' ? 'selected' : '' }}>Sent</option>
          <option value="failed"    {{ old('status', $announcement->status ?? '') == 'failed' ? 'selected' : '' }}>Failed</option>
        </select>
      </div>
      <div><button class="btn primary">Update</button></div>
    </form>
  </div>
</div>
@endsection
