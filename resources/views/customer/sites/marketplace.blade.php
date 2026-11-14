@extends('layouts.public')

@section('document_title', $site['label'].' – BacklinkPro')
@section('meta_description', $site['about'])

@section('content')
    @include('customer.publishers.listing', [
        'rows' => $rows,
        'demo' => $demo,
        'categories' => $categories,
        'title' => $site['label'],
        'lead' => $site['about'].' '.$site['detail'],
        'breadcrumb' => 'Niche Edit sites',
        'defaultType' => $defaultType ?? '',
        'defaultNiche' => $defaultNiche ?? '',
        'defaultDr' => $defaultDr ?? '',
        'siteOptions' => $sites,
    ])
@endsection
