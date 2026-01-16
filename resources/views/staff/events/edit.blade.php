{{-- Author: Chan Kah Wei --}}
@extends('layouts.app')
@section('title','Edit Event • Staff')
@section('content')
<div class="wrap">
  <div class="card">
    <a href="{{ route('staff.events.index') }}" class="pill">Back</a>
    <h2>Edit Event</h2>

    <form method="POST" action="{{ route('staff.events.update',$event) }}" enctype="multipart/form-data" class="grid grid-2" style="gap:14px">
      @csrf 
      @method('PUT')

      <div><label>Title</label><input name="title" class="input" value="{{ $event->title }}" required></div>
      <div><label>Type</label><input name="type" class="input" value="{{ $event->type }}"></div>
      <div><label>Delivery Mode</label><input name="delivery_mode" class="input" value="{{ $event->delivery_mode }}"></div>
      <div><label>Starts At</label><input type="datetime-local" name="starts_at" class="input" value="{{ $event->starts_at->format('Y-m-d\TH:i') }}"></div>
      <div><label>Ends At</label><input type="datetime-local" name="ends_at" class="input" value="{{ $event->ends_at?->format('Y-m-d\TH:i') }}"></div>
      <div><label>Visibility</label><input name="visibility" class="input" value="{{ $event->visibility }}"></div>

      <div>
        <label>Status</label>
        <select name="status" class="input" required>
          <option value="draft"     {{ old('status', $event->status ?? '') == 'draft' ? 'selected' : '' }}>Draft</option>
          <option value="scheduled" {{ old('status', $event->status ?? '') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
          <option value="sent"      {{ old('status', $event->status ?? '') == 'sent' ? 'selected' : '' }}>Sent</option>
          <option value="failed"    {{ old('status', $event->status ?? '') == 'failed' ? 'selected' : '' }}>Failed</option>
        </select>
      </div>

      <div><label>Points Reward</label><input type="number" name="points_reward" class="input" value="{{ $event->points_reward }}"></div>

      <div style="grid-column:1/-1"><label>Description</label><textarea name="description" class="input">{{ $event->description }}</textarea></div>

      {{-- Event image upload --}}
      <div style="grid-column:1/-1">
        <label>Event Image</label>
        @if($event->image_path)
          <div class="mb"><img src="{{ asset('storage/'.$event->image_path) }}" alt="Event Image" style="height:120px;border-radius:8px"></div>
        @endif
        <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp" class="input">
      </div>

      <div style="grid-column:1/-1"><button class="btn primary">Update</button></div>
    </form>
  </div>
</div>
@endsection
