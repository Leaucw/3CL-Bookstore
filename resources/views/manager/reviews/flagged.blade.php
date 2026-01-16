{{-- Author: Leong Hui Hui --}}
@extends('layouts.app')
@section('title','Flagged Reviews')

@section('content')
<div class="container">
    <h1 class="mb-4">Flagged Reviews</h1>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if($reviews->isEmpty())
        <p class="text-muted">No flagged reviews at the moment.</p>
    @else
        <div class="card">
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Book</th>
                            <th>User</th>
                            <th>Rating</th>
                            <th>Content</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($reviews as $review)
                        <tr>
                            <td>{{ $review->book->title }}</td>
                            <td>{{ $review->user->name }}</td>
                            <td>{{ $review->rating }}</td>
                            <td>{{ $review->content }}</td>
                            <td>
                                @if($review->hidden)
                                    <span class="badge bg-danger">Hidden</span>
                                @else
                                    <span class="badge bg-success">Visible</span>
                                @endif
                                @if($review->manager_trusted)
                                    <span class="badge bg-primary">Trusted</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if(!$review->hidden)
                                    <!-- Hide -->
                                    <form method="POST" action="{{ route('reviews.managerHide', $review->id) }}" class="d-inline">
                                        @csrf @method('PATCH')
                                        <button class="btn btn-sm btn-warning">Hide</button>
                                    </form>
                                @else
                                    <!-- Unhide (clears flag + trusts) -->
                                    <form method="POST" action="{{ route('reviews.managerUnhide', $review->id) }}" class="d-inline">
                                        @csrf @method('PATCH')
                                        <button class="btn btn-sm btn-success">Unhide</button>
                                    </form>
                                @endif

                                <!-- Trust (clear flag + unhide immediately) -->
                                <form method="POST" action="{{ route('reviews.managerTrust', $review->id) }}" class="d-inline">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-sm btn-info">Trust</button>
                                </form>

                                <!-- Delete -->
                                <form method="POST" action="{{ route('reviews.managerDelete', $review->id) }}" 
                                      class="d-inline"
                                      onsubmit="return confirm('Are you sure you want to delete this review?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection