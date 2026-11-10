@extends('layouts.admin')

@section('title', $heading)

@section('content')
    <div class="space-y-6">
        <section>
            <p class="text-xs font-semibold tracking-wider text-brand uppercase">Admin</p>
            <h1 class="mt-2 text-2xl font-semibold">{{ $heading }}</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-navy/70">{{ $summary }}</p>
        </section>
        <section class="panel p-6 sm:p-8">
            <p class="text-sm leading-6 text-navy/75">This database does not include an {{ strtolower($heading) }} table, so there are no records to list, add, or edit. Creating that table was left out so the existing tables stay unchanged.</p>
        </section>
    </div>
@endsection
