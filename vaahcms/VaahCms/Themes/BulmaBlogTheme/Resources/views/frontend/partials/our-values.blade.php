<section class="about-us our-value">

    <div class="container">
        <div class="heading-section has-margin-bottom-30">
            <h2 class="title">
                {!! get_field($data, 'title', 'cards', 'template') !!}
            </h2>
        </div>
        <div class="card-container">
            <div class="columns is-multiline">
                <div class="column is-6 is-full-mobile">
                    <div class="card">
                        <div class="card-content">
                            <div class="media is-bottom-marginless">
                                <div class="media-content">
                                    <h4 class="title">
                                        {!! get_field($data, 'heading-1', 'cards', 'template') !!}
                                    </h4>
                                </div>
                                <div class="media-left is-marginless">
                                    <figure class="has-width-130 has-negative-margin-right-22 has-negative-margin-top-22">
                                        <img src='{{get_field($data, 'icon-1', 'cards', 'template')}}'/>
                                    </figure>
                                </div>
                            </div>

                            <div class="content">
                                <p>
                                    {!! get_field($data, 'content-1', 'cards', 'template') !!}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="column is-6 is-full-mobile">
                    <div class="card is-relative">
                        <div class="card-content">
                            <div class="media is-bottom-marginless">
                                <div class="media-content">
                                    <h4 class="title has-margin-top-15-desktop">
                                        {!! get_field($data, 'heading-2', 'cards', 'template') !!}
                                    </h4>
                                </div>
                                <div class=" media-left is-marginless">
                                    <figure class="has-width-130 has-negative-margin-right-22 has-negative-margin-top-22">
                                        <img src='{{get_field($data, 'icon-2', 'cards', 'template')}}'/>
                                    </figure>
                                </div>
                            </div>

                            <div class="content">
                                <p>
                                    {!! get_field($data, 'content-2', 'cards', 'template') !!}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="column is-6 is-full-mobile">
                    <div class="card">
                        <div class="card-content">
                            <div class="media is-bottom-marginless">
                                <div class="media-content">
                                    <h4 class="title">
                                        {!! get_field($data, 'heading-3', 'cards', 'template') !!}
                                    </h4>
                                </div>
                                <div class=" media-left is-marginless">
                                    <figure class="has-width-130 has-negative-margin-right-22 has-negative-margin-top-22">
                                        <img src='{{get_field($data, 'icon-3', 'cards', 'template')}}'/>
                                    </figure>
                                </div>
                            </div>

                            <div class="content">
                                <p>
                                    {!! get_field($data, 'content-3', 'cards', 'template') !!}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="column is-6 is-full-mobile">
                    <div class="card is-relative">
                        <div class="card-content">
                            <div class="media is-bottom-marginless">
                                <div class="media-content">
                                    <h4 class="title has-margin-top-15-desktop">
                                        {!! get_field($data, 'heading-4', 'cards', 'template') !!}
                                    </h4>
                                </div>
                                <div class=" media-left is-marginless">
                                    <figure class="has-width-130 has-negative-margin-right-22 has-negative-margin-top-22">
                                        <img src='{{get_field($data, 'icon-4', 'cards', 'template')}}'/>
                                    </figure>
                                </div>
                            </div>

                            <div class="content">
                                <p>
                                    {!! get_field($data, 'content-4', 'cards', 'template') !!}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
