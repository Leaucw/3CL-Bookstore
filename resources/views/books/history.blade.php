{{-- Author: Leau Chee Way --}}
@extends('layouts.app')

@section('header')
  <h2 class="font-semibold text-xl text-gray-800 leading-tight">📦 Stock History — {{ $book->title }}</h2>
@endsection

@section('content')
<div class="py-6">
  <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
    <a href="{{ route('books.index') }}" class="text-indigo-600 hover:underline">&larr; Back to Inventory</a>

    <div class="mt-4 bg-white shadow rounded overflow-hidden">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-3 text-left">Date</th>
            <th class="px-4 py-3 text-left">Type</th>
            <th class="px-4 py-3 text-left">Qty</th>
            <th class="px-4 py-3 text-left">Reason</th>
            <th class="px-4 py-3 text-left">By</th>
          </tr>
        </thead>
        <tbody>
          @forelse($book->stockMovements as $m)
            <tr class="border-t">
              <td class="px-4 py-3">{{ $m->created_at->format('Y-m-d H:i') }}</td>
              <td class="px-4 py-3">{{ ucfirst($m->type) }}</td>
              <td class="px-4 py-3 {{ $m->quantity_change >= 0 ? 'text-green-700' : 'text-red-700' }}">
                {{ $m->quantity_change > 0 ? '+' : '' }}{{ $m->quantity_change }}
              </td>
              <td class="px-4 py-3">{{ $m->reason ?? '—' }}</td>
              <td class="px-4 py-3">{{ $m->user?->name ?? 'System' }}</td>
            </tr>
          @empty
            <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">No stock history yet.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
