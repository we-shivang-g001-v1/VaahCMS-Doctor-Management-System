@php

    $blogs = get_the_contents('blog');

@endphp




<div class="container">
    <div class="section">
        <div class="columns">
            <div class="column is-three-quarters mt-6">
                    <div class="columns is-multiline">
                        @if(isset($blogs['list']) && (is_array($blogs['list']) || is_object($blogs['list'])) && count($blogs['list']) > 0)
                            @foreach($blogs['list'] as $blog_data)

                                @php


                                    if($blog_data->authorUser){
                                        $image = $blog_data->authorUser->avatar;
                                        $name = $blog_data->authorUser->name;
                                        $title = $blog_data->authorUser->title;

                                    }


                                @endphp

                                <div class="column is-6" style="display: grid;">
                                    <div class="card box">

                                        <a href="{{url('/blog/'.$blog_data->permalink)}}">
                                            <div class="card-image">
                                                <figure class="image is-4by3">
                                                    @if(get_the_field($blog_data, 'thumbnail-image'))
                                                        <img src='{{get_the_field($blog_data, 'thumbnail-image')}}'/>
                                                    @else
                                                        <img src="http://bulma.io/images/placeholders/1280x960.png" alt="Image">
                                                    @endif
                                                </figure>
                                            </div>

                                            <div class="card-content">

                                                @if($blog_data->authorUser)
                                                    <div class="media">
                                                        <div class="media-left">
                                                            <figure class="image is-48x48">
                                                                @if($image)
                                                                    <img src='{{$image}}' alt="author_image"/>
                                                                @else
                                                                    <img src="http://bulma.io/images/placeholders/96x96.png" alt="Image">
                                                                @endif

                                                            </figure>
                                                        </div>
                                                        <div class="media-content">
                                                            <p>
                                                                <strong>{{$name ? $name : '' }}</strong>
                                                                <br>
                                                                <small>{{ $title ? $title : '' }}</small>
                                                            </p>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>

                                        </a>


                                        <div class="content">
                                            <a href="{{url('/blog/'.$blog_data->permalink)}}">
                                                <h4>{!! get_field($blog_data, 'title') !!}</h4>
                                                <p class="has-text-black">{!! substr(strip_tags(get_field($blog_data, 'content')), 0, 250); !!}....</p>
                                            </a>
                                            <br/>
                                            <div class="level">

                                                    <span class="level-left has-text-weight-bold" style="display: grid">


                                                        @php

                                                            if(isset($blog_data)
                                                            && get_the_field($blog_data, 'category')){

                                                             $taxonomy_cats = get_the_field($blog_data, 'category');

                                                                if(isset($taxonomy_cats->id)){
                                                                    $taxonomy_cats = [$taxonomy_cats];
                                                                }

                                                            }

                                                            $taxonomy_type = array();

                                                            if(isset($taxonomy_cats) && is_array($taxonomy_cats) && count($taxonomy_cats) > 0){
                                                                foreach ($taxonomy_cats as $taxonomy_cat){

                                                                    $taxonomy_type[] = \WebReinvent\VaahCms\Entities\TaxonomyType::where(
                                                                                    'id',$taxonomy_cat->vh_taxonomy_type_id
                                                                                )->first();

                                                                }
                                                            }

                                                        @endphp
                                                        @if( isset($taxonomy_cats) && is_array($taxonomy_cats) && count($taxonomy_cats) > 0 )

                                                            @foreach($taxonomy_cats as $key => $taxonomy_cat)

                                                                @if($taxonomy_type[$key])
                                                                    <a href="{{url('/taxonomies/'.$taxonomy_type[$key]->slug.'/'.$taxonomy_cat->slug)}}">
                                                                        {!! $taxonomy_cat->name !!}
                                                                    </a>
                                                                @endif



                                                            @endforeach

                                                        @endif

                                                    </span>
                                                <br/>
                                                <small class="level-right">{{date('d M Y - h:i A', strtotime($blog_data->created_at))}}</small>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                            @endforeach
                        @else
                            <div class="column">
                                <img style="width: -webkit-fill-available"
                                     src='https://cdn.dribbble.com/users/683081/screenshots/2728654/exfuse_app_main_nocontent.png?compress=1&resize=400x300'/>
                            </div>
                        @endif
                    </div>
                <div class="has-text-centered">

                    @if($blogs && isset($blogs['list']))
                        {{$blogs['list']}}
                    @endif

                </div>

            </div>
            <div class="column">
                @include('bulmablogtheme::frontend.partials.side-bar-filter')
            </div>

        </div>



    </div>
</div>


