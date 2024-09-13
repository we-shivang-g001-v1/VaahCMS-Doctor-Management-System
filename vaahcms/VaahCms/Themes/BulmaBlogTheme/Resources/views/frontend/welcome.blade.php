@extends("jalapeno::frontend.layouts.default")

@section('vaahcms_extend_frontend_head')

@endsection

@section('vaahcms_extend_frontend_css')

@endsection

@section('vaahcms_extend_frontend_scripts')


    <script src="{{vh_theme_assets_url("Jalapeno", "js/common.js")}}"></script>


@endsection

@section('content')


    <div id="appJalapeno">


        <!--sections-->
        <section class="section">
            <div class="container">

                <!--columns-->
                <div class="columns is-centered">
                    <div class="column is-half">
                        <h1 class="title">Bulma 0.9.2 Blog - Theme</h1>

                        <h3 class="subtitle">Steps to configure:</h3>

                        <ol class="has-margin-left-40">
                            <li>Create Home page</li>
                            <li>Create Top Menu</li>
                            <li>Select Home Page</li>
                            <li>Set Home Menu as <b>Home Page</b> </li>
                        </ol>
                    </div>
                </div>
                <!--/columns-->

            </div>
        </section>
        <!--sections-->

        <!--sections-->
        <section class="section">
            <div class="container">

            </div>
        </section>
        <!--sections-->




    </div>



@endsection
