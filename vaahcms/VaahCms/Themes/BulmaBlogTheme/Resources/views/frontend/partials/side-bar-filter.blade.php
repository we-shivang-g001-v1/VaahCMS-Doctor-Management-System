
<section>
    <div class="section box" style="margin-top: 3rem;">
        <form method="get" action="{{url('/search')}}">
            <div class="field has-addons">
                <div class="control">
                    <input class="input" value="{{app('request')->input('q')}}" name="q" type="text" placeholder="Find a blog">
                </div>
                <div class="control">
                    <button type="submit" class="button is-primary">
                        Search
                    </button>
                </div>

            </div>
        </form>
    </div>

    @php

        $category = \WebReinvent\VaahCms\Entities\Taxonomy::whereHas('contentFormRelations' , function($c){
            $c->whereHas('field',function ($f){
                $f->where('slug', 'category');
            });
            $c->whereHas('content',function ($f){
                $f->whereHas('contentType',function ($ct){
                    $ct->where('slug', 'blog');
                });
            });
        })->with(['type'])->has('type')->get();

    @endphp

    @if(count($category) > 0)
        <div class="section box">
            <aside class="menu">
                <p class="menu-label">
                    Category
                </p>
                <ul class="menu-list">
                    @foreach($category as $cat)

                        <li>
                            <a href="{{url('/taxonomies/'.$cat->type->slug.'/'.$cat->slug)}}"
                               class="{{ isset($taxonomy) && $cat->slug == $taxonomy->slug ? 'is-active':''}}"
                               id="category-filter"
                               data-category="{{ $cat->name }}">
                                {{ $cat->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </aside>

        </div>
    @endif



</section>
