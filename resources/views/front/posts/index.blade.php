@extends('layouts.front')
@section('title', $title.' - Lembaga Bahasa')
@section('content')
<section class="max-w-7xl mx-auto px-4 py-8">
  <h1 class="text-2xl font-semibold mb-6">{{ $title }}</h1>
  @if($posts->count())
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
      @foreach($posts as $p)
        <x-post.card :post="$p"/>
      @endforeach
    </div>
    <div class="mt-8">{{ $posts->links() }}</div>
  @else
    <p>Belum ada data.</p>
  @endif
</section>
@endsection
