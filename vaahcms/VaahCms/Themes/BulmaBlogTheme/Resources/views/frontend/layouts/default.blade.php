<!DOCTYPE html>
<html lang="en">

<head>
    {!! config('settings.global.script_after_head_start'); !!}



    @if(isset($title) && $title)
        <title>{{$title}}</title>
    @elseif(isset($data) && (is_array($data) || is_object($data)) && is_subclass_of($data, 'Illuminate\Database\Eloquent\Model'))
        {!! get_field($data, 'seo-meta-tags') !!}
    @else
        <title>BlogTheme</title>
    @endif

    {!! vh_search_engine_visibility() !!}

    <meta name="csrf-token" id="_token" content="{{ csrf_token() }}">

    <base href="{{\URL::to('/')}}">

    <meta name="current-url" id="current_url" content="{{ url()->current() }}">

    <meta name="debug" id="debug" content="true">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.2/css/bulma.min.css">

    @yield('vaahcms_extend_frontend_head')

    @yield('vaahcms_extend_frontend_css')
    {!! config('settings.global.script_before_head_close'); !!}
</head>
	<body>

    {!! config('settings.global.script_after_body_start'); !!}

	<?php

	//vh_get_modules_extended_views('menu');

	?>

    @include("vaahcms::frontend.partials.errors")
    @include("vaahcms::frontend.partials.flash")

    @include('bulmablogtheme::frontend.partials.top-menu')


    @yield('content')


    @yield('vaahcms_extend_frontend_scripts')

    {!! config('settings.global.script_before_body_close'); !!}

	</body>
</html>
