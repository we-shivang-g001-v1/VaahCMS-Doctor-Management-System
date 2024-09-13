@extends("bulmablogtheme::frontend.layouts.default",["title" => "Search Result for: ".app('request')->input('q')])

@section('vaahcms_extend_frontend_head')

@endsection

@section('vaahcms_extend_frontend_css')

@endsection

@section('vaahcms_extend_frontend_scripts')

@endsection

@section('content')
    <div>
        @include('bulmablogtheme::frontend.partials.search-result-cards')
        @include('bulmablogtheme::frontend.partials.footer')
    </div>

@endsection


