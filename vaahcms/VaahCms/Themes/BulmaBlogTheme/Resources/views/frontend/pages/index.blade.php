@extends("bulmablogtheme::frontend.layouts.default")

@section('vaahcms_extend_frontend_head')

@endsection

@section('vaahcms_extend_frontend_css')

@endsection

@section('vaahcms_extend_frontend_scripts')

@endsection

@section('content')
    <div>
        @include('bulmablogtheme::frontend.partials.header')

        544444

    <div class="container">
        <div class="section">
            <div class="columns">
                <div class="column is-three-quarters">
                    <div class="section">
                        <input type="hidden" value="{{ csrf_token() }}" id="token">
                       <div class="columns is-multiline" id="blogList"></div>
                    </div>
                </div>
                <div class="column">
                    <div class="section box" style="margin-top: 3rem;">
                        <div class="field is-grouped">
                            <p class="control is-expanded">
                                <input class="input" id="search-blog" type="text" placeholder="Find a blog">
                            </p>
                        </div>
                    </div>

                    @php

                        $category = \VaahCms\Modules\Cms\Entities\Content::leftJoin('vh_cms_content_types',function ($join){
                            $join->on('vh_cms_content_types.id', 'vh_cms_contents.vh_cms_content_type_id');
                        })->join('vh_cms_content_form_fields', function ($join) {
                            $join->on('vh_cms_content_form_fields.vh_cms_content_id', '=', 'vh_cms_contents.id');
                        })->join('vh_cms_form_fields', function ($join) {
                            $join->on('vh_cms_form_fields.id', '=', 'vh_cms_content_form_fields.vh_cms_form_field_id');
                        })->where('vh_cms_content_types.slug','blog')
                            ->where('vh_cms_form_fields.slug','category')
                            ->select('vh_cms_content_form_fields.content')
                            ->distinct()
                            ->get();

                    @endphp

                    <div class="section box">
                        <aside class="menu">
                            <p class="menu-label">
                                Category
                            </p>
                            <ul class="menu-list">
                                <li><a id="category-filter" class="is-active" data-category="all">All</a></li>
                                @foreach($category as $cat)

                                    <li><a id="category-filter" data-category="{{ $cat->content }}">{{ $cat->content }}</a></li>
                                @endforeach
                            </ul>
                        </aside>

                    </div>
                </div>

            </div>

        </div>
    </div>
    <footer class="footer">
        <div class="container">
            <div class="content has-text-centered">
                <p>
                    <strong>Bulma</strong> by <a href="http://jgthms.com">Jeremy Thomas</a>. The source code is licensed
                    <a href="http://opensource.org/licenses/mit-license.php">MIT</a>. The website content is licensed <a href="http://creativecommons.org/licenses/by-nc-sa/4.0/">CC ANS 4.0</a>.
                </p>
            </div>
        </div>
    </footer>
    </div>

@endsection


