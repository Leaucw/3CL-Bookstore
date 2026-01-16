{{-- Author: Chiang Jong Sheng --}}

@extends('layouts.app')
@section('title','Create User')
@section('content')
@include('manager.users._form', [
  'title' => 'Create User',
  'action' => route('manager.users.store'),
  'method' => 'POST'
])
@endsection
