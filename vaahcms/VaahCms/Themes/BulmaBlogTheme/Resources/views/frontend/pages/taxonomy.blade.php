@extends("bulmablogtheme::frontend.layouts.default",["title" => "Category: ".$taxonomy->name])

@section('vaahcms_extend_frontend_head')

@endsection

@section('vaahcms_extend_frontend_css')

@endsection

@section('vaahcms_extend_frontend_scripts')

@endsection

@section('content')
    <div>
        @include('bulmablogtheme::frontend.partials.header')
        @include('bulmablogtheme::frontend.partials.category-result-cards')
        @include('bulmablogtheme::frontend.partials.footer')
    </div>

@endsection


