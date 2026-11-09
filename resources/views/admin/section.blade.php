@extends('layouts.admin')

@section('title', $heading)

@section('content')
    <section class="panel p-6 sm:p-8">
        <p class="text-xs font-semibold tracking-wider text-brand uppercase">Admin</p>
        <h1 class="mt-2 text-2xl font-semibold">{{ $heading }}</h1>
        <p class="mt-3 max-w-2xl text-sm leading-6 text-navy/70">{{ $summary }}</p>
    </section>
@endsection
