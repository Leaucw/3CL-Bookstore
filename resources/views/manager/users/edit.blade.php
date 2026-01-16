{{-- Author: Chiang Jong Sheng --}}

@extends('layouts.app')
@section('title','Edit User')
@section('content')
@include('manager.users._form', [
  'title' => 'Edit User',
  'action' => route('manager.users.update', $user),
  'method' => 'PUT',
  'user' => $user
])
@endsection
