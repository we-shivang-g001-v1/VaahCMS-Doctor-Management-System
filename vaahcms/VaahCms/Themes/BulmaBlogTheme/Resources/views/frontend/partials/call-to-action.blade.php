@if(Request::is('software'))
    <section class="call-to-action">
        <img src='{{get_field($data, 'image', 'banner-text', 'template')}}'/>
        <div class="container">
            <div class="content">
                <h3 class="title is-size-5-mobile is-size-3-tablet is-size-1-desktop has-text-weight-normal">

                    {!! get_field($data, 'headline', 'banner-text', 'template') !!}
                </h3>
                <button class="is-large button is-primary launch-modal" data-id="1">
                    {!! get_field($data, 'button-label', 'banner-text', 'template') !!}
                </button>
            </div>
        </div>
    </section>

@elseif(Request::is('training'))
    <section class="call-to-action">
        <img src='{{get_field($data, 'image', 'banner-text', 'template')}}'/>
        <div class="container">
            <div class="content">
                <h3 class="title is-size-4-mobile is-size-3-tablet is-size-1-desktop has-text-weight-normal">
                    {!! get_field($data, 'headline', 'banner-text', 'template') !!}
                </h3>
                <button class="is-large button is-primary launch-modal" data-id="1">
                    {!! get_field($data, 'button-label', 'banner-text', 'template') !!}
                </button>
            </div>
        </div>
    </section>

@elseif(Request::is('consulting'))
    <section class="call-to-action">
        <img src='{{get_field($data, 'image', 'banner-text', 'template')}}'/>
        <div class="container">
            <div class="content">
                <h3 class="title is-size-5-mobile is-size-3-tablet is-size-1-desktop has-text-weight-normal">
                    {!! get_field($data, 'headline', 'banner-text', 'template') !!}
                </h3>
                <button class="is-large button is-primary launch-modal" data-id="1">
                    {!! get_field($data, 'button-label', 'banner-text', 'template') !!}
                </button>
            </div>
        </div>
    </section>

@elseif(Request::is('coaching'))
    <section class="call-to-action">
        <img src='{{get_field($data, 'image', 'banner-text', 'template')}}'/>
        <div class="container">
            <div class="content">
                <h3 class="title is-size-5-mobile is-size-3-tablet is-size-1-desktop has-text-weight-normal">
                    {!! get_field($data, 'headline', 'banner-text', 'template') !!}
                </h3>
                <button class="is-large button is-primary launch-modal" data-id="1">
                    {!! get_field($data, 'button-label', 'banner-text', 'template') !!}
                </button>
            </div>
        </div>
    </section>

@elseif(Request::is('counselling'))
    <section class="call-to-action">
        <img src='{{get_field($data, 'image', 'banner-text', 'template')}}'/>
        <div class="container">
            <div class="content">
                <h3 class="title is-size-5-mobile is-size-3-tablet is-size-1-desktop has-text-weight-normal">
                    {!! get_field($data, 'headline', 'banner-text', 'template') !!}
                </h3>
                <button class="is-large button is-primary launch-modal" data-id="1">
                    {!! get_field($data, 'button-label', 'banner-text', 'template') !!}
                </button>
            </div>
        </div>
    </section>

@elseif(Request::is('insights'))
    <section class="call-to-action">
         <img src="{!! get_field($data, 'image', 'bottom-banner', 'template') !!}" alt="Placeholder image">
        <div class="container">
            <div class="content">
                <h3 class="title is-size-5-mobile is-size-3-tablet is-size-1-desktop has-text-weight-normal">
                    {!! get_field($data, 'headline', 'bottom-banner', 'template') !!}
                </h3>
                <button class="is-large button is-primary launch-modal" data-id="1">
                    {!! get_field($data, 'button-label', 'bottom-banner', 'template') !!}
                </button>
            </div>
        </div>
    </section>

@elseif(Request::is('about-us'))
    <section class="call-to-action">
        <img src='{{get_field($data, 'image', 'banner-text', 'template')}}'/>
        <div class="container">
            <div class="content">
                <h3 class="title is-size-5-mobile is-size-3-tablet is-size-1-desktop has-text-weight-normal">
                    {!! get_field($data, 'headline', 'banner-text', 'template') !!}
                </h3>
                <button class="is-large button is-primary launch-modal" data-id="1">
                    {!! get_field($data, 'button-label', 'banner-text', 'template') !!}
                </button>
            </div>
        </div>
    </section>

@elseif(Request::is('events'))
    <section class="call-to-action">
        <img src="{{vh_theme_image_url()}}/images/bg-events-cta.jpg" alt="Placeholder image"/>
        <div class="container">
            <div class="content">
                <h3 class="title is-size-5-mobile is-size-3-tablet is-size-1-desktop has-text-weight-normal">Want to
                    talk to us sooner?</h3>
                <button class="is-large button is-primary launch-modal" data-id="1">Contact Us</button>
            </div>
        </div>
    </section>
@elseif(Request::is('testimonials'))
        <section class="call-to-action">
        <img src='{{get_field($data, 'image', 'bottom-banner', 'template')}}'/>
        <div class="container">
            <div class="content">
                <h3 class="title is-size-5-mobile is-size-3-tablet is-size-1-desktop has-text-weight-normal">
                    {!! get_field($data, 'headline', 'bottom-banner', 'template') !!}
                </h3>
                <button class="is-large button is-primary launch-modal" data-id="1">
                    {!! get_field($data, 'button-label', 'bottom-banner', 'template') !!}
                </button>
            </div>
        </div>
    </section>
@else
    <section class="call-to-action">
         <img src="{!! get_field($data, 'image', 'bottom-banner', 'template') !!}" alt="Placeholder image">
        <div class="container">
            <div class="content">
                <h3 class="title is-size-5-mobile is-size-3-tablet is-size-1-desktop has-text-weight-normal">
                    {!! get_field($data, 'headline', 'bottom-banner', 'template') !!}
                </h3>
                <button class="is-large button is-primary launch-modal" data-id="1">
                    {!! get_field($data, 'button-label', 'bottom-banner', 'template') !!}
                </button>
            </div>
        </div>
    </section>
@endif

