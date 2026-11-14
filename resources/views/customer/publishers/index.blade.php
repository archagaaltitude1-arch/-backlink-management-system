@extends('layouts.public')

@section('document_title', 'All publishers – BacklinkPro')
@section('meta_description', 'Browse every publisher website, then filter by the niche, language, metrics and price stored on each listing.')

@section('content')
    @include('customer.publishers.listing', [
        'rows' => $rows,
        'demo' => $demo,
        'categories' => $categories,
        'title' => 'List of posting websites (All publishers)',
        'lead' => 'Every publisher website on this list is included. Filter by the niche, language, metrics and price stored on each site, then open the one you want.',
        'breadcrumb' => 'Publisher list',
    ])
@endsection
