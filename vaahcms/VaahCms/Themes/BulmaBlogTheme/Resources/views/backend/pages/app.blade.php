@extends("vaahcms::backend.vaahone.layouts.backend")

@section('vaahcms_extend_backend_css')

@endsection


@section('vaahcms_extend_backend_js')

    @if(env('APP_THEME_BULMABLOGTHEME_ENV') == 'develop')
        <script src="http://localhost:8080/bulmablogtheme/assets/build/app.js" defer></script>
    @else
        <script src="{{vh_theme_assets_url("BulmaBlogTheme", "build/app.js")}}"></script>
    @endif

@endsection

@section('content')

    <div id="appBulmaBlogTheme">

        <router-view></router-view>

        <vue-progress-bar></vue-progress-bar>

    </div>

@endsection
