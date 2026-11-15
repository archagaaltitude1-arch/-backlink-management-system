@extends('layouts.public')

@php
    $defaultType = $defaultType ?? '';
    $defaultDr = $defaultDr ?? '';
    $paidLabel = $paidLabel ?? '';
    $heading = isset($country['label'])
        ? $country['label'].' publisher websites'
        : ($defaultType !== '' ? $defaultType.' websites' : 'List of posting websites (All publishers)');
    $intro = isset($country['label'])
        ? 'This chart lists only the publisher websites whose strongest traffic comes from '.$country['label'].'.'
        : ($defaultType !== ''
            ? 'This chart lists '.$defaultType.' websites'.($defaultDr !== '' ? ' with a Domain Rating of '.$defaultDr.' or higher.' : '.')
            : 'Every publisher website on this list is included. Filter by the niche, language, metrics and price stored on each site, then open the one you want.');
@endphp

@section('document_title', $heading.' – BacklinkPro')
@section('meta_description', $intro)

@section('content')
    @include('customer.publishers.listing', [
        'rows' => $rows,
        'demo' => $demo,
        'categories' => $categories,
        'title' => $heading,
        'lead' => $intro,
        'breadcrumb' => $country['label'] ?? ($defaultType !== '' ? $defaultType : 'Publisher list'),
        'defaultCountry' => $country['code'] ?? '',
        'defaultType' => $defaultType,
        'defaultDr' => $defaultDr,
        'paidLabel' => $paidLabel,
    ])
@endsection
