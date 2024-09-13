<section class="p-0 navbar-menu-section has-background-white">
    <nav class="navbar" role="navigation" aria-label="main navigation">
        <div class="navbar-brand">
            <a class="navbar-item" href="{{url('/')}}">
                <img src="https://bulma.io/images/bulma-logo.png" width="112" height="28">
            </a>

            <a role="button" class="navbar-burger" aria-label="menu" aria-expanded="false" data-target="navbarBasicExample">
                <span aria-hidden="true"></span>
                <span aria-hidden="true"></span>
                <span aria-hidden="true"></span>
            </a>
        </div>

        <div id="navbarBasicExample" class="navbar-menu">
            <div class="navbar-start">

            </div>

            <div class="navbar-end">
                {!! vh_location('top', true) !!}
            </div>
        </div>
    </nav>
</section>

{{--{!! vh_location('top', true) !!}--}}

